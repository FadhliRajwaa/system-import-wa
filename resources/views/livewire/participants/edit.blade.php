<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Edit Data Peserta</h2>
                <p class="text-sm text-slate-500 mt-1">NRP/NIP: <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ $nrp_nip }}</span></p>
            </div>
            <div class="hidden sm:block">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                    Tgl Periksa: {{ $tanggal_periksa }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <form wire:submit.prevent="update" class="space-y-6">
                <!-- Section: Identitas -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">Identitas Diri</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="data.nama" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm" placeholder="Nama Peserta">
                            @error('data.nama') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Tanggal Lahir
                            </label>
                            <input type="date" wire:model="data.tanggal_lahir" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm">
                            @error('data.tanggal_lahir') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- No HP -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                No Handphone
                            </label>
                            <input type="text" wire:model="data.no_hp_raw" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm" placeholder="08xxx">
                            @error('data.no_hp_raw') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Pekerjaan -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">Data Pekerjaan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Pangkat -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Pangkat/Golongan
                            </label>
                            <input type="text" wire:model="data.pangkat" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm">
                        </div>

                        <!-- Jabatan -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Jabatan
                            </label>
                            <input type="text" wire:model="data.jabatan" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm">
                        </div>

                        <!-- Satuan Kerja -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Satuan Kerja
                            </label>
                            <input type="text" wire:model="data.satuan_kerja" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Section: Data Pemeriksaan -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">Data Pemeriksaan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- No Lab -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                No Lab
                            </label>
                            <input type="text" wire:model="data.no_lab" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm">
                        </div>

                        <!-- Kode Paket -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Kode Paket
                            </label>
                            <input type="text" wire:model="data.kode_paket" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm">
                        </div>

                        <!-- Kode Instansi -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Kode Instansi
                            </label>
                            <input type="text" wire:model="data.kode_instansi" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-4">
                    <button type="button" wire:click="cancel" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-colors shadow-sm">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 border border-transparent rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-sm transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
