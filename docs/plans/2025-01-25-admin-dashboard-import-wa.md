# Admin Dashboard Import WA Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build admin dashboard for importing participant data from Excel and managing WhatsApp messages with opt-in compliance.

**Architecture:** Laravel 12 + Livewire 3 + Tailwind CSS 4 + MySQL with queue-based WhatsApp messaging and header-based Excel import with preview/commit workflow.

**Tech Stack:** Laravel 12, Livewire 3, Flux UI, Tailwind CSS 4, MySQL, maatwebsite/excel, Pest testing

---

## Phase 1: Authentication Setup (Login Only)

### Task 1: Configure Fortify for login-only

**Files:**
- Modify: `config/fortify.php`
- Modify: `bootstrap/providers.php`

**Step 1: Update Fortify configuration**

Modify `config/fortify.php` to disable register/forgot password:

```php
'features' => [
    // Features::registration(),
    // Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirmPassword' => true,
    ]),
],
```

**Step 2: Remove FortifyServiceProvider if exists**

```bash
rm bootstrap/providers/FortifyServiceProvider.php
```

**Step 3: Update bootstrap/providers.php**

Remove FortifyServiceProvider from providers array if present.

**Step 4: Test**

Run: `php artisan config:clear && php artisan route:list --path=register`
Expected: No registration routes

**Step 5: Commit**

```bash
git add config/fortify.php bootstrap/providers.php
git commit -m "feat: disable registration and password reset features"
```

---

### Task 2: Seed default admin user

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `database/seeders/AdminSeeder.php`

**Step 1: Write test for admin seeder**

```php
// tests/Feature/AdminSeederTest.php
use App\Models\User;

it('creates default admin user', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@example.com')->first();

    expect($admin)
        ->not->toBeNull()
        ->name->toBe('Admin')
        ->email->toBe('admin@example.com')
        ->is_active->toBeTrue();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=creates_default_admin_user`
Expected: FAIL with admin not found

**Step 3: Create AdminSeeder**

```php
// database/seeders/AdminSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
    }
}
```

**Step 4: Update DatabaseSeeder**

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $this->call([
        AdminSeeder::class,
    ]);
}
```

**Step 5: Run seeder and test**

Run: `php artisan db:seed && php artisan test --filter=creates_default_admin_user`
Expected: PASS

**Step 6: Commit**

```bash
git add database/seeders/
git commit -m "feat: add default admin seeder"
```

---

## Phase 2: Database Migrations & Models

### Task 3: Create migrations for packages

**Files:**
- Create: `database/migrations/2024_01_01_000001_create_packages_table.php`

**Step 1: Write test for package model**

```php
// tests/Unit/PackageTest.php
use App\Models\Package;

it('has fillable attributes', function () {
    $package = new Package([
        'code' => 'PKG001',
        'name' => 'Paket A',
        'description' => 'Deskripsi paket',
        'is_active' => true,
    ]);

    expect($package->code)->toBe('PKG001');
    expect($package->name)->toBe('Paket A');
});

