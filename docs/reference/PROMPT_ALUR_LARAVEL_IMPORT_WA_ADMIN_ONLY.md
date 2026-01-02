# PRD + Prompt Pengembangan (Laravel + Livewire + Tailwind + MySQL)
**Nama proyek:** Admin Dashboard — Import Data Pemeriksaan & Pengiriman WhatsApp (berbasis persetujuan/opt‑in)  
**Stack:** Laravel (Livewire Starter Kit), TailwindCSS, MySQL, Queue (database/redis)

> Catatan kepatuhan: Fitur WhatsApp wajib mematuhi kebijakan WhatsApp/Meta dan hukum setempat. Sistem ini **hanya** boleh mengirim pesan ke nomor yang **sudah memberikan persetujuan (opt‑in)**. Sertakan audit log, rate limit, dan mekanisme opt‑out.

---

## 1) Gambaran Produk
Aplikasi web internal untuk **admin** yang memiliki fitur:
1) **Upload & import** data peserta dari Excel ke database.  
2) **Data Anggota**: daftar peserta, status sudah/belum WA, status sudah/belum ada file.  
3) **Pengaturan**:
   - Pengaturan Paket
   - Pengaturan Instansi/Perusahaan (mengatur **kata prolog** untuk pesan WA)  
4) **Upload File**:
   - Upload Data (Excel)
   - Upload File (lampiran peserta)  
5) **Setting Akun (admin)**:
   - Karena tidak ada register/forgot, akun admin dibuat/diatur dari panel ini (untuk multi-admin jika dibutuhkan).

---

## 2) Autentikasi & Akses (HANYA ADMIN)
### 2.1 Halaman awal
- Halaman awal aplikasi adalah **/login**
- **Tidak ada** register dan forgot password
- Hanya ada **login admin**

### 2.2 Role
- **Tidak ada role user.**
- Semua akun yang dapat login adalah **admin** dan memiliki akses yang sama ke seluruh data.

### 2.3 Keamanan minimal
- Middleware auth untuk semua route (kecuali login).
- Proteksi brute-force (Laravel throttle).
- Opsional: `is_active` untuk menonaktifkan akun admin tertentu.

---

## 3) Struktur Menu & Fitur
### 3.1 Dashboard (Admin)
Ringkasan:
- Total peserta
- Sudah terkirim WA
- Belum terkirim WA
- Sudah ada file lampiran
- Belum ada file lampiran
- Import terakhir (file + timestamp)
- Grafik ringkas (opsional)

### 3.2 Data Anggota
Tabel peserta dengan kolom utama:
- Nama, Pangkat, NRP/NIP, Jabatan, Satuan Kerja, No HP
- Tgl Lahir, Gender, No Lab, Tgl Pemeriksaan
- Kode Paket, Kode Perusahaan
- Status WA: Belum / Antri / Terkirim / Gagal
- Status File: Ada / Tidak
- Aksi: Detail, Upload Lampiran, Kirim WA (jika memenuhi syarat), Lihat Log

Fitur tabel:
- Search (nama / NRP/NIP / no_lab / no_hp)
- Filter (tanggal pemeriksaan, kode_paket, kode_perusahaan, status WA, status file)
- Sort & pagination
- Bulk action **yang aman**:
  - “Antrikan Kirim WA” hanya untuk peserta yang **sudah opt‑in** + **belum terkirim**
  - Batas batch (misal max 50) + rate limit

### 3.3 Pengaturan
#### 3.3.1 Pengaturan Paket
CRUD Paket:
- `kode_paket` (unik)
- `nama_paket`
- `deskripsi` (opsional)
- `aktif` (bool)

#### 3.3.2 Pengaturan Instansi/Perusahaan
CRUD Instansi/Perusahaan:
- `kode_perusahaan` (unik)
- `nama_instansi`
- `prolog_template` (teks prolog WA)
- `footer_template` (opsional)
- `aktif` (bool)

