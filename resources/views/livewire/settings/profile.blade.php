<section class="w-full max-w-4xl mx-auto animate-fade-in-up">
    <!-- Enhanced Header -->
    <div class="mb-8 space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                <flux:icon name="user" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
            Profil Saya
        </h1>
        <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Perbarui informasi profil dan alamat email akun Anda</p>
    </div>

    <!-- Enhanced Profile Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden mb-8">
        <div class="p-6 sm:p-8">
            <form wire:submit="updateProfileInformation" class="space-y-8 max-w-2xl">

                <!-- Profile Avatar & Info -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pb-8 border-b border-slate-100 dark:border-slate-800">
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white font-bold text-3xl shadow-xl shadow-brand-500/30 ring-4 ring-white dark:ring-slate-800">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 rounded-full ring-4 ring-white dark:ring-slate-900 flex items-center justify-center">
                            <flux:icon name="check" class="w-3 h-3 text-white" />
                        </div>
                        <div class="absolute inset-0 rounded-2xl bg-brand-500/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                            <flux:icon name="camera" class="w-6 h-6 text-white" />
                        </div>
                    </div>

                    <div class="text-center sm:text-left flex-1">
                        <h3 class="font-bold text-xl text-slate-900 dark:text-white">{{ auth()->user()->name }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">{{ auth()->user()->email }}</p>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-400 border border-brand-200 dark:border-brand-800">
                                <flux:icon name="shield-check" class="w-3 h-3" />
                                Administrator
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                Online
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="space-y-6">
                    <flux:field>
                        <flux:label>Nama Lengkap</flux:label>
                        <flux:input wire:model="name" type="text" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap Anda" icon="user" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Alamat Email</flux:label>
                        <flux:input wire:model="email" type="email" required autocomplete="email" placeholder="nama@email.com" icon="envelope" />
                        <flux:error name="email" />
                    </flux:field>

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                        <div class="p-5 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 text-amber-800 dark:text-amber-200 rounded-2xl border border-amber-200 dark:border-amber-800">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                                    <flux:icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold">{{ __('Email Anda belum diverifikasi.') }}</p>
                                    <p class="text-sm opacity-75 mt-1">{{ __('Verifikasi email untuk mengakses semua fitur.') }}</p>
                                    <button wire:click.prevent="resendVerificationNotification" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-amber-500/25">
                                        <flux:icon name="paper-airplane" class="w-4 h-4" />
                                        {{ __('Kirim Verifikasi') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if (session('status') === 'verification-link-sent')
                            <div class="mt-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                <flux:icon name="check-circle" class="w-5 h-5" />
                                <span class="text-sm font-medium">{{ __('Link verifikasi baru telah dikirim ke email Anda.') }}</span>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <x-action-message class="text-sm text-emerald-600 dark:text-emerald-400 flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800" on="profile-updated">
                        <flux:icon name="check-circle" class="w-4 h-4" />
                        Profil berhasil diperbarui
                    </x-action-message>
                    <flux:button variant="primary" type="submit" icon="check" class="shadow-xl shadow-brand-500/25 min-w-[200px]">
                        Simpan Perubahan
                    </flux:button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Delete Account Section -->
    <div class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/10 dark:to-rose-900/10 rounded-2xl border border-red-200 dark:border-red-900/30 overflow-hidden shadow-lg shadow-red-900/5 dark:shadow-red-950/20">
        <div class="p-6 sm:p-8">
            <div class="flex items-start gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0">
                    <flux:icon name="exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-red-900 dark:text-red-300">Zona Bahaya</h3>
                    <p class="text-sm text-red-700 dark:text-red-400 mt-1">Tindakan ini bersifat permanen dan tidak dapat dibatalkan</p>
                </div>
            </div>
            <livewire:settings.delete-user-form />
        </div>
    </div>
</section>