it('has unique code', function () {
    Package::factory()->create(['code' => 'PKG001']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Package::factory()->create(['code' => 'PKG001']);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PackageTest`
Expected: FAIL with model not found

**Step 3: Create migration**

```php
// database/migrations/2024_01_01_000001_create_packages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
```

**Step 4: Create Package model**

```php
// app/Models/Package.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
```

**Step 5: Create PackageFactory**

```bash
php artisan make:factory PackageFactory --model=Package
```

```php
// database/factories/PackageFactory.php
namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->lexify('???###')),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
```

**Step 6: Run migration and tests**

Run: `php artisan migrate && php artisan test --filter=PackageTest`
Expected: PASS

**Step 7: Commit**

```bash
git add database/migrations/ app/Models/Package.php database/factories/ tests/Unit/PackageTest.php
git commit -m "feat: create packages table and model"
```

---

### Task 4: Create migrations for companies

**Files:**
- Create: `database/migrations/2024_01_01_000002_create_companies_table.php`

**Step 1: Write test for company model**

```php
// tests/Unit/CompanyTest.php
use App\Models\Company;

it('has fillable attributes', function () {
    $company = new Company([
        'code' => 'COMP001',
        'name' => 'PT Example',
        'prolog_template' => 'Halo {nama}, berikut hasil pemeriksaan Anda.',
        'footer_template' => 'Terima kasih.',
        'is_active' => true,
    ]);

    expect($company->code)->toBe('COMP001');
    expect($company->name)->toBe('PT Example');
});

it('has unique code', function () {
    Company::factory()->create(['code' => 'COMP001']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Company::factory()->create(['code' => 'COMP001']);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CompanyTest`
Expected: FAIL

**Step 3: Create migration**

```php
// database/migrations/2024_01_01_000002_create_companies_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('prolog_template');
            $table->text('footer_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
```

**Step 4: Create Company model**

```php
// app/Models/Company.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'prolog_template',
        'footer_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
```

**Step 5: Create CompanyFactory**

```bash
php artisan make:factory CompanyFactory --model=Company
```

```php
// database/factories/CompanyFactory.php
namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->lexify('???###')),
            'name' => $this->faker->company(),
            'prolog_template' => 'Halo {nama},',
            'footer_template' => 'Terima kasih.',
            'is_active' => true,
        ];
    }
}
```

**Step 6: Run migration and tests**

Run: `php artisan migrate && php artisan test --filter=CompanyTest`
Expected: PASS

**Step 7: Commit**

```bash
git add database/migrations/ app/Models/Company.php database/factories/ tests/Unit/CompanyTest.php
git commit -m "feat: create companies table and model"
```

---

### Task 5: Create migrations for participants

**Files:**
- Create: `database/migrations/2024_01_01_000003_create_participants_table.php`

**Step 1: Write test for participant model**

```php
// tests/Unit/ParticipantTest.php
use App\Models\Participant;
use App\Models\Package;
use App\Models\Company;

it('has fillable attributes', function () {
    $participant = new Participant([
        'name' => 'John Doe',
        'phone_raw' => '08123456789',
        'phone_e164' => '628123456789',
        'wa_status' => 'not_sent',
        'has_attachment' => false,
    ]);

    expect($participant->name)->toBe('John Doe');
    expect($participant->wa_status)->toBe('not_sent');
});

it('has unique constraint', function () {
    Participant::factory()->create([
        'lab_number' => 'LAB001',
        'exam_date' => '2024-01-01',
        'company_code' => 'COMP001',
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Participant::factory()->create([
        'lab_number' => 'LAB001',
        'exam_date' => '2024-01-01',
        'company_code' => 'COMP001',
    ]);
});

it('normalizes phone number to E164', function () {
    $participant = Participant::factory()->create([
        'phone_raw' => '08123456789',
    ]);

    expect($participant->phone_e164)->toBe('628123456789');
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ParticipantTest`
Expected: FAIL

**Step 3: Create migration**

```php
// database/migrations/2024_01_01_000003_create_participants_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rank')->nullable();
            $table->string('nrp_nip')->nullable();
            $table->string('position')->nullable();
            $table->string('unit')->nullable();
            $table->string('phone_raw');
            $table->string('phone_e164');
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('lab_number');
            $table->date('exam_date');
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_code')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_code');
            $table->timestamp('wa_opt_in_at')->nullable();
            $table->string('wa_opt_in_source')->nullable();
            $table->enum('wa_status', ['not_sent', 'queued', 'sent', 'failed'])->default('not_sent');
            $table->timestamp('wa_sent_at')->nullable();
            $table->text('wa_last_error')->nullable();
            $table->boolean('has_attachment')->default(false);
            $table->timestamps();

            $table->unique(['lab_number', 'exam_date', 'company_code']);
            $table->index(['lab_number', 'exam_date', 'company_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
```

**Step 4: Create Participant model**

```php
// app/Models/Participant.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rank',
        'nrp_nip',
        'position',
        'unit',
        'phone_raw',
        'phone_e164',
        'birth_date',
        'gender',
        'lab_number',
        'exam_date',
        'package_id',
        'package_code',
        'company_id',
        'company_code',
        'wa_opt_in_at',
        'wa_opt_in_source',
        'wa_status',
        'wa_sent_at',
        'wa_last_error',
        'has_attachment',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'exam_date' => 'date',
        'wa_opt_in_at' => 'datetime',
        'wa_sent_at' => 'datetime',
        'has_attachment' => 'boolean',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'exam_date' => 'date',
            'wa_opt_in_at' => 'datetime',
            'wa_sent_at' => 'datetime',
            'has_attachment' => 'boolean',
        ];
    }

    public function setPhoneRawAttribute($value)
    {
        $this->attributes['phone_raw'] = $value;
        $this->attributes['phone_e164'] = $this->normalizeToE164($value);
    }

    protected function normalizeToE164($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function attachments()
    {
        return $this->hasMany(ParticipantAttachment::class);
    }

    public function waMessages()
    {
        return $this->hasMany(WaMessage::class);
    }

    public function scopeWaSendable($query)
    {
        return $query->whereNotNull('wa_opt_in_at')
            ->where('wa_status', 'not_sent')
            ->whereNotNull('phone_e164');
    }
}
```

**Step 5: Create ParticipantFactory**

```bash
php artisan make:factory ParticipantFactory --model=Participant
```

```php
// database/factories/ParticipantFactory.php
namespace Database\Factories;

use App\Models\Participant;
use App\Models\Package;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $companyCode = 'COMP' . $this->faker->randomNumber(3);

        return [
            'name' => $this->faker->name(),
            'rank' => $this->faker->randomElement(['Pangkat A', 'Pangkat B', 'Pangkat C']),
            'nrp_nip' => $this->faker->unique()->numerify('#############'),
            'position' => $this->faker->jobTitle(),
            'unit' => $this->faker->company(),
            'phone_raw' => '08' . $this->faker->numerify('#########'),
            'phone_e164' => null, // Will be set by mutator
            'birth_date' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['Pria', 'Wanita']),
            'lab_number' => 'LAB' . $this->faker->unique()->numerify('######'),
            'exam_date' => $this->faker->date(),
            'package_id' => Package::factory(),
            'package_code' => 'PKG' . $this->faker->numerify('###'),
            'company_id' => Company::factory(),
            'company_code' => $companyCode,
            'wa_opt_in_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'wa_opt_in_source' => 'manual',
            'wa_status' => 'not_sent',
            'has_attachment' => false,
        ];
    }
}
```

**Step 6: Run migration and tests**

Run: `php artisan migrate && php artisan test --filter=ParticipantTest`
Expected: PASS

**Step 7: Commit**

```bash
git add database/migrations/ app/Models/Participant.php database/factories/ tests/Unit/ParticipantTest.php
git commit -m "feat: create participants table and model with phone normalization"
```

---

### Task 6: Create migrations for uploads

**Files:**
- Create: `database/migrations/2024_01_01_000004_create_uploads_table.php`

**Step 1: Create migration**

```php
// database/migrations/2024_01_01_000004_create_uploads_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->enum('type', ['data_excel', 'attachment']);
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->enum('status', ['uploaded', 'parsed', 'imported', 'failed'])->default('uploaded');
            $table->integer('total_rows')->nullable();
            $table->integer('success_rows')->nullable();
            $table->integer('failed_rows')->nullable();
            $table->text('error_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
```

**Step 2: Create Upload model**

```php
// app/Models/Upload.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by',
        'type',
        'original_name',
        'stored_path',
        'mime',
        'size',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_summary',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
```

**Step 3: Create UploadFactory**

```bash
php artisan make:factory UploadFactory --model=Upload
```

**Step 4: Run migration**

Run: `php artisan migrate`
Expected: Migration successful

**Step 5: Commit**

```bash
git add database/migrations/ app/Models/Upload.php
git commit -m "feat: create uploads table and model"
```

---

### Task 7: Create migrations for participant_attachments

**Files:**
- Create: `database/migrations/2024_01_01_000005_create_participant_attachments_table.php`

**Step 1: Create migration**

```php
// database/migrations/2024_01_01_000005_create_participant_attachments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('upload_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_attachments');
    }
};
```

**Step 2: Create ParticipantAttachment model**

```php
// app/Models/ParticipantAttachment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'upload_id',
        'original_name',
        'stored_path',
        'mime',
        'size',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}
