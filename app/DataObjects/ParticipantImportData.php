<?php

namespace App\DataObjects;

use Carbon\Carbon;

class ParticipantImportData
{
    public function __construct(
        public readonly string $name,
        public readonly string $rank,
        public readonly string $nrpNip,
        public readonly string $position,
        public readonly string $unit,
        public readonly string $phoneRaw,
        public readonly string $phoneE164,
        public readonly Carbon $birthDate,
        public readonly string $gender,
        public readonly string $labNumber,
        public readonly Carbon $examDate,
        public readonly int $packageId,
        public readonly string $packageCode,
        public readonly int $companyId,
        public readonly string $companyCode,
        public readonly int $rowNumber
    ) {}

    public static function fromArray(array $data, int $rowNumber): self
    {
        return new self(
            name: $data['name'],
            rank: $data['rank'],
            nrpNip: $data['nrp_nip'],
            position: $data['position'],
            unit: $data['unit'],
            phoneRaw: $data['phone_raw'],
            phoneE164: $data['phone_e164'],
            birthDate: $data['birth_date'],
            gender: $data['gender'],
            labNumber: $data['lab_number'],
            examDate: $data['exam_date'],
            packageId: $data['package_id'],
            packageCode: $data['package_code'],
            companyId: $data['company_id'],
            companyCode: $data['company_code'],
            rowNumber: $rowNumber,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'rank' => $this->rank,
            'nrp_nip' => $this->nrpNip,
            'position' => $this->position,
            'unit' => $this->unit,
            'phone_raw' => $this->phoneRaw,
            'phone_e164' => $this->phoneE164,
            'birth_date' => $this->birthDate->toDateString(),
            'gender' => $this->gender,
            'lab_number' => $this->labNumber,
            'exam_date' => $this->examDate->toDateString(),
            'package_id' => $this->packageId,
            'package_code' => $this->packageCode,
            'company_id' => $this->companyId,
            'company_code' => $this->companyCode,
        ];
    }
}