### 3.4 Upload File
#### 3.4.1 Upload Data (Excel Import)
- Upload `.xlsx/.xls`
- Mode: `preview` → `commit`
- Strategi duplikasi: `skip` / `update` / `fail`
- Output:
  - Preview baris valid & invalid + alasan
  - Summary import (total/sukses/gagal)

#### 3.4.2 Upload File (Lampiran)
- Upload lampiran dari detail peserta
- Menandai peserta `has_attachment = true`
- Menyimpan metadata lampiran & path file

### 3.5 Setting Akun (Admin)
- CRUD akun admin:
  - name, email (unik), password, is_active
- Tidak ada role.

---

## 4) Template Excel & Mapping Kolom
Template Excel memiliki header berikut (baris 1):

| Kolom | Header di Excel |
|---|---|
| A | `Nama` |
| B | `Pangkat` |
| C | `NRP/NIP` |
| D | `Jabatan` |
| E | `satuan_kerja` |
| F | `no_hp` |
| G | `tgl_lahir(dd/mm/yyyy)` |
| H | `gender(Pria/Wanita)` |
| I | `no_lab` |
| J | `tgl_pemeriksaan(dd/mm/yyyy)` |
| K | **(kosong / diabaikan)** |
| L | `kode_paket` |
| M | `kode_perusahaan` |

**Aturan mapping:**
- Wajib **header-based mapping** (jangan pakai index kolom) karena ada kolom K kosong.
- Normalisasi header: lowercase, trim, ganti non-alnum menjadi `_`.

**Validasi import:**
- `Nama` wajib
- `no_hp` wajib → normalisasi ke format **E.164** (contoh Indonesia: `08...` → `628...`)
- `tgl_lahir` & `tgl_pemeriksaan` → parse `dd/mm/yyyy` atau Excel date serial
- `gender` hanya `Pria` / `Wanita`
- `kode_paket` & `kode_perusahaan` harus ada di master (atau admin boleh auto-create jika diaktifkan)

**Kunci unik duplikasi (disarankan):**
- `no_lab + tgl_pemeriksaan + kode_perusahaan`

---

## 5) Desain Database (Migrations)
### 5.1 users (admin)
- id
- name
- email (unique)
- password
- is_active (bool)
- timestamps

### 5.2 packages
- id
- code (unique)  → `kode_paket`
- name
- description (nullable)
- is_active (bool)
- timestamps

### 5.3 companies (instansi/perusahaan)
- id
- code (unique) → `kode_perusahaan`
- name
- prolog_template (text)
- footer_template (text, nullable)
- is_active (bool)
- timestamps

### 5.4 participants (anggota/peserta)
- id
- name
- rank (pangkat)
- nrp_nip
- position (jabatan)
- unit (satuan_kerja)
- phone_raw
- phone_e164
- birth_date (date)
- gender (string)
- lab_number (no_lab)
- exam_date (date)
- package_id (FK packages.id, nullable)
- package_code (string, opsional untuk trace)
- company_id (FK companies.id, nullable)
- company_code (string)
- wa_opt_in_at (datetime, nullable)  **WA hanya boleh dikirim jika terisi**
- wa_opt_in_source (string, nullable)
- wa_status (enum: `not_sent|queued|sent|failed`)
- wa_sent_at (datetime, nullable)
- wa_last_error (text, nullable)
- has_attachment (bool default false)
- timestamps
- unique index: (lab_number, exam_date, company_code)

### 5.5 uploads
- id
- uploaded_by (FK users.id) *(audit)*
- type (enum: `data_excel|attachment`)
- original_name
- stored_path
- mime
- size
- status (enum: `uploaded|parsed|imported|failed`)
- total_rows (int, nullable)
- success_rows (int, nullable)
- failed_rows (int, nullable)
- error_summary (text, nullable)
- timestamps

### 5.6 participant_attachments
- id
- participant_id (FK)
- upload_id (FK uploads.id, nullable)
- original_name
- stored_path
- mime
- size
- timestamps

