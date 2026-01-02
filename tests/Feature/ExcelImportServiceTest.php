<?php

use App\Models\Company;
use App\Models\Package;
use App\Services\ExcelImportService;

beforeEach(function () {
    Package::factory()->create(['code' => 'PKG001', 'name' => 'Package 1']);
    Package::factory()->create(['code' => 'PKG002', 'name' => 'Package 2']);
    Company::factory()->create(['code' => 'COM001', 'name' => 'Company 1']);
    Company::factory()->create(['code' => 'COM002', 'name' => 'Company 2']);
});

test('service can be instantiated', function () {
    $service = new ExcelImportService;
    expect($service)->toBeInstanceOf(ExcelImportService::class);
});

test('normalize phone converts 08 to +628', function () {
    $service = new ExcelImportService;

    expect($service->normalizePhone('08123456789'))->toBe('+628123456789')
        ->and($service->normalizePhone('08555777788'))->toBe('+628555777788')
        ->and($service->normalizePhone('0812-345-6789'))->toBe('+628123456789');
});

test('normalize phone adds +62 if missing', function () {
    $service = new ExcelImportService;

    expect($service->normalizePhone('8123456789'))->toBe('+628123456789');
});

test('normalize phone keeps existing +62', function () {
    $service = new ExcelImportService;

    expect($service->normalizePhone('+628123456789'))->toBe('+628123456789')
        ->and($service->normalizePhone('628123456789'))->toBe('+628123456789');
});

test('parse date handles dd/mm/yyyy format', function () {
    $service = new ExcelImportService;

    $date = $service->parseDate('25/12/2025');
    expect($date)->not->toBeNull()
        ->and($date->format('Y-m-d'))->toBe('2025-12-25');
});

test('parse date handles excel serial dates', function () {
    $service = new ExcelImportService;

    $date = $service->parseDate(44927);
    expect($date)->not->toBeNull()
        ->and($date->format('Y-m-d'))->toBe('2023-01-01');
});

test('parse date returns null for invalid format', function () {
    $service = new ExcelImportService;

    expect($service->parseDate('invalid'))->toBeNull()
        ->and($service->parseDate('not-a-date'))->toBeNull()
        ->and($service->parseDate(''))->toBeNull();
});

test('validate row with valid data returns valid', function () {
    $service = new ExcelImportService;

    $row = [
        'nama' => 'John Doe',
        'pangkat' => 'Staff',
        'nrp_nip' => '12345',
        'jabatan' => 'Manager',
        'satuan_kerja' => 'IT',
        'no_hp' => '08123456789',
        'tgl_lahir' => '01/01/1990',
        'gender' => 'Pria',
        'no_lab' => 'LAB001',
        'tgl_pemeriksaan' => '25/12/2025',
        'kode_paket' => 'PKG001',
        'kode_perusahaan' => 'COM001',
    ];

    $result = $service->validateRow($row);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty()
        ->and($result['data']['name'])->toBe('John Doe')
        ->and($result['data']['phone_e164'])->toBe('+628123456789')
        ->and($result['data']['gender'])->toBe('Pria')
        ->and($result['data']['package_code'])->toBe('PKG001')
        ->and($result['data']['company_code'])->toBe('COM001');
});

test('validate row fails with invalid gender', function () {
    $service = new ExcelImportService;

    $row = [
        'nama' => 'John Doe',
        'pangkat' => 'Staff',
        'nrp_nip' => '12345',
        'jabatan' => 'Manager',
        'satuan_kerja' => 'IT',
        'no_hp' => '08123456789',
        'tgl_lahir' => '01/01/1990',
        'gender' => 'Invalid',
        'no_lab' => 'LAB001',
        'tgl_pemeriksaan' => '25/12/2025',
        'kode_paket' => 'PKG001',
        'kode_perusahaan' => 'COM001',
    ];

    $result = $service->validateRow($row);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Gender must be "Pria" or "Wanita", got: Invalid');
});

test('validate row fails with invalid package code', function () {
    $service = new ExcelImportService;

    $row = [
        'nama' => 'John Doe',
        'pangkat' => 'Staff',
        'nrp_nip' => '12345',
        'jabatan' => 'Manager',
        'satuan_kerja' => 'IT',
        'no_hp' => '08123456789',
        'tgl_lahir' => '01/01/1990',
        'gender' => 'Pria',
        'no_lab' => 'LAB001',
        'tgl_pemeriksaan' => '25/12/2025',
        'kode_paket' => 'INVALID_PKG',
        'kode_perusahaan' => 'COM001',
    ];

    $result = $service->validateRow($row);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Kode Paket \'INVALID_PKG\' does not exist');
});

test('validate row fails with invalid company code', function () {
    $service = new ExcelImportService;

    $row = [
        'nama' => 'John Doe',
        'pangkat' => 'Staff',
        'nrp_nip' => '12345',
        'jabatan' => 'Manager',
        'satuan_kerja' => 'IT',
        'no_hp' => '08123456789',
        'tgl_lahir' => '01/01/1990',
        'gender' => 'Pria',
        'no_lab' => 'LAB001',
        'tgl_pemeriksaan' => '25/12/2025',
        'kode_paket' => 'PKG001',
        'kode_perusahaan' => 'INVALID_COM',
    ];

    $result = $service->validateRow($row);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toContain('Kode Perusahaan \'INVALID_COM\' does not exist');
});

test('validate row fails with missing required fields', function () {
    $service = new ExcelImportService;

    $row = [
        'nama' => '',
        'pangkat' => '',
        'nrp_nip' => '',
        'jabatan' => '',
        'satuan_kerja' => '',
        'no_hp' => '',
        'tgl_lahir' => '',
        'gender' => '',
        'no_lab' => '',
        'tgl_pemeriksaan' => '',
        'kode_paket' => '',
        'kode_perusahaan' => '',
    ];

    $result = $service->validateRow($row);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toHaveCount(12);
});

test('validate row accepts Wanita as gender', function () {
    $service = new ExcelImportService;

    $row = [
        'nama' => 'Jane Doe',
        'pangkat' => 'Staff',
        'nrp_nip' => '12345',
        'jabatan' => 'Manager',
        'satuan_kerja' => 'IT',
        'no_hp' => '08123456789',
        'tgl_lahir' => '01/01/1990',
        'gender' => 'Wanita',
        'no_lab' => 'LAB001',
        'tgl_pemeriksaan' => '25/12/2025',
        'kode_paket' => 'PKG001',
        'kode_perusahaan' => 'COM001',
    ];

    $result = $service->validateRow($row);

    expect($result['valid'])->toBeTrue()
        ->and($result['errors'])->toBeEmpty()
        ->and($result['data']['gender'])->toBe('Wanita');
});