```

**Step 3: Create ParticipantAttachmentFactory**

```bash
php artisan make:factory ParticipantAttachmentFactory --model=ParticipantAttachment
```

**Step 4: Run migration**

Run: `php artisan migrate`
Expected: Migration successful

**Step 5: Commit**

```bash
git add database/migrations/ app/Models/ParticipantAttachment.php
git commit -m "feat: create participant_attachments table and model"
```

---

### Task 8: Create migrations for wa_messages

**Files:**
- Create: `database/migrations/2024_01_01_000006_create_wa_messages_table.php`

**Step 1: Create migration**

```php
// database/migrations/2024_01_01_000006_create_wa_messages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('provider')->default('meta_cloud_api');
            $table->string('to_phone_e164');
            $table->text('message_body');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
    }
};
```

**Step 2: Create WaMessage model**

```php
// app/Models/WaMessage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'created_by',
        'provider',
        'to_phone_e164',
        'message_body',
        'status',
        'provider_message_id',
        'error_message',
        'sent_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeQueued($query)
    {
        return $query->where('status', 'queued');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
```

**Step 3: Create WaMessageFactory**

```bash
php artisan make:factory WaMessageFactory --model=WaMessage
```

**Step 4: Run migration**

Run: `php artisan migrate`
Expected: Migration successful

**Step 5: Commit**

```bash
git add database/migrations/ app/Models/WaMessage.php
git commit -m "feat: create wa_messages table and model"
```

---

## Phase 3: Excel Import with Header Mapping

### Task 9: Install maatwebsite/excel package

**Files:**
- Modify: `composer.json`

**Step 1: Add package**

```bash
composer require maatwebsite/excel
```

**Step 2: Verify installation**

Run: `composer show maatwebsite/excel`
Expected: Package version displayed

**Step 3: Publish config (optional)**

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

**Step 4: Commit**

```bash
git add composer.json composer.lock config/excel.php
git commit -m "feat: install maatwebsite/excel package"
```

---

### Task 10: Create Excel import service

**Files:**
- Create: `app/Services/ExcelImportService.php`

**Step 1: Write test for date parsing**

```php
// tests/Unit/ExcelImportServiceTest.php
use App\Services\ExcelImportService;

it('parses date from dd/mm/yyyy format', function () {
    $service = new ExcelImportService();

    $date = $service->parseDate('01/01/2024');

    expect($date)->format('Y-m-d')->toBe('2024-01-01');
});

it('parses date from Excel serial', function () {
    $service = new ExcelImportService();

    $date = $service->parseDate(45292);

    expect($date)->format('Y-m-d')->toBe('2024-01-01');
});

it('normalizes phone number to E164', function () {
    $service = new ExcelImportService();

    $phone = $service->normalizePhone('08123456789');

    expect($phone)->toBe('628123456789');
});
```

**Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=ExcelImportServiceTest`
Expected: FAIL

**Step 3: Create ExcelImportService**

```php
// app/Services/ExcelImportService.php
namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExcelImportService
{
    protected array $headerMap = [
        'nama' => 'name',
        'pangkat' => 'rank',
        'nrp/nip' => 'nrp_nip',
        'jabatan' => 'position',
        'satuan_kerja' => 'unit',
        'no_hp' => 'phone_raw',
        'tgl_lahir(dd/mm/yyyy)' => 'birth_date',
        'gender(pria/wanita)' => 'gender',
        'no_lab' => 'lab_number',
        'tgl_pemeriksaan(dd/mm/yyyy)' => 'exam_date',
        'kode_paket' => 'package_code',
        'kode_perusahaan' => 'company_code',
    ];

    public function import(string $filePath, string $strategy = 'skip'): array
    {
        $data = Excel::toArray(new class {
            public function collection(Collection $collection)
            {
                return $collection;
            }
        }, $filePath);

        $rows = $data[0];
        $headers = array_map('strtolower', $rows->first()?->toArray() ?? []);
        $headers = array_map(fn($h) => preg_replace('/[^a-z0-9]/', '_', trim($h)), $headers);

        $mappedHeaders = [];
        foreach ($headers as $index => $header) {
            $mappedKey = $this->headerMap[$header] ?? null;
            if ($mappedKey) {
                $mappedHeaders[$index] = $mappedKey;
            }
        }

        $results = [
            'valid' => [],
            'invalid' => [],
            'total' => 0,
        ];

        foreach ($rows->skip(1) as $row) {
            $results['total']++;
            $rowData = $row->toArray();

            $mappedData = [];
            $errors = [];

            foreach ($mappedHeaders as $index => $key) {
                $value = $rowData[$index] ?? null;
                $mappedData[$key] = $value;
            }

            $validation = $this->validateRow($mappedData);
            if (!empty($validation['errors'])) {
                $results['invalid'][] = [
                    'row' => $results['total'],
                    'data' => $mappedData,
                    'errors' => $validation['errors'],
                ];
                continue;
            }

            $mappedData['phone_e164'] = $this->normalizePhone($mappedData['phone_raw']);
            $mappedData['birth_date'] = $this->parseDate($mappedData['birth_date']);
            $mappedData['exam_date'] = $this->parseDate($mappedData['exam_date']);
            $mappedData['gender'] = $this->normalizeGender($mappedData['gender']);

            $results['valid'][] = $mappedData;
        }

        return $results;
    }

    protected function validateRow(array $row): array
    {
        $errors = [];

        if (empty($row['name'])) {
            $errors[] = 'Nama wajib diisi';
        }

        if (empty($row['phone_raw'])) {
            $errors[] = 'No HP wajib diisi';
        }

        if (!empty($row['gender']) && !in_array($row['gender'], ['Pria', 'Wanita'])) {
            $errors[] = 'Gender harus Pria atau Wanita';
        }

        return ['errors' => $errors];
    }

    public function parseDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    protected function normalizeGender(?string $gender): ?string
    {
        if (empty($gender)) {
            return null;
        }

        return match (strtolower($gender)) {
            'pria', 'laki-laki', 'male' => 'Pria',
            'wanita', 'perempuan', 'female' => 'Wanita',
            default => $gender,
        };
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --filter=ExcelImportServiceTest`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Services/ExcelImportService.php tests/Unit/ExcelImportServiceTest.php
git commit -m "feat: create ExcelImportService with header mapping and normalization"
```

---

## Phase 4: Dashboard Component

### Task 11: Create Dashboard Livewire component

**Files:**
- Create: `app/Livewire/Dashboard/Index.php`
- Create: `resources/views/livewire/dashboard/index.blade.php`

**Step 1: Write test**

```php
// tests/Feature/DashboardTest.php
use App\Livewire\Dashboard\Index;
use App\Models\Participant;
use Livewire\Livewire;

it('displays dashboard stats', function () {
    Participant::factory()->count(5)->create(['wa_status' => 'sent']);
    Participant::factory()->count(3)->create(['wa_status' => 'not_sent']);
    Participant::factory()->count(2)->create(['has_attachment' => true]);

    Livewire::test(Index::class)
        ->assertSee('Total Peserta: 10')
        ->assertSee('Sudah Terkirim WA: 5')
        ->assertSee('Belum Terkirim WA: 3');
});

it('only accessible to authenticated users', function () {
    $this->get('/dashboard')
        ->assertRedirect('/login');
});

it('accessible to authenticated admin', function () {
    $user = \App\Models\User::factory()->create();
    auth()->login($user);

    $this->get('/dashboard')
        ->assertStatus(200);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DashboardTest`
Expected: FAIL

**Step 3: Create Dashboard Livewire component**

```php
// app/Livewire/Dashboard/Index.php
namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Participant;
use App\Models\Upload;

class Index extends Component
{
    public $totalParticipants;
    public $sentWa;
    public $notSentWa;
    public $hasAttachment;
    public $noAttachment;
    public $lastImport;

    public function mount(): void
    {
        $this->totalParticipants = Participant::count();
        $this->sentWa = Participant::where('wa_status', 'sent')->count();
        $this->notSentWa = Participant::where('wa_status', 'not_sent')->count();
        $this->hasAttachment = Participant::where('has_attachment', true)->count();
        $this->noAttachment = Participant::where('has_attachment', false)->count();
        $this->lastImport = Upload::where('type', 'data_excel')
            ->where('status', 'imported')
            ->latest()
            ->first();
    }

    public function render()
    {
        return view('livewire.dashboard.index')
            ->layout('components.layouts.app');
    }
}
```

**Step 4: Create Dashboard view**

```blade
<!-- resources/views/livewire/dashboard/index.blade.php -->
<div class="space-y-6">
    <h1 class="text-2xl font-semibold">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-gray-600 dark:text-neutral-400">Total Peserta</p>
            <p class="text-3xl font-bold mt-2">{{ $totalParticipants }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-gray-600 dark:text-neutral-400">Sudah Terkirim WA</p>
            <p class="text-3xl font-bold mt-2 text-green-600">{{ $sentWa }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-gray-600 dark:text-neutral-400">Belum Terkirim WA</p>
            <p class="text-3xl font-bold mt-2 text-yellow-600">{{ $notSentWa }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-gray-600 dark:text-neutral-400">Sudah Ada File Lampiran</p>
            <p class="text-3xl font-bold mt-2 text-blue-600">{{ $hasAttachment }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700">
            <p class="text-sm text-gray-600 dark:text-neutral-400">Belum Ada File Lampiran</p>
            <p class="text-3xl font-bold mt-2 text-gray-600">{{ $noAttachment }}</p>
        </div>

        @if($lastImport)
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-sm border border-neutral-200 dark:border-neutral-700">
                <p class="text-sm text-gray-600 dark:text-neutral-400">Import Terakhir</p>
                <p class="text-lg font-semibold mt-2">{{ $lastImport->original_name }}</p>
                <p class="text-sm text-gray-500 dark:text-neutral-500 mt-1">{{ $lastImport->updated_at->diffForHumans() }}</p>
            </div>
        @endif
    </div>
</div>
```

**Step 5: Update routes**

```php
// routes/web.php
Route::view('dashboard', 'livewire.dashboard.index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
```

**Step 6: Run tests**

Run: `php artisan test --filter=DashboardTest`
Expected: PASS

**Step 7: Commit**

```bash
git add app/Livewire/Dashboard/ resources/views/livewire/dashboard/ routes/web.php tests/Feature/DashboardTest.php
git commit -m "feat: create dashboard component with stats"
```

---

## Phase 5: Participants Management

### Task 12: Create Participants Index component

**Files:**
- Create: `app/Livewire/Participants/Index.php`
- Create: `resources/views/livewire/participants/index.blade.php`

**Step 1: Create component**

```php
// app/Livewire/Participants/Index.php
namespace App\Livewire\Participants;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Participant;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $filterWaStatus = '';

    #[Url]
    public $filterFileStatus = '';

    #[Url]
    public $sortField = 'created_at';

    #[Url]
    public $sortDirection = 'desc';

    public function render()
    {
        $query = Participant::query()
            ->with(['package', 'company']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('nrp_nip', 'like', '%' . $this->search . '%')
                    ->orWhere('lab_number', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_raw', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterWaStatus) {
            $query->where('wa_status', $this->filterWaStatus);
        }

        if ($this->filterFileStatus === 'has') {
            $query->where('has_attachment', true);
        } elseif ($this->filterFileStatus === 'none') {
            $query->where('has_attachment', false);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.participants.index', [
            'participants' => $query->paginate(10),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterWaStatus = '';
        $this->filterFileStatus = '';
        $this->resetPage();
    }
}
```

**Step 2: Create view**

```blade
<!-- resources/views/livewire/participants/index.blade.php -->
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Data Anggota</h1>
        <flux:button variant="primary">Upload Data</flux:button>
    </div>

    <flux:card>
        <div class="space-y-4">
            <div class="flex gap-4">
                <flux:input
                    wire:model.live="search"
                    placeholder="Cari nama, NRP/NIP, No Lab, No HP..."
                />
                <flux:select wire:model="filterWaStatus">
                    <option value="">Semua Status WA</option>
                    <option value="not_sent">Belum Terkirim</option>
                    <option value="queued">Antri</option>
                    <option value="sent">Terkirim</option>
                    <option value="failed">Gagal</option>
                </flux:select>
                <flux:select wire:model="filterFileStatus">
                    <option value="">Semua Status File</option>
                    <option value="has">Ada File</option>
                    <option value="none">Tidak Ada File</option>
                </flux:select>
                @if($search || $filterWaStatus || $filterFileStatus)
                    <flux:button variant="ghost" wire:click="resetFilters">
                        Reset Filter
                    </flux:button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <th class="text-left p-3 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800"
                                wire:click="sortBy('name')">
                                Nama
                                @if($sortField === 'name')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="text-left p-3">NRP/NIP</th>
                            <th class="text-left p-3">No HP</th>
                            <th class="text-left p-3 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800"
                                wire:click="sortBy('wa_status')">
                                Status WA
                                @if($sortField === 'wa_status')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="text-left p-3 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800"
                                wire:click="sortBy('has_attachment')">
                                File
                                @if($sortField === 'has_attachment')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th class="text-left p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $participant)
                            <tr class="border-b border-neutral-100 dark:border-neutral-800">
                                <td class="p-3">{{ $participant->name }}</td>
                                <td class="p-3">{{ $participant->nrp_nip }}</td>
                                <td class="p-3">{{ $participant->phone_raw }}</td>
                                <td class="p-3">
                                    @switch($participant->wa_status)
                                        @case('not_sent')
                                            <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">Belum</span>
                                        @break
                                        @case('queued')
                                            <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">Antri</span>
                                        @break
                                        @case('sent')
                                            <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Terkirim</span>
                                        @break
                                        @case('failed')
                                            <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-800">Gagal</span>
                                        @break
                                    @endswitch
                                </td>
                                <td class="p-3">
                                    @if($participant->has_attachment)
                                        <span class="text-green-600">Ada</span>
                                    @else
                                        <span class="text-gray-400">Tidak</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <flux:button variant="ghost" size="sm">Detail</flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $participants->links() }}
        </div>
    </flux:card>
</div>
```

**Step 3: Add route**

```php
// routes/web.php
Route::get('/participants', Participants\Index::class)
    ->middleware(['auth'])
    ->name('participants.index');
```

**Step 4: Test**

Run: `php artisan test --filter=ParticipantsTest`
Expected: PASS (create test if needed)

**Step 5: Commit**

```bash
git add app/Livewire/Participants/ resources/views/livewire/participants/ routes/web.php
git commit -m "feat: create participants index with search and filter"
```

---

## Phase 6: Settings (Packages & Companies)

### Task 13: Create Packages Management

**Files:**
- Create: `app/Livewire/Settings/Packages/Index.php`
- Create: `resources/views/livewire/settings/packages/index.blade.php`

**Step 1: Create component**

```php
// app/Livewire/Settings/Packages/Index.php
namespace App\Livewire\Settings\Packages;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Package;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingPackage = null;
    public $code = '';
    public $name = '';
    public $description = '';
    public $is_active = true;

    protected $rules = [
        'code' => 'required|unique:packages,code',
        'name' => 'required',
        'description' => 'nullable',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.settings.packages.index', [
            'packages' => Package::orderBy('code')->paginate(10),
        ])->layout('components.layouts.app');
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Package $package)
    {
        $this->editingPackage = $package;
        $this->code = $package->code;
        $this->name = $package->name;
        $this->description = $package->description;
        $this->is_active = $package->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Package::updateOrCreate(
            ['id' => $this->editingPackage?->id],
            [
                'code' => $this->code,
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Package $package)
    {
        $package->delete();
    }

    protected function resetForm()
    {
        $this->editingPackage = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
    }
}
```

**Step 2: Create view**

```blade
<!-- resources/views/livewire/settings/packages/index.blade.php -->
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Pengaturan Paket</h1>
        <flux:button variant="primary" wire:click="create">Tambah Paket</flux:button>
    </div>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <th class="text-left p-3">Kode</th>
                        <th class="text-left p-3">Nama</th>
                        <th class="text-left p-3">Deskripsi</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                        <tr class="border-b border-neutral-100 dark:border-neutral-800">
                            <td class="p-3">{{ $package->code }}</td>
                            <td class="p-3">{{ $package->name }}</td>
                            <td class="p-3">{{ $package->description }}</td>
                            <td class="p-3">
                                @if($package->is_active)
                                    <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">Nonaktif</span>
                                @endif
                            </td>
                            <td class="p-3 flex gap-2">
                                <flux:button variant="ghost" size="sm" wire:click="edit({{ $package->id }})">Edit</flux:button>
                                <flux:button variant="ghost" size="sm" wire:click="delete({{ $package->id }})">Hapus</flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>

    @if($showModal)
        <flux:modal wire:model="showModal">
            <div class="space-y-4 p-6">
                <h2 class="text-xl font-semibold">{{ $editingPackage ? 'Edit Paket' : 'Tambah Paket' }}</h2>

                <flux:field label="Kode">
                    <flux:input wire:model.live="code" />
                </flux:field>

                <flux:field label="Nama">
                    <flux:input wire:model.live="name" />
                </flux:field>

                <flux:field label="Deskripsi">
                    <flux:textarea wire:model.live="description" />
                </flux:field>

                <flux:field label="Status">
                    <flux:switch wire:model.live="is_active">
                        {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                    </flux:switch>
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">Batal</flux:button>
                    <flux:button variant="primary" wire:click="save">Simpan</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
```

**Step 3: Add route**

```php
// routes/web.php
Route::get('/settings/packages', Settings\Packages\Index::class)
    ->middleware(['auth'])
    ->name('settings.packages');
```

**Step 4: Commit**

```bash
git add app/Livewire/Settings/Packages/ resources/views/livewire/settings/packages/ routes/web.php
git commit -m "feat: create packages management CRUD"
```

---

### Task 14: Create Companies Management

**Files:**
- Create: `app/Livewire/Settings/Companies/Index.php`
- Create: `resources/views/livewire/settings/companies/index.blade.php`

**Step 1: Create component**

```php
// app/Livewire/Settings/Companies/Index.php
namespace App\Livewire\Settings\Companies;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Company;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingCompany = null;
    public $code = '';
    public $name = '';
    public $prolog_template = '';
    public $footer_template = '';
    public $is_active = true;

    protected $rules = [
        'code' => 'required|unique:companies,code',
        'name' => 'required',
        'prolog_template' => 'required',
        'footer_template' => 'nullable',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.settings.companies.index', [
            'companies' => Company::orderBy('code')->paginate(10),
        ])->layout('components.layouts.app');
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Company $company)
    {
        $this->editingCompany = $company;
        $this->code = $company->code;
        $this->name = $company->name;
        $this->prolog_template = $company->prolog_template;
        $this->footer_template = $company->footer_template;
        $this->is_active = $company->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Company::updateOrCreate(
            ['id' => $this->editingCompany?->id],
            [
                'code' => $this->code,
                'name' => $this->name,
                'prolog_template' => $this->prolog_template,
                'footer_template' => $this->footer_template,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Company $company)
    {
        $company->delete();
    }

    protected function resetForm()
    {
        $this->editingCompany = null;
        $this->code = '';
        $this->name = '';
        $this->prolog_template = 'Halo {nama},';
        $this->footer_template = 'Terima kasih.';
        $this->is_active = true;
    }
}
```

**Step 2: Create view**

```blade
<!-- resources/views/livewire/settings/companies/index.blade.php -->
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Pengaturan Instansi</h1>
        <flux:button variant="primary" wire:click="create">Tambah Instansi</flux:button>
    </div>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700">
                        <th class="text-left p-3">Kode</th>
                        <th class="text-left p-3">Nama</th>
                        <th class="text-left p-3">Prolog WA</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $company)
                        <tr class="border-b border-neutral-100 dark:border-neutral-800">
                            <td class="p-3">{{ $company->code }}</td>
                            <td class="p-3">{{ $company->name }}</td>
                            <td class="p-3">{{ str_limit($company->prolog_template, 50) }}</td>
                            <td class="p-3">
                                @if($company->is_active)
                                    <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">Nonaktif</span>
                                @endif
                            </td>
                            <td class="p-3 flex gap-2">
                                <flux:button variant="ghost" size="sm" wire:click="edit({{ $company->id }})">Edit</flux:button>
                                <flux:button variant="ghost" size="sm" wire:click="delete({{ $company->id }})">Hapus</flux:button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>

    @if($showModal)
        <flux:modal wire:model="showModal">
            <div class="space-y-4 p-6">
                <h2 class="text-xl font-semibold">{{ $editingCompany ? 'Edit Instansi' : 'Tambah Instansi' }}</h2>

                <flux:field label="Kode">
                    <flux:input wire:model.live="code" />
                </flux:field>

                <flux:field label="Nama">
                    <flux:input wire:model.live="name" />
                </flux:field>

                <flux:field label="Template Prolog WA">
                    <flux:textarea wire:model.live="prolog_template" />
                    <p class="text-xs text-gray-500 mt-1">Gunakan {nama} untuk nama peserta</p>
                </flux:field>

                <flux:field label="Template Footer (opsional)">
                    <flux:textarea wire:model.live="footer_template" />
                </flux:field>

                <flux:field label="Status">
                    <flux:switch wire:model.live="is_active">
                        {{ $is_active ? 'Aktif' : 'Nonaktif' }}
                    </flux:switch>
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">Batal</flux:button>
                    <flux:button variant="primary" wire:click="save">Simpan</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
```

**Step 3: Add route**

```php
// routes/web.php
Route::get('/settings/companies', Settings\Companies\Index::class)
    ->middleware(['auth'])
    ->name('settings.companies');
```

**Step 4: Commit**

```bash
git add app/Livewire/Settings/Companies/ resources/views/livewire/settings/companies/ routes/web.php
git commit -m "feat: create companies management with WA prolog templates"
```

---

## Phase 7: Upload Data (Excel) with Preview

### Task 15: Create Data Upload component

**Files:**
- Create: `app/Livewire/Uploads/DataImport.php`
- Create: `resources/views/livewire/uploads/data-import.blade.php`

**Step 1: Create component**

```php
// app/Livewire/Uploads/DataImport.php
namespace App\Livewire\Uploads;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Upload;
use App\Services\ExcelImportService;

class DataImport extends Component
{
    use WithFileUploads;

    public $file;
    public $uploadId;
    public $preview = null;
    public $showPreview = false;
    public $strategy = 'skip';

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls|max:10240',
        'strategy' => 'required|in:skip,update,fail',
    ];

    public function upload()
    {
        $this->validate();

        $path = $this->file->store('imports', 'local');

        $upload = Upload::create([
            'uploaded_by' => auth()->id(),
            'type' => 'data_excel',
            'original_name' => $this->file->getClientOriginalName(),
            'stored_path' => $path,
            'mime' => $this->file->getMimeType(),
            'size' => $this->file->getSize(),
            'status' => 'uploaded',
        ]);

        $this->uploadId = $upload->id;
        $this->showPreview = true;

        $this->previewData($upload);
    }

    protected function previewData(Upload $upload)
    {
        $service = new ExcelImportService();
        $results = $service->import(
            storage_path('app/' . $upload->stored_path),
            $this->strategy
        );

        $this->preview = $results;

        $upload->update([
            'status' => 'parsed',
            'total_rows' => $results['total'],
        ]);
    }

    public function commitImport()
    {
        if (!$this->uploadId || !$this->preview) {
            return;
        }

        $upload = Upload::find($this->uploadId);

        foreach ($this->preview['valid'] as $data) {
            // Check for duplicates based on strategy
            $existing = \App\Models\Participant::where('lab_number', $data['lab_number'])
                ->where('exam_date', $data['exam_date'])
                ->where('company_code', $data['company_code'])
                ->first();

            if ($existing) {
                if ($this->strategy === 'skip') {
                    continue;
                } elseif ($this->strategy === 'update') {
                    $existing->update($data);
                } elseif ($this->strategy === 'fail') {
                    continue;
                }
            } else {
                \App\Models\Participant::create($data);
            }
        }

        $upload->update([
            'status' => 'imported',
            'success_rows' => count($this->preview['valid']),
            'failed_rows' => count($this->preview['invalid']),
        ]);

        $this->showPreview = false;
        $this->uploadId = null;
        $this->preview = null;

        $this->redirect(route('participants.index'));
    }

    public function cancel()
    {
        $this->showPreview = false;
        $this->uploadId = null;
        $this->preview = null;
        $this->file = null;
    }

    public function render()
    {
        return view('livewire.uploads.data-import')
            ->layout('components.layouts.app');
    }
}
```

**Step 2: Create view**

```blade
<!-- resources/views/livewire/uploads/data-import.blade.php -->
<div class="space-y-6">
    <h1 class="text-2xl font-semibold">Upload Data Excel</h1>

    @if(!$showPreview)
        <flux:card class="max-w-2xl">
            <div class="space-y-6 p-6">
                <div>
                    <flux:heading size="lg">Upload File Excel</flux:heading>
                    <flux:text>Upload file .xlsx atau .xls berisi data peserta.</flux:text>
                </div>

                <flux:field label="Pilih File">
                    <flux:input type="file" wire:model="file" accept=".xlsx,.xls" />
                    @error('file')
                        <flux:text class="text-red-600">{{ $message }}</flux:text>
                    @enderror
                </flux:field>

                <flux:field label="Strategi Duplikasi">
                    <flux:select wire:model="strategy">
                        <option value="skip">Lewati Data Duplikat</option>
                        <option value="update">Update Data Duplikat</option>
                        <option value="fail">Gagal Jika Ada Duplikat</option>
                    </flux:select>
                </flux:field>

                <flux:button variant="primary" wire:click="upload">
                    Upload & Preview
                </flux:button>
            </div>
        </flux:card>
    @else
        <div class="space-y-6">
            <flux:card>
                <div class="space-y-4 p-6">
                    <div>
                        <flux:heading size="lg">Preview Import</flux:heading>
                        <flux:text>Total: {{ $preview['total'] }} baris</flux:text>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <p class="text-sm text-green-800 dark:text-green-200">Valid</p>
                            <p class="text-2xl font-bold text-green-600">{{ count($preview['valid']) }}</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                            <p class="text-sm text-red-800 dark:text-red-200">Invalid</p>
                            <p class="text-2xl font-bold text-red-600">{{ count($preview['invalid']) }}</p>
                        </div>
                    </div>

                    @if(count($preview['invalid']) > 0)
                        <div>
                            <flux:heading size="md">Data Invalid</flux:heading>
                            <div class="max-h-64 overflow-y-auto space-y-2">
                                @foreach($preview['invalid'] as $invalid)
                                    <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded">
                                        <p class="font-semibold">Baris {{ $invalid['row'] }}</p>
                                        <p class="text-sm">{{ implode(', ', $invalid['errors']) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-4">
                        <flux:button variant="ghost" wire:click="cancel">Batal</flux:button>
                        <flux:button variant="primary" wire:click="commitImport">
                            Import {{ count($preview['valid']) }} Data
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        </div>
    @endif
</div>
```

**Step 3: Add route**

```php
// routes/web.php
Route::get('/uploads/data', Uploads\DataImport::class)
    ->middleware(['auth'])
    ->name('uploads.data');
```

**Step 4: Commit**

```bash
git add app/Livewire/Uploads/ resources/views/livewire/uploads/ routes/web.php
git commit -m "feat: create data upload with preview and commit"
```

---

## Phase 8: Update Sidebar Navigation

### Task 16: Update navigation sidebar

**Files:**
- Modify: `resources/views/components/layouts/app/sidebar.blade.php`

**Step 1: Update sidebar navigation**

Add navigation menu items:

```blade
<!-- Update the sidebar navigation section -->
<div class="space-y-1">
    <a href="{{ route('dashboard') }}" class="...">
        Dashboard
    </a>
    <a href="{{ route('participants.index') }}" class="...">
        Data Anggota
    </a>
    <a href="{{ route('uploads.data') }}" class="...">
        Upload File
    </a>
    <div class="pt-4">
        <p class="px-3 text-xs font-semibold text-gray-500 uppercase">Pengaturan</p>
    </div>
    <a href="{{ route('settings.packages') }}" class="...">
        Paket
    </a>
    <a href="{{ route('settings.companies') }}" class="...">
        Instansi
    </a>
</div>
```

**Step 2: Commit**

```bash
git add resources/views/components/layouts/app/sidebar.blade.php
git commit -m "feat: update sidebar navigation"
```

---

## Phase 9: Update User Model for Admin

### Task 17: Update User model

**Files:**
- Modify: `app/Models/User.php`

**Step 1: Add is_active field**

Update User model:

```php
// app/Models/User.php
protected $fillable = [
    'name',
    'email',
    'password',
    'is_active',
];

protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];
}
```

**Step 2: Create migration for is_active**

```bash
php artisan make:migration add_is_active_to_users_table
```

```php
// database/migrations/xxxx_xx_xx_xxxxxx_add_is_active_to_users_table.php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_active')->default(true)->after('password');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('is_active');
    });
}
```

**Step 3: Run migration**

Run: `php artisan migrate`
Expected: Migration successful

**Step 4: Commit**

```bash
git add database/migrations/ app/Models/User.php
git commit -m "feat: add is_active field to users"
```

---

## Final Steps

### Task 18: Run all tests

**Step 1: Run test suite**

```bash
php artisan test
```

Expected: All tests pass

**Step 2: Run Pint for code formatting**

```bash
vendor/bin/pint
```

**Step 3: Final commit**

```bash
git add .
git commit -m "chore: final cleanup and formatting"
```

---

## Summary

This plan covers:

1. ✅ Authentication setup (login-only, default admin seeder)
2. ✅ Complete database schema (packages, companies, participants, uploads, attachments, wa_messages)
3. ✅ Excel import service with header mapping, validation, and E164 phone normalization
4. ✅ Dashboard with statistics
5. ✅ Participants management with search, filter, and pagination
6. ✅ Settings (Packages & Companies CRUD)
7. ✅ Data upload with preview and commit workflow
8. ✅ Navigation structure

**Remaining for full completion:**
- WhatsApp messaging service and queue jobs
- Participant detail view with attachment upload
- Admin account management
- Opt-in/opt-out functionality for WhatsApp
- Rate limiting implementation