### 5.7 wa_messages (log pengiriman)
- id
- participant_id (FK)
- created_by (FK users.id) *(audit)*
- provider (string: `meta_cloud_api|twilio|other`)
- to_phone_e164
- message_body (text)
- status (enum: `queued|sent|failed`)
- provider_message_id (string, nullable)
- error_message (text, nullable)
- sent_at (datetime, nullable)
- attempts (int default 0)
- timestamps

---

## 6) Alur UX Detail
### 6.1 Login (Admin)
1. Buka URL → redirect ke `/login`
2. Login sukses → `/dashboard`
3. Login gagal → tampilkan error

### 6.2 Upload Data (Excel)
1. Menu: **Upload File → Upload data**
2. Upload file excel
3. Sistem:
   - Simpan file ke storage
   - Create record `uploads` status `uploaded`
   - Parsing pakai `maatwebsite/excel`
   - Validasi per baris + siapkan data preview
4. Halaman **Preview**:
   - Total baris
   - Baris valid & invalid + alasan
5. Klik **Import/Commit**:
   - Terapkan strategi duplikasi (skip/update/fail)
   - Insert/update ke tabel `participants`
   - Update `uploads` status `imported` + summary
6. Redirect ke **Data Anggota** + notifikasi hasil import

### 6.3 Upload Lampiran
1. Buka detail peserta
2. Upload lampiran
3. Sistem simpan file + create `participant_attachments`
4. Update `participants.has_attachment = true`

### 6.4 Kirim WhatsApp (Opt‑in Wajib)
**Tombol “Kirim WA” aktif hanya jika:**
- `wa_opt_in_at` terisi
- `phone_e164` valid
- `wa_status` bukan `sent` dan bukan `queued`

Alur:
1. Klik **Kirim WA**
2. Sistem compose pesan:
   - Prolog dari `companies.prolog_template`
   - Isi pesan (mis. no_lab, tgl pemeriksaan, instruksi, dll)
   - Footer (opsional)
3. Preview pesan + konfirmasi
4. Enqueue job:
   - Create `wa_messages` status queued
   - Update `participants.wa_status = queued`
5. Worker mengirim via provider resmi:
   - sukses → update status `sent`
   - gagal → update `failed` + isi error

**Rate limit wajib** (contoh 1 msg/detik) + batch limit max 50.

---

## 7) Integrasi WhatsApp (Resmi & Aman)
- Gunakan API resmi:
  - Meta WhatsApp Business Platform (Cloud API), atau
  - Twilio WhatsApp API

Catatan:
- Pesan pertama sering butuh **template pre-approved** (tergantung kebijakan provider).
- Pesan free-form biasanya mengikuti window tertentu setelah user membalas (mis. 24 jam).

ENV contoh:
- `WA_PROVIDER=meta_cloud_api`
- `WA_PHONE_NUMBER_ID=...`
- `WA_ACCESS_TOKEN=...`
- `WA_TEMPLATE_NAME=...`
- `WA_RATE_LIMIT_PER_SECOND=1`

Buat:
- `WhatsAppService`
- `SendWhatsAppMessageJob` (retry + log + rate limiting)

---

## 8) Struktur Livewire yang Disarankan
- `Dashboard/Index`
- `Participants/Index` (table + filters)
- `Participants/Show` (detail)
- `Participants/UploadAttachmentModal`
- `Uploads/DataImport` (upload + preview + commit)
- `Settings/Packages/Index` + Form
- `Settings/Companies/Index` + Form (prolog template)
- `Accounts/AdminUsers/Index` + Form
- `WhatsApp/Logs` (opsional)

---

## 9) Routing (contoh)
- Auth:
  - `GET /login`, `POST /login`, `POST /logout`
- App (auth-only):
  - `GET /dashboard`
  - `GET /participants`
  - `GET /participants/{id}`
  - `POST /participants/{id}/attachments`
  - `POST /participants/{id}/send-wa`
  - `GET /uploads/data`
  - `POST /uploads/data` (upload)
  - `POST /uploads/data/preview`
  - `POST /uploads/data/commit`
- Settings (auth-only):
  - `/settings/packages`
  - `/settings/companies`
  - `/settings/admin-users`

