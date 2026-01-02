<section class="w-full max-w-4xl mx-auto animate-fade-in-up">
    <!-- Enhanced Header -->
    <div class="mb-8 space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/30">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </div>
            Konfigurasi SaungWA
        </h1>
        <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Atur App Key dan Auth Key SaungWA untuk mengirim WhatsApp otomatis</p>
    </div>

    <!-- Settings Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden mb-8">
        <div class="p-6 sm:p-8">
            <form wire:submit="updateSaungwaSettings" class="space-y-6 max-w-2xl">

                <!-- App Key -->
                <flux:field>
                    <flux:label>SaungWA App Key</flux:label>
                    <flux:input
                        wire:model="saungwa_appkey"
                        type="password"
                        placeholder="Masukkan App Key dari dashboard SaungWA"
                        icon="key"
                    />
                    <flux:description>App Key bisa didapat dari menu API di dashboard SaungWA</flux:description>
                    <flux:error name="saungwa_appkey" />
                </flux:field>

                <!-- Auth Key -->
                <flux:field>
                    <flux:label>SaungWA Auth Key</flux:label>
                    <flux:input
                        wire:model="saungwa_authkey"
                        type="password"
                        placeholder="Masukkan Auth Key dari dashboard SaungWA"
                        icon="lock-closed"
                    />
                    <flux:description>Auth Key digunakan untuk mengautentikasi request API</flux:description>
                    <flux:error name="saungwa_authkey" />
                </flux:field>

                <!-- Phone Display -->
                <flux:field>
                    <flux:label>Nomor WhatsApp</flux:label>
                    <flux:input
                        wire:model="saungwa_phone"
                        placeholder="08123456789"
                        icon="phone"
                    />
                    <flux:description>Nomor WA yang terdaftar di SaungWA (untuk referensi)</flux:description>
                    <flux:error name="saungwa_phone" />
                </flux:field>

                <!-- Action Buttons -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    @if (session('status') === 'saungwa-saved')
                        <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800">
                            <flux:icon name="check-circle" class="w-4 h-4" />
                            Pengaturan SaungWA berhasil disimpan!
                        </div>
                    @endif

                    @if (session('test-success'))
                        <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800">
                            <flux:icon name="check-circle" class="w-4 h-4" />
                            {{ session('test-success') }}
                        </div>
                    @endif

                    @if (session('test-error'))
                        <div class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400 px-4 py-2 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                            <flux:icon name="x-circle" class="w-4 h-4" />
                            {{ session('test-error') }}
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <flux:button type="button" variant="subtle" wire:click="testConnection" icon="signal">
                            Test Koneksi
                        </flux:button>
                        <flux:button variant="primary" type="submit" icon="check" class="shadow-xl shadow-green-500/25 min-w-[200px]">
                            Simpan Pengaturan
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/10 dark:to-cyan-900/10 rounded-2xl border border-blue-200 dark:border-blue-900/30 overflow-hidden shadow-lg shadow-blue-900/5 dark:shadow-blue-950/20">
        <div class="p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                    <flux:icon name="information-circle" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-blue-900 dark:text-blue-300">Cara Mendapatkan Credentials</h3>
                    <ol class="mt-3 text-sm text-blue-800 dark:text-blue-200 list-decimal list-inside space-y-2">
                        <li>Login ke dashboard SaungWA di <a href="https://saungwa.com" target="_blank" class="underline font-medium">saungwa.com</a></li>
                        <li>Buka menu "API" atau "Integrasi"</li>
                        <li>Salin App Key dan Auth Key yang tersedia</li>
                        <li>Tempel kedua key di form di atas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>
