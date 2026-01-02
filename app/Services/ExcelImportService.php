<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelImportService
{
    /**
     * Header mapping dari Excel ke field database (bahasa Indonesia)
     * Key = header Excel (lowercase), Value = field database
     */
    protected array $headerMapping = [
        'nama' => 'nama',
        'pangkat' => 'pangkat',
        'nrp/nip' => 'nrp_nip',
        'nrp_nip' => 'nrp_nip',
        'nrpnip' => 'nrp_nip',
        'jabatan' => 'jabatan',
        'satuan_kerja' => 'satuan_kerja',
        'satuan kerja' => 'satuan_kerja',
        'unit' => 'satuan_kerja',
        'no_hp' => 'no_hp_raw',
        'no hp' => 'no_hp_raw',
        'nohp' => 'no_hp_raw',
        'telepon' => 'no_hp_raw',
        'hp' => 'no_hp_raw',
        'tgl_lahir' => 'tanggal_lahir',
        'tgl lahir' => 'tanggal_lahir',
        'tanggal_lahir' => 'tanggal_lahir',
        'tanggal lahir' => 'tanggal_lahir',
        'gender' => 'jenis_kelamin',
        'jenis_kelamin' => 'jenis_kelamin',
        'jenis kelamin' => 'jenis_kelamin',
        'no_lab' => 'no_lab',
        'no lab' => 'no_lab',
        'nolab' => 'no_lab',
        'tgl_pemeriksaan' => 'tanggal_periksa',
        'tgl pemeriksaan' => 'tanggal_periksa',
        'tanggal_periksa' => 'tanggal_periksa',
        'tanggal_pemeriksaan' => 'tanggal_periksa',
        'tanggal pemeriksaan' => 'tanggal_periksa',
        'kode_paket' => 'kode_paket',
        'kode paket' => 'kode_paket',
        'paket' => 'kode_paket',
        'kode_perusahaan' => 'kode_instansi',
        'kode perusahaan' => 'kode_instansi',
        'kode_instansi' => 'kode_instansi',
        'kode instansi' => 'kode_instansi',
        'instansi' => 'kode_instansi',
    ];

    /**
     * Header aliases untuk format khusus dari Excel
     */
    protected array $headerAliases = [
        'tgl_lahir(dd/mm/yyyy)' => 'tanggal_lahir',
        'gender(pria/wanita)' => 'jenis_kelamin',
        'tgl_pemeriksaan(dd/mm/yyyy)' => 'tanggal_periksa',
    ];

    /**
     * Parse file Excel dan return array data yang sudah divalidasi
     */
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) < 2) {
            throw new Exception('File Excel harus memiliki minimal 1 baris data selain header');
        }

        $headers = $this->parseHeaders($rows[0]);
        Log::info('Parsed headers', ['headers' => $headers]);

        $validData = [];
        $invalidData = [];
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1;

            // Skip baris kosong
            if ($this->isEmptyRow($row)) {
                continue;
            }

            try {
                $parsedRow = $this->parseRow($row, $headers, $rowNumber);
                $validData[] = [
                    'row_number' => $rowNumber,
                    'data' => $parsedRow,
                ];
            } catch (Exception $e) {
                $invalidData[] = [
                    'row_number' => $rowNumber,
                    'data' => $row,
                    'error' => $e->getMessage(),
                ];
                $errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
            }
        }

        return [
            'valid' => $validData,
            'invalid' => $invalidData,
            'errors' => $errors,
        ];
    }

    /**
     * Cek apakah baris kosong
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (!empty(trim((string) $cell))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Parse headers dan mapping ke field database
     */
    protected function parseHeaders(array $headerRow): array
    {
        $headers = [];

        foreach ($headerRow as $index => $header) {
            if (empty($header)) {
                continue;
            }

            $normalizedHeader = $this->normalizeHeader($header);
            
            // Cek di header aliases dulu
            if (isset($this->headerAliases[$normalizedHeader])) {
                $headers[$index] = $this->headerAliases[$normalizedHeader];
            }
            // Lalu cek di header mapping
            elseif (isset($this->headerMapping[$normalizedHeader])) {
                $headers[$index] = $this->headerMapping[$normalizedHeader];
            }
            // Default: gunakan header asli (normalized)
            else {
                $headers[$index] = $normalizedHeader;
            }
        }

        return $headers;
    }

    /**
     * Normalize header string
     */
    protected function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = preg_replace('/\s+/', ' ', $header);
        return $header;
    }

    /**
     * Parse satu baris data
     */
    protected function parseRow(array $row, array $headers, int $rowNumber): array
    {
        $data = [];

        foreach ($headers as $index => $field) {
            $value = $row[$index] ?? null;
            $data[$field] = $this->parseValue($field, $value);
        }

        // Validasi field wajib
        if (empty($data['nrp_nip'])) {
            throw new Exception("NRP/NIP tidak boleh kosong (wajib diisi sebagai primary key)");
        }
        
        if (empty($data['nama'])) {
            throw new Exception("Nama tidak boleh kosong");
        }

        if (empty($data['tanggal_periksa'])) {
            throw new Exception("Tanggal Periksa tidak boleh kosong");
        }

        // Format nomor HP
        if (!empty($data['no_hp_raw'])) {
            $data['no_hp_wa'] = $this->formatPhoneE164($data['no_hp_raw']);
        }

        return $data;
    }

    /**
     * Parse value berdasarkan field type
     */
    protected function parseValue(string $field, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle date fields
        if (in_array($field, ['tanggal_lahir', 'tanggal_periksa'])) {
            $fieldLabel = $field === 'tanggal_lahir' ? 'Tgl Lahir' : 'Tgl Periksa';
            return $this->parseDate($value, $fieldLabel);
        }

        // Handle numeric as string (NRP/NIP, No Lab, etc)
        if (in_array($field, ['nrp_nip', 'no_lab', 'kode_paket', 'kode_instansi'])) {
            return (string) $value;
        }

        // Handle phone number
        if (in_array($field, ['no_hp_raw'])) {
            return $this->sanitizePhone($value);
        }

        // Handle gender
        if ($field === 'jenis_kelamin') {
            return $this->normalizeGender($value);
        }

        return trim((string) $value);
    }

    /**
     * Parse date dari berbagai format dengan validasi ketat
     * 
     * @throws Exception jika format tanggal tidak valid
     */
    protected function parseDate($value, string $fieldName = 'tanggal'): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parsedDate = null;

        // Jika berupa numeric (Excel serial date)
        if (is_numeric($value)) {
            try {
                $parsedDate = Carbon::instance(Date::excelToDateTimeObject($value));
            } catch (Exception $e) {
                throw new Exception("Format {$fieldName} tidak valid: {$value}");
            }
        }

        // Jika berupa string dengan format dd/mm/yyyy atau mm/dd/yyyy
        if (is_string($value) && $parsedDate === null) {
            $value = trim($value);
            
            // Coba parse dengan berbagai format
            // Format Indonesia: dd/mm/yyyy (WAJIB, tidak ada fallback ke format US)
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $value, $matches)) {
                $day = (int) $matches[1];
                $month = (int) $matches[2];
                $year = (int) $matches[3];
                
                // Konversi 2 digit year ke 4 digit
                if ($year < 100) {
                    $year = $year > 50 ? 1900 + $year : 2000 + $year;
                }
                
                // Validasi bulan harus 1-12 (FORMAT INDONESIA: dd/mm/yyyy = TANGGAL/BULAN/TAHUN)
                if ($month < 1 || $month > 12) {
                    throw new Exception("{$fieldName} tidak valid: '{$value}' → Posisi ke-2 (bulan) adalah {$month}, tapi bulan harus 1-12. Format yang benar: TANGGAL/BULAN/TAHUN (contoh: 20/01/2025 = 20 Januari 2025)");
                }
                
                // Validasi hari harus 1-31
                if ($day < 1 || $day > 31) {
                    throw new Exception("{$fieldName} tidak valid: {$value} (tanggal {$day} tidak ada, harus 1-31)");
                }
                
                // Validasi tanggal dengan checkdate (cek kalender)
                if (!checkdate($month, $day, $year)) {
                    throw new Exception("{$fieldName} tidak valid: {$value} (tanggal {$day}/{$month}/{$year} tidak ada di kalender)");
                }
                
                $parsedDate = Carbon::createFromDate($year, $month, $day);
            }
            // Format yyyy-mm-dd
            elseif (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $value, $matches)) {
                $year = (int) $matches[1];
                $month = (int) $matches[2];
                $day = (int) $matches[3];
                
                if (!checkdate($month, $day, $year)) {
                    throw new Exception("Tanggal tidak valid: {$value} (bulan {$month} hari {$day} tahun {$year} tidak ada di kalender)");
                }
                
                $parsedDate = Carbon::createFromDate($year, $month, $day);
            }
            else {
                throw new Exception("Format tanggal tidak dikenali: {$value} (gunakan format dd/mm/yyyy atau yyyy-mm-dd)");
            }
        }

        // Validasi akhir: pastikan tanggal masuk akal
        if ($parsedDate !== null) {
            // Validasi tahun (tidak terlalu jauh di masa depan atau masa lalu)
            $year = $parsedDate->year;
            if ($year < 1900 || $year > 2100) {
                throw new Exception("Tahun tidak valid: {$year} (harus antara 1900-2100)");
            }
        }

        return $parsedDate;
    }

    /**
     * Normalize gender value
     */
    protected function normalizeGender($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['pria', 'laki-laki', 'laki', 'l', 'male', 'm'])) {
            return 'Pria';
        }

        if (in_array($value, ['wanita', 'perempuan', 'p', 'female', 'f'])) {
            return 'Wanita';
        }

        return ucfirst($value);
    }

    /**
     * Sanitize phone number
     */
    protected function sanitizePhone($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Convert to string dan hapus karakter non-digit
        $phone = preg_replace('/[^0-9+]/', '', (string) $value);

        // Handle scientific notation (jika dari Excel)
        if (is_numeric($value) && strpos((string) $value, 'E') !== false) {
            $phone = number_format($value, 0, '', '');
        }

        return $phone;
    }

    /**
     * Format phone number to E164
     */
    protected function formatPhoneE164(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Hapus karakter non-digit kecuali +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Jika dimulai dengan 0, ganti dengan +62
        if (str_starts_with($phone, '0')) {
            $phone = '+62' . substr($phone, 1);
        }
        // Jika dimulai dengan 62, tambah +
        elseif (str_starts_with($phone, '62')) {
            $phone = '+' . $phone;
        }
        // Jika belum ada +, tambahkan +62
        elseif (!str_starts_with($phone, '+')) {
            $phone = '+62' . $phone;
        }

        return $phone;
    }
}
