<?php

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Participants\Index as ParticipantsIndex;
use App\Livewire\Participants\Show as ParticipantsShow;
use App\Livewire\Settings\AdminUsers\Index as AdminUsersIndex;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Companies\Index as CompaniesIndex;
use App\Livewire\Settings\Packages\Index as PackagesIndex;
use App\Livewire\Settings\Paket\Index as PaketIndex;
use App\Livewire\Settings\Paket\Create as PaketCreate;
use App\Livewire\Settings\Paket\Edit as PaketEdit;
use App\Livewire\Settings\Instansi\Index as InstansiIndex;
use App\Livewire\Settings\Instansi\Create as InstansiCreate;
use App\Livewire\Settings\Instansi\Edit as InstansiEdit;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Uploads\DataImport;
use App\Livewire\Uploads\PdfImport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

// Routes for all authenticated users (both Admin and User)
Route::middleware(['auth'])->group(function () {

    Route::get('dashboard', DashboardIndex::class)->name('dashboard');

    Route::get('participants', ParticipantsIndex::class)->name('participants.index');
    Route::get('participants/{participant}', ParticipantsShow::class)->name('participants.show');
    Route::get('participants/{nrp_nip}/{tanggal_periksa}/edit', \App\Livewire\Participants\Edit::class)->name('participants.edit');

    // Import Data routes (available for both Admin and User)
    Route::get('uploads/data', DataImport::class)->name('uploads.data');
    Route::get('uploads/pdf', PdfImport::class)->name('uploads.pdf');

    // Profile settings (available for both Admin and User)
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');
    Route::get('settings/saungwa', \App\Livewire\Settings\Saungwa::class)->name('settings.saungwa');
    
    // Pengaturan Paket dan Instansi (all users with scoping)
    Route::get('settings/paket', PaketIndex::class)->name('settings.paket');
    Route::get('settings/paket/create', PaketCreate::class)->name('settings.paket.create');
    Route::get('settings/paket/{paket}/edit', PaketEdit::class)->name('settings.paket.edit');
    Route::get('settings/instansi', InstansiIndex::class)->name('settings.instansi');
    Route::get('settings/instansi/create', InstansiCreate::class)->name('settings.instansi.create');
    Route::get('settings/instansi/{instansi}/edit', InstansiEdit::class)->name('settings.instansi.edit');
});

// Routes for Admin only
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('settings/packages', PackagesIndex::class)->name('settings.packages');
    Route::get('settings/companies', CompaniesIndex::class)->name('settings.companies');
    Route::get('settings/admin-users', AdminUsersIndex::class)->name('settings.admin-users');
});