---

## 10) Checklist Implementasi (Task Breakdown)
1. Scaffold auth login-only + seed admin default.
2. Buat migrations + models + factories.
3. Buat layout Tailwind + navigasi menu admin.
4. Implement master data packages & companies.
5. Implement upload excel + parsing + preview + commit import.
6. Implement participants list + detail + attachment upload.
7. Implement WA compose + preview + enqueue job + log.
8. Implement queue worker + rate limit + retry.
9. Testing minimal (Pest): import parsing + normalisasi no_hp + duplikasi.
10. Hardening: validasi file, audit log, error reporting.

---

## 11) Acceptance Criteria
- Aplikasi selalu mulai di `/login`, tanpa register/forgot.
- Semua akun yang bisa login adalah admin dan punya akses penuh.
- Import excel punya preview valid/invalid dan commit menyimpan data sesuai strategi duplikasi.
- Data Anggota bisa search/filter dan menampilkan status WA & status file.
- Upload lampiran bekerja dan menandai peserta “Ada file”.
- WA hanya bisa dikirim jika opt‑in, pengiriman via queue, ada log sent/failed, ada throttle.

---

## 12) PROMPT UNTUK AI CODING (SIAP COPY-PASTE)
Gunakan prompt di bawah ini jika Anda ingin AI menghasilkan kode proyek secara end-to-end.

---
### Prompt
Anda adalah Senior Laravel Engineer. Buat aplikasi **Admin Dashboard** Laravel + Livewire + Tailwind + MySQL dengan spesifikasi berikut:

1) Halaman awal `/login` (tanpa register, tanpa forgot password). Semua akun dibuat dari panel admin.
2) **Hanya ada 1 tipe akses: admin.** Tidak ada role user. Semua admin bisa melihat dan mengelola seluruh data.
3) Menu: Dashboard, Data Anggota, Pengaturan (Paket & Instansi/Perusahaan dengan prolog WA), Upload File (Upload data excel & Upload lampiran), Setting Akun (admin).
4) Upload data excel menggunakan **header-based mapping** (abaikan kolom kosong K). Header:
   `Nama`, `Pangkat`, `NRP/NIP`, `Jabatan`, `satuan_kerja`, `no_hp`, `tgl_lahir(dd/mm/yyyy)`, `gender(Pria/Wanita)`, `no_lab`, `tgl_pemeriksaan(dd/mm/yyyy)`, `kode_paket`, `kode_perusahaan`.
   - Parse tanggal dari string dd/mm/yyyy atau serial excel.
   - Normalisasi no_hp ke E.164.
   - Validasi gender hanya Pria/Wanita.
   - Buat preview (valid/invalid + alasan), lalu commit import.
   - Tangani duplikasi dengan mode: skip/update/fail berdasarkan unique key `no_lab + tgl_pemeriksaan + kode_perusahaan`.
5) Data Anggota: table + filter + search + status WA + status file. Detail peserta bisa upload lampiran.
6) WhatsApp messaging: **opt-in wajib** (`wa_opt_in_at` terisi). Implement service + job queue untuk pengiriman via provider resmi (Meta Cloud API/Twilio). Simpan log pada tabel `wa_messages`, update status peserta (`queued/sent/failed`). Terapkan rate limiting dan batch limit.
7) Buat migrations, models, Livewire components, routes, dan UI Tailwind rapi. Sertakan seeder admin default + halaman manajemen akun admin.
8) Sertakan testing dasar (Pest) untuk import parsing, normalisasi nomor, dan aturan duplikasi (WA service boleh dimock).

Hasilkan:
- Struktur folder & file yang dibuat
- Migrations + Eloquent models
- Livewire components utama & view
- Import class untuk excel (maatwebsite/excel)
- WhatsAppService + SendWhatsAppMessageJob (dengan rate limit)
- Instruksi .env & cara menjalankan queue

Pastikan kode clean, aman, dan mematuhi kebijakan WhatsApp (opt-in wajib).
---
