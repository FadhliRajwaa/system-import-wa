<?php

namespace App\DataObjects;

class ImportResult
{
    public function __construct(
        public readonly array $validRows,
        public readonly array $invalidRows,
        public readonly array $errors
    ) {}

    public function totalRows(): int
    {
        return count($this->validRows) + count($this->invalidRows);
    }

    public function successCount(): int
    {
        return count($this->validRows);
    }

    public function failureCount(): int
    {
        return count($this->invalidRows);
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors) || ! empty($this->invalidRows);
    }

    public function toArray(): array
    {
        return [
            'total_rows' => $this->totalRows(),
            'success_count' => $this->successCount(),
            'failure_count' => $this->failureCount(),
            'valid_rows' => $this->validRows,
            'invalid_rows' => $this->invalidRows,
            'errors' => $this->errors,
        ];
    }
}
