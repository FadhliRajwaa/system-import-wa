# 📋 Dokumentasi Project: Rikkes Berkala (System Import WA)

> **Dibuat:** 28 Desember 2025  
> **Dianalisis dengan:** Laravel Boost MCP, Superpowers Skills  
> **Status:** Production Ready

---

## 📖 Daftar Isi

1. [Overview](#-overview)
2. [Tech Stack](#-tech-stack)
3. [Struktur Direktori](#-struktur-direktori)
4. [Konfigurasi Environment](#-konfigurasi-environment)
5. [Database Schema](#-database-schema)
6. [Models & Relationships](#-models--relationships)
7. [Routes & Endpoints](#-routes--endpoints)
8. [Livewire Components](#-livewire-components)
9. [Services (Business Logic)](#-services-business-logic)
10. [Fitur Utama](#-fitur-utama)
11. [Data Sample](#-data-sample)
12. [Diagram Arsitektur](#-diagram-arsitektur)

---

## 🎯 Overview

### Deskripsi Aplikasi
**Rikkes Berkala** adalah sistem manajemen data peserta pemeriksaan kesehatan berkala (Rikkes) dengan kemampuan:

- ✅ Import data peserta dari file Excel
- ✅ Upload hasil pemeriksaan dalam format PDF
- ✅ Pengiriman hasil via WhatsApp (multi-provider)
- ✅ Multi-user dengan role-based access control
- ✅ Data isolation per user

### Tujuan Bisnis
Mempermudah distribusi hasil pemeriksaan kesehatan kepada peserta melalui WhatsApp secara otomatis dan terstruktur.

---

## 🔧 Tech Stack

### Backend
| Komponen | Versi | Keterangan |
|----------|-------|------------|
| PHP | 8.2.12 | Runtime |
| Laravel | 12.44.0 | Framework utama |
| Livewire | 3.7.3 | Full-stack framework |
| Fortify | 1.33.0 | Authentication |
| Pest | 3.8.4 | Testing framework |
| Pint | 1.26.0 | Code style fixer |
| Sail | 1.51.0 | Docker development |

### Frontend
| Komponen | Versi | Keterangan |
|----------|-------|------------|
| Flux UI | 2.10.2 | UI Component library |
| Tailwind CSS | 4.1.11 | Utility-first CSS |
| Alpine.js | (via Livewire) | Reactivity |

### Database
| Komponen | Keterangan |
|----------|------------|
| Engine | MySQL |
| Database Name | `system_import_wa` |
| Connection | Default Laravel |

### External Services
| Service | Provider | Keterangan |
|---------|----------|------------|
| WhatsApp API | Wablas | Primary provider |
| WhatsApp API | Meta Cloud API | Alternative provider |
| WhatsApp Manual | wa.me links | Fallback option |

---

## 📁 Struktur Direktori

```
system-import-wa/
├── app/
│   ├── Actions/
│   │   └── Fortify/              # Authentication actions
│   │       ├── CreateNewUser.php
│   │       ├── PasswordValidationRules.php
│   │       ├── ResetUserPassword.php
│   │       └── UpdateUserPassword.php
│   │
│   ├── DataObjects/              # Data Transfer Objects
│   │
│   ├── Exports/                  # Excel export classes
│   │
│   ├── Http/
│   │   ├── Controllers/          # HTTP Controllers
│   │   └── Middleware/           # Custom middleware
│   │
│   ├── Jobs/
│   │   └── SendWhatsAppMessageJob.php  # Queue job untuk WA
│   │
│   ├── Livewire/
│   │   ├── Actions/              # Livewire action components
│   │   ├── Dashboard/
│   │   │   └── Index.php         # Dashboard statistik
│   │   ├── Participants/
│   │   │   ├── Edit.php          # Edit peserta
│   │   │   ├── Index.php         # List peserta
│   │   │   └── Show.php          # Detail peserta
│   │   ├── Settings/
│   │   │   ├── AdminUsers/       # Kelola admin users
│   │   │   ├── Companies/        # Kelola companies
│   │   │   ├── Instansi/         # Kelola instansi
│   │   │   ├── Packages/         # Kelola packages
│   │   │   ├── Paket/            # Kelola paket
│   │   │   ├── TwoFactor/        # 2FA settings
│   │   │   ├── Appearance.php    # UI settings
│   │   │   ├── DeleteUserForm.php
│   │   │   ├── Password.php      # Change password
│   │   │   ├── Profile.php       # User profile
│   │   │   └── Wablas.php        # Wablas API settings
│   │   └── Uploads/
│   │       ├── DataImport.php    # Import Excel
│   │       └── PdfImport.php     # Import PDF
│   │
│   ├── Models/
│   │   ├── Company.php
│   │   ├── Instansi.php          # Master instansi
│   │   ├── Package.php
│   │   ├── Paket.php             # Master paket
│   │   ├── Participant.php
│   │   ├── ParticipantAttachment.php
│   │   ├── Peserta.php           # Data peserta (UTAMA)
│   │   ├── PesanWa.php           # Log pesan WA
│   │   ├── Unggahan.php          # Log upload files
│   │   ├── Upload.php
│   │   ├── User.php              # User authentication
│   │   └── WaMessage.php
│   │
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   │
│   └── Services/
│       ├── ExcelImportService.php   # Parse & import Excel
│       ├── PdfImportService.php     # Handle PDF uploads
│       ├── PesanWaService.php       # WA message handling
│       └── WhatsAppService.php      # WA API integration
│
├── database/
│   ├── factories/                # Model factories
│   ├── migrations/               # Database migrations (17 files)
│   └── seeders/                  # Database seeders
│
├── resources/
│   ├── css/                      # Stylesheets
│   ├── js/                       # JavaScript
│   └── views/
│       ├── components/           # Blade components
│       ├── flux/                 # Flux UI overrides
│       ├── livewire/
│       │   ├── auth/             # Auth views
│       │   ├── dashboard/        # Dashboard views
│       │   ├── participants/     # Participant views
│       │   ├── settings/         # Settings views
│       │   └── uploads/          # Upload views
│       ├── pagination/           # Pagination templates
│       └── partials/             # Partial views
│
├── routes/
│   ├── console.php               # Console routes
│   └── web.php                   # Web routes
│
├── tests/                        # Pest tests
├── docs/                         # Documentation
└── storage/                      # File storage
```

---

## ⚙️ Konfigurasi Environment

### File `.env` (Key Variables)

```env
# Application
APP_NAME="Rikkes Berkala"
APP_ENV=local
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_DATABASE=system_import_wa
DB_USERNAME=root
DB_PASSWORD=

# WhatsApp Provider
WA_PROVIDER=wablas
WABLAS_SERVER=solo
WABLAS_TOKEN=<user-specific>
WABLAS_PHONE=<user-specific>

# Queue
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database
```

### WhatsApp Provider Options
| Provider | Value | Keterangan |
|----------|-------|------------|
| Wablas | `wablas` | API berbayar, stabil |
| Meta Cloud API | `meta_cloud_api` | Official WhatsApp Business |
| Manual | `manual` | Generate wa.me links |

---

## 🗄️ Database Schema

### Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   users     │       │   instansi  │       │    paket    │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │◄──┐   │ id (PK)     │       │ id (PK)     │
│ name        │   │   │ kode (UK)   │◄──┐   │ kode (UK)   │◄──┐
│ email (UK)  │   │   │ nama        │   │   │ nama        │   │
│ role        │   │   │ template_*  │   │   │ deskripsi   │   │
│ wablas_*    │   │   │ dibuat_oleh │───┘   │ dibuat_oleh │───┤
│ kode_user   │   │   └─────────────┘       └─────────────┘   │
└─────────────┘   │                                           │
       ▲          │   ┌─────────────────────────────────┐     │
       │          │   │           peserta               │     │
       │          │   ├─────────────────────────────────┤     │
       │          │   │ nrp_nip (PK)      ──┐           │     │
       │          │   │ tanggal_periksa (PK)│ Composite │     │
       │          └───│ diupload_oleh       ◄───────────┤     │
       │              │ kode_instansi ──────────────────┼─────┘
       │              │ kode_paket ─────────────────────┼─────┘
       │              │ nama, pangkat, jabatan          │
       │              │ no_hp_raw, no_hp_wa             │
       │              │ status_pdf, status_wa           │
       │              │ path_pdf                        │
       │              └─────────────────────────────────┘
       │                              │
       │                              ▼
       │              ┌─────────────────────────────────┐
       │              │          pesan_wa               │
       │              ├─────────────────────────────────┤
       └──────────────│ dibuat_oleh                     │
                      │ nrp_nip_peserta (FK)            │
                      │ provider, no_tujuan             │
                      │ isi_pesan, status               │
                      │ percobaan, error_terakhir       │
                      └─────────────────────────────────┘

┌─────────────────────────────────┐
│          unggahan               │
├─────────────────────────────────┤
│ id (PK)                         │
│ diupload_oleh (FK → users)      │
│ tipe (data_excel | pdf)         │
│ nama_file_asli, path_tersimpan  │
│ status, total_baris             │
│ baris_sukses, baris_gagal       │
│ ringkasan_error (JSON)          │
└─────────────────────────────────┘
```

### Table Definitions

#### 1. `users` - User Management
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint | NO | AUTO | Primary key |
| `name` | varchar(255) | NO | - | Nama lengkap |
| `email` | varchar(255) | NO | - | Email (unique) |
| `password` | varchar(255) | NO | - | Hashed password |
| `role` | enum('admin','user') | NO | 'user' | Role user |
| `is_active` | boolean | NO | true | Status aktif |
| `kode_user` | varchar(50) | YES | NULL | Kode unik user |
| `wablas_token` | varchar(255) | YES | NULL | Token Wablas API |
| `wablas_server` | varchar(100) | YES | NULL | Server Wablas |
| `wablas_phone` | varchar(20) | YES | NULL | No HP Wablas |
| `email_verified_at` | timestamp | YES | NULL | Verifikasi email |
| `remember_token` | varchar(100) | YES | NULL | Remember me token |
| `created_at` | timestamp | YES | NULL | Waktu dibuat |
| `updated_at` | timestamp | YES | NULL | Waktu diupdate |

#### 2. `peserta` - Data Peserta (COMPOSITE PRIMARY KEY)
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `nrp_nip` | varchar(50) | NO | - | **PK Part 1** - NRP/NIP |
| `tanggal_periksa` | date | NO | - | **PK Part 2** - Tanggal pemeriksaan |
| `nama` | varchar(255) | NO | - | Nama lengkap peserta |
| `pangkat` | varchar(50) | YES | NULL | Pangkat (IPTU, AIPTU, dll) |
| `jabatan` | varchar(255) | YES | NULL | Jabatan |
| `satuan_kerja` | varchar(255) | YES | NULL | Satuan kerja |
| `no_hp_raw` | varchar(50) | YES | NULL | No HP asli dari Excel |
| `no_hp_wa` | varchar(20) | YES | NULL | No HP format E164 (+62xxx) |
| `tanggal_lahir` | date | YES | NULL | Tanggal lahir |
| `jenis_kelamin` | varchar(10) | YES | NULL | Pria/Wanita |
| `no_lab` | varchar(50) | YES | NULL | Nomor laboratorium |
| `kode_paket` | varchar(50) | YES | NULL | FK → paket.kode |
| `kode_instansi` | varchar(50) | YES | NULL | FK → instansi.kode |
| `status_pdf` | enum | NO | 'not_uploaded' | Status upload PDF |
| `path_pdf` | varchar(500) | YES | NULL | Path file PDF |
| `status_wa` | enum | NO | 'not_sent' | Status kirim WA |
| `diupload_oleh` | bigint | NO | - | FK → users.id |
| `created_at` | timestamp | YES | NULL | Waktu dibuat |
| `updated_at` | timestamp | YES | NULL | Waktu diupdate |

**Status PDF Values:** `not_uploaded`, `uploaded`  
**Status WA Values:** `not_sent`, `pending`, `sent`, `failed`

#### 3. `instansi` - Master Instansi
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint | NO | AUTO | Primary key |
| `kode` | varchar(50) | NO | - | Kode instansi (unique) |
| `nama` | varchar(255) | NO | - | Nama instansi |
| `template_prolog` | text | YES | NULL | Template pesan prolog |
| `template_footer` | text | YES | NULL | Template pesan footer |
| `aktif` | boolean | NO | true | Status aktif |
| `dibuat_oleh` | bigint | NO | - | FK → users.id |
| `created_at` | timestamp | YES | NULL | Waktu dibuat |
| `updated_at` | timestamp | YES | NULL | Waktu diupdate |

#### 4. `paket` - Master Paket Pemeriksaan
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint | NO | AUTO | Primary key |
| `kode` | varchar(50) | NO | - | Kode paket (unique) |
| `nama` | varchar(255) | NO | - | Nama paket |
| `deskripsi` | text | YES | NULL | Deskripsi paket |
| `aktif` | boolean | NO | true | Status aktif |
| `dibuat_oleh` | bigint | NO | - | FK → users.id |
| `created_at` | timestamp | YES | NULL | Waktu dibuat |
| `updated_at` | timestamp | YES | NULL | Waktu diupdate |

#### 5. `pesan_wa` - Log Pesan WhatsApp
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint | NO | AUTO | Primary key |
| `provider` | varchar(50) | NO | - | Provider: wablas/meta_cloud_api/manual |
| `no_tujuan` | varchar(20) | NO | - | No HP tujuan |
| `isi_pesan` | text | NO | - | Isi pesan WA |
| `status` | enum | NO | 'queued' | Status pengiriman |
| `percobaan` | int | NO | 0 | Jumlah percobaan kirim |
| `error_terakhir` | text | YES | NULL | Error message terakhir |
| `waktu_kirim` | timestamp | YES | NULL | Waktu berhasil kirim |
| `nrp_nip_peserta` | varchar(50) | YES | NULL | FK → peserta.nrp_nip |
| `dibuat_oleh` | bigint | NO | - | FK → users.id |
| `created_at` | timestamp | YES | NULL | Waktu dibuat |
| `updated_at` | timestamp | YES | NULL | Waktu diupdate |

**Status Values:** `queued`, `pending`, `sent`, `failed`

#### 6. `unggahan` - Log Upload Files
| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint | NO | AUTO | Primary key |
| `diupload_oleh` | bigint | NO | - | FK → users.id |
| `tipe` | enum | NO | - | Tipe: data_excel/pdf |
| `nama_file_asli` | varchar(255) | NO | - | Nama file original |
| `path_tersimpan` | varchar(500) | NO | - | Path di storage |
| `mime` | varchar(100) | YES | NULL | MIME type |
| `ukuran` | bigint | YES | NULL | Ukuran file (bytes) |
| `status` | enum | NO | 'parsed' | Status: parsed/imported |
| `total_baris` | int | YES | NULL | Total baris Excel |
| `baris_sukses` | int | YES | NULL | Baris berhasil import |
| `baris_gagal` | int | YES | NULL | Baris gagal import |
| `ringkasan_error` | json | YES | NULL | Summary errors |
| `created_at` | timestamp | YES | NULL | Waktu dibuat |
| `updated_at` | timestamp | YES | NULL | Waktu diupdate |

---

## 🔗 Models & Relationships

### User Model
```php
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'role',
        'kode_user', 'wablas_token', 'wablas_server', 'wablas_phone'
    ];

    // Scopes
    public function scopeAdmins($query) // role = admin
    public function scopeUsers($query)  // role = user
    public function scopeActive($query) // is_active = true

    // Helpers
    public function isAdmin(): bool
}
```

### Peserta Model (Composite Primary Key)
```php
class Peserta extends Model
{
    protected $table = 'peserta';
    protected $primaryKey = ['nrp_nip', 'tanggal_periksa'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nrp_nip', 'tanggal_periksa', 'nama', 'pangkat', 'jabatan',
        'satuan_kerja', 'no_hp_raw', 'no_hp_wa', 'tanggal_lahir',
        'jenis_kelamin', 'no_lab', 'kode_paket', 'kode_instansi',
        'status_pdf', 'path_pdf', 'status_wa', 'diupload_oleh'
    ];

    // Relationships
    public function uploader()  // belongsTo User
    public function paket()     // belongsTo Paket (via kode_paket)
    public function instansi()  // belongsTo Instansi (via kode_instansi)
    public function pesanWa()   // hasMany PesanWa
}
```

### PesanWa Model
```php
class PesanWa extends Model
{
    protected $table = 'pesan_wa';

    protected $fillable = [
        'provider', 'no_tujuan', 'isi_pesan', 'status',
        'percobaan', 'error_terakhir', 'waktu_kirim',
        'nrp_nip_peserta', 'dibuat_oleh'
    ];

    // Relationships
    public function peserta()  // belongsTo Peserta
    public function pembuat()  // belongsTo User
}
```

### Instansi Model
```php
class Instansi extends Model
{
    protected $table = 'instansi';

    protected $fillable = [
        'kode', 'nama', 'template_prolog', 'template_footer',
        'aktif', 'dibuat_oleh'
    ];

    // Relationships
    public function pembuat()  // belongsTo User
    public function peserta()  // hasMany Peserta

    // Scopes (Role-based)
    public function scopeForUser($query, $user)
}
```

### Paket Model
```php
class Paket extends Model
{
    protected $table = 'paket';

    protected $fillable = [
        'kode', 'nama', 'deskripsi', 'aktif', 'dibuat_oleh'
    ];

    // Relationships
    public function pembuat()  // belongsTo User
    public function peserta()  // hasMany Peserta

    // Scopes (Role-based)
    public function scopeForUser($query, $user)
}
```

### Unggahan Model
```php
class Unggahan extends Model
{
    protected $table = 'unggahan';

    protected $fillable = [
        'diupload_oleh', 'tipe', 'nama_file_asli', 'path_tersimpan',
        'mime', 'ukuran', 'status', 'total_baris',
        'baris_sukses', 'baris_gagal', 'ringkasan_error'
    ];

    protected $casts = [
        'ringkasan_error' => 'array'
    ];

    // Relationships
    public function uploader()  // belongsTo User
}
```

---

## 🛣️ Routes & Endpoints

### Public Routes
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/` | Redirect ke dashboard/login |

### Authenticated Routes (Auth Middleware)
| Method | URI | Component | Description |
|--------|-----|-----------|-------------|
| GET | `/dashboard` | Dashboard\Index | Statistik overview |
| GET | `/participants` | Participants\Index | List peserta |
| GET | `/participants/{nrp}/{date}` | Participants\Show | Detail peserta |
| GET | `/participants/{nrp}/{date}/edit` | Participants\Edit | Edit peserta |
| GET | `/uploads/data` | Uploads\DataImport | Import Excel |
| GET | `/uploads/pdf` | Uploads\PdfImport | Upload PDF |
| GET | `/settings/profile` | Settings\Profile | Edit profil |
| GET | `/settings/password` | Settings\Password | Ganti password |
| GET | `/settings/wablas` | Settings\Wablas | Konfigurasi Wablas |
| GET | `/settings/paket` | Settings\Paket | Kelola paket |
| GET | `/settings/instansi` | Settings\Instansi | Kelola instansi |

### Admin Only Routes
| Method | URI | Component | Description |
|--------|-----|-----------|-------------|
| GET | `/settings/admin-users` | AdminUsers\Index | Kelola users |
| GET | `/settings/packages` | Packages | Kelola packages |
| GET | `/settings/companies` | Companies | Kelola companies |

---

## 🧩 Livewire Components

### Dashboard/Index
**Fungsi:** Menampilkan statistik overview
```php
// Properties yang dihitung
$totalPeserta   // Total peserta yang diupload user
$sentWa         // Jumlah WA terkirim
$notSentWa      // Jumlah WA belum terkirim
$hasPdf         // Jumlah yang sudah ada PDF
$noPdf          // Jumlah yang belum ada PDF
$lastUpload     // Upload terakhir
$recentPeserta  // 5 peserta terbaru
$waStats        // Statistik WA per status
```

### Participants/Index
**Fungsi:** List peserta dengan fitur lengkap
```php
// Features
- Search by nama, NRP, satuan kerja
- Filter by tanggal, paket, satuan_kerja, status_wa, status_pdf
- Sorting by column
- Bulk selection
- queueBulkWa()        // Kirim WA bulk
- generateWaMessage()  // Generate pesan WA
- exportData()         // Export ke Excel
- delete()             // Hapus single/bulk
```

### Uploads/DataImport
**Fungsi:** Import data dari Excel
```php
// Workflow
1. Upload Excel file
2. Parse dengan ExcelImportService
3. Preview mode - tampilkan data yang akan diimport
4. Pilih duplication strategy (skip/update/fail)
5. validateDataBeforeImport() - cek duplikat
6. commitImport() - simpan ke database dengan ownership
```

### Uploads/PdfImport
**Fungsi:** Upload file PDF hasil pemeriksaan
```php
// Workflow
1. Upload multiple PDF files
2. Match dengan peserta berdasarkan nama file
3. Update status_pdf dan path_pdf
```

### Settings/Wablas
**Fungsi:** Konfigurasi API Wablas per user
```php
// Fields
- wablas_token
- wablas_server
- wablas_phone
```

---

## ⚡ Services (Business Logic)

### ExcelImportService
**Lokasi:** `app/Services/ExcelImportService.php`

**Fungsi Utama:**
```php
public function parse(string $filePath): array
{
    // 1. Baca file Excel
    // 2. Mapping header Indonesia → field database
    // 3. Parse tanggal format dd/mm/yyyy
    // 4. Format nomor HP ke E164 (+62xxx)
    // 5. Validasi required fields: nrp_nip, nama, tanggal_periksa
    // 6. Return array of parsed data
}
```

**Header Mapping:**
| Header Excel | Field Database |
|--------------|----------------|
| NRP/NIP, NRP, NIP | nrp_nip |
| NAMA, NAMA LENGKAP | nama |
| PANGKAT | pangkat |
| JABATAN | jabatan |
| SATUAN KERJA, SATKER | satuan_kerja |
| NO HP, NOMOR HP, NO TELP | no_hp_raw |
| TANGGAL LAHIR, TGL LAHIR | tanggal_lahir |
| JENIS KELAMIN, JK | jenis_kelamin |
| NO LAB, NOMOR LAB | no_lab |
| TANGGAL PERIKSA, TGL PERIKSA | tanggal_periksa |
| PAKET, KODE PAKET | kode_paket |
| INSTANSI, KODE INSTANSI | kode_instansi |

### WhatsAppService
**Lokasi:** `app/Services/WhatsAppService.php`

**Fungsi Utama:**
```php
public function composeMessage(Peserta $peserta): string
{
    // 1. Ambil template dari instansi (prolog + footer)
    // 2. Compose message dengan data peserta
    // 3. Return formatted message
}

public function sendMessage(Peserta $peserta): void
{
    // 1. Check opt-in status
    // 2. Dispatch SendWhatsAppMessageJob ke queue
}

public function checkOptIn(string $phoneNumber): bool
public function canSendMessage(Peserta $peserta): bool
```

### PesanWaService
**Lokasi:** `app/Services/PesanWaService.php`

**Fungsi:** Handle pengiriman WA via berbagai provider

**Provider Support:**
- `wablas` - API Wablas
- `meta_cloud_api` - Meta WhatsApp Business API
- `manual` - Generate wa.me links

---

## 🎨 Fitur Utama

### 1. Import Data Excel
```
┌─────────────────────────────────────────────────────────┐
│                    WORKFLOW                             │
├─────────────────────────────────────────────────────────┤
│  1. User upload file Excel (.xlsx, .xls)                │
│  2. Sistem parse & validasi data                        │
│  3. Preview data yang akan diimport                     │
│  4. Pilih strategi duplikasi:                           │
│     - Skip: Lewati data duplikat                        │
│     - Update: Update data yang sudah ada                │
│     - Fail: Batalkan jika ada duplikat                  │
│  5. Commit import ke database                           │
│  6. Log hasil ke tabel unggahan                         │
└─────────────────────────────────────────────────────────┘
```

### 2. Upload PDF Hasil
```
┌─────────────────────────────────────────────────────────┐
│                    WORKFLOW                             │
├─────────────────────────────────────────────────────────┤
│  1. User upload multiple PDF files                      │
│  2. Sistem match file dengan peserta (by nama/NRP)      │
│  3. Update field:                                       │
│     - status_pdf = 'uploaded'                           │
│     - path_pdf = storage path                           │
│  4. PDF siap untuk dikirim via WA                       │
└─────────────────────────────────────────────────────────┘
```

### 3. Kirim WhatsApp
```
┌─────────────────────────────────────────────────────────┐
│                    WORKFLOW                             │
├─────────────────────────────────────────────────────────┤
│  1. Pilih peserta (single atau bulk)                    │
│  2. Generate pesan dari template instansi               │
│  3. Kirim via provider yang dikonfigurasi:              │
│     - Wablas API                                        │
│     - Meta Cloud API                                    │
│     - Manual (wa.me link)                               │
│  4. Update status:                                      │
│     - status_wa = 'pending' → 'sent' / 'failed'         │
│  5. Log ke tabel pesan_wa                               │
└─────────────────────────────────────────────────────────┘
```

### 4. Role-Based Access Control
```
┌─────────────────────────────────────────────────────────┐
│  ADMIN                    │  USER                       │
├───────────────────────────┼─────────────────────────────┤
│  ✅ Lihat semua data      │  ✅ Lihat data sendiri      │
│  ✅ Kelola users          │  ❌ Kelola users            │
│  ✅ Kelola master data    │  ✅ Kelola master sendiri   │
│  ✅ Full access           │  ✅ Limited access          │
└───────────────────────────┴─────────────────────────────┘
```

---

## 📊 Data Sample

### Users (4 Records)
| ID | Name | Email | Role | Status |
|----|------|-------|------|--------|
| 1 | Admin | admin@example.com | admin | Active |
| 2 | User Demo | user@example.com | user | Active |
| 4 | User Demo 2 | user2@example.com | user | Active |
| 5 | User Demo 3 | user3@example.com | user | Active |

### Peserta (3 Records)
| NRP/NIP | Nama | Pangkat | Satuan Kerja | Status PDF | Status WA |
|---------|------|---------|--------------|------------|-----------|
| 73070427 | AGUS YULIANTO | IPTU | POLRES TANGERANG | not_uploaded | not_sent |
| 81040471 | FADHLI RAJWAA | BRIPTU | POLRES TANGERANG | not_uploaded | not_sent |
| 85050718 | ROBBY SABILLY | BRIPTU | POLRES TANGERANG | not_uploaded | not_sent |

### Instansi & Paket
> **Status:** Belum ada data (0 records)

### Pesan WA (1 Record)
| Provider | No Tujuan | Status | Percobaan |
|----------|-----------|--------|-----------|
| meta_cloud_api | +6281231712687 | queued | 0 |

### Unggahan (10+ Records)
Sebagian besar bertipe `data_excel` dengan status `parsed` atau `imported`.

---

## 🏗️ Diagram Arsitektur

### Application Flow
```
┌──────────────────────────────────────────────────────────────────┐
│                         FRONTEND                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐               │
│  │   Flux UI   │  │ Tailwind CSS│  │  Alpine.js  │               │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘               │
│         └────────────────┴────────────────┘                       │
│                          │                                        │
│  ┌───────────────────────▼───────────────────────┐               │
│  │              LIVEWIRE COMPONENTS               │               │
│  │  Dashboard │ Participants │ Uploads │ Settings │               │
│  └───────────────────────┬───────────────────────┘               │
└──────────────────────────┼───────────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────────┐
│                         BACKEND                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                      SERVICES                                │ │
│  │  ExcelImportService │ WhatsAppService │ PesanWaService       │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                           │                                       │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                       MODELS                                 │ │
│  │  User │ Peserta │ Instansi │ Paket │ PesanWa │ Unggahan     │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                           │                                       │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                        JOBS                                  │ │
│  │              SendWhatsAppMessageJob (Queue)                  │ │
│  └─────────────────────────────────────────────────────────────┘ │
└──────────────────────────┼───────────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────────┐
│                      EXTERNAL SERVICES                            │
│  ┌───────────────┐  ┌───────────────┐  ┌───────────────┐         │
│  │    Wablas     │  │ Meta Cloud API│  │   Manual WA   │         │
│  │     API       │  │  (WhatsApp)   │  │   (wa.me)     │         │
│  └───────────────┘  └───────────────┘  └───────────────┘         │
└──────────────────────────────────────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────────┐
│                        DATABASE                                   │
│                   MySQL: system_import_wa                         │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐    │
│  │  users  │ │ peserta │ │instansi │ │  paket  │ │pesan_wa │    │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘    │
│                                                  ┌─────────┐     │
│                                                  │unggahan │     │
│                                                  └─────────┘     │
└──────────────────────────────────────────────────────────────────┘
```

---

## 📝 Catatan Penting

### Composite Primary Key pada Peserta
Tabel `peserta` menggunakan composite primary key (`nrp_nip` + `tanggal_periksa`) yang memungkinkan:
- Satu peserta dapat memiliki multiple records pemeriksaan pada tanggal berbeda
- Unique constraint per kombinasi NRP dan tanggal periksa

### Data Isolation
- User role `user` hanya dapat melihat dan mengelola data yang mereka upload sendiri
- User role `admin` dapat melihat semua data dari semua user

### WhatsApp Provider
Provider default adalah `wablas` dengan konfigurasi per-user. Setiap user dapat memiliki token Wablas sendiri.

---

> **Dokumentasi ini dihasilkan secara otomatis melalui analisis menggunakan Laravel Boost MCP dan Superpowers Skills.**
