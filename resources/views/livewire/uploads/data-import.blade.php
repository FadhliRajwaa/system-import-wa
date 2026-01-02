<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Import Data Excel</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Upload data peserta baru dari file Excel.</p>
        </div>
        @if($previewMode)
            <flux:button variant="subtle" wire:click="cancel" icon="x-mark">Batal</flux:button>
        @endif
    </div>

    @if(!$previewMode)
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Upload Area (Main) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="p-8">
                        <div 
                            x-data="{
                                uploading: false,
                                processing: false,
                                progress: 0,
                                processingProgress: 0,
                                processingInterval: null,
                                
                                // Total progress: Upload = 0-70%, Processing = 70-100%
                                get totalProgress() {
                                    if (this.processing) {
                                        return 70 + (this.processingProgress * 0.3);
                                    }
                                    return this.progress * 0.7;
                                },
                                
                                get statusText() {
                                    if (this.processing) {
                                        if (this.processingProgress >= 100) return 'Selesai!';
                                        if (this.processingProgress >= 80) return 'Memvalidasi data...';
                                        if (this.processingProgress >= 50) return 'Membaca kolom...';
                                        return 'Memproses Excel...';
                                    }
                                    return 'Mengupload file...';
                                },
                                
                                startProcessing() {
                                    this.processing = true;
                                    this.processingProgress = 0;
                                    // Simulate processing progress
                                    this.processingInterval = setInterval(() => {
                                        if (this.processingProgress < 90) {
                                            this.processingProgress += Math.random() * 15 + 5;
                                            if (this.processingProgress > 90) this.processingProgress = 90;
                                        }
                                    }, 300);
                                },
                                
                                completeProcessing() {
                                    clearInterval(this.processingInterval);
                                    this.processingProgress = 100;
                                    setTimeout(() => {
                                        this.processing = false;
                                        this.uploading = false;
                                        this.progress = 0;
                                        this.processingProgress = 0;
                                    }, 500);
                                },
                                
                                resetState() {
                                    clearInterval(this.processingInterval);
                                    this.uploading = false;
                                    this.processing = false;
                                    this.progress = 0;
                                    this.processingProgress = 0;
                                }
                            }"
                            x-on:livewire-upload-start="uploading = true; progress = 0"
                            x-on:livewire-upload-finish="startProcessing()"
                            x-on:livewire-upload-cancel="resetState()"
                            x-on:livewire-upload-error="resetState()"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                            @processing-complete.window="completeProcessing()"
                            class="relative"
                        >
                            <!-- Drop Zone -->
                            <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 sm:p-10 text-center hover:border-brand-500 hover:bg-brand-50/30 dark:hover:bg-brand-900/10 transition-all group relative overflow-hidden">
                                <input type="file" 
                                    wire:model="uploadFile" 
                                    accept=".xlsx,.xls" 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    :disabled="uploading"
                                />
                                
                                <div class="flex flex-col items-center justify-center transition-transform group-hover:scale-105 duration-300 relative z-0">
                                    <div class="p-4 sm:p-5 bg-brand-50 dark:bg-brand-900/20 rounded-full mb-4 sm:mb-6 group-hover:shadow-lg group-hover:shadow-brand-500/20 transition-all">
                                        <flux:icon name="cloud-arrow-up" class="w-10 h-10 sm:w-12 sm:h-12 text-brand-600 dark:text-brand-400" />
                                    </div>
                                    
                                    <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white mb-2">
                                        Klik atau Drag File Excel
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto mb-4 sm:mb-6 leading-relaxed">
                                        Format: .xlsx atau .xls. Pastikan format kolom sesuai dengan template.
                                    </p>
                                    
                                    <flux:button variant="filled" class="pointer-events-none w-full sm:w-auto">Pilih File</flux:button>
                                </div>
                            </div>

                            <!-- Combined Progress Bar (Upload + Processing) -->
                            <div x-show="uploading || processing" x-transition.opacity.duration.300ms class="mt-6">
                                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm relative overflow-hidden">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center" :class="processing ? 'animate-pulse' : ''">
                                                <flux:icon x-show="!processing" name="arrow-up-tray" class="w-4 h-4 text-brand-600" />
                                                <flux:icon x-show="processing" x-cloak name="cog-6-tooth" class="w-4 h-4 text-brand-600 animate-spin" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 dark:text-white" x-text="statusText"></p>
                                                <p class="text-xs text-slate-500">Mohon jangan tutup halaman ini</p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold text-brand-600 dark:text-brand-400" x-text="Math.round(totalProgress) + '%'"></span>
                                    </div>
                                    
                                    <!-- Progress Track -->
                                    <div class="h-2.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <!-- Progress Fill -->
                                        <div class="h-full rounded-full transition-all duration-300 ease-out relative"
                                             :class="processing ? 'bg-gradient-to-r from-indigo-500 to-purple-500' : 'bg-gradient-to-r from-brand-500 to-indigo-500'"
                                             :style="'width: ' + Math.round(totalProgress) + '%'">
                                            <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Phase Indicators -->
                                    <div class="flex justify-between mt-2 text-xs text-slate-400">
                                        <span :class="progress > 0 ? 'text-brand-600 font-medium' : ''">Upload</span>
                                        <span :class="processing ? 'text-indigo-600 font-medium' : ''">Proses</span>
                                        <span :class="processingProgress >= 100 ? 'text-emerald-600 font-medium' : ''">Selesai</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Error State -->
                            @error('uploadFile')
                                <div class="mt-6 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-100 dark:border-red-800 animate-shake">
                                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-none">
                                        <flux:icon name="exclamation-circle" class="w-5 h-5 text-red-600" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Gagal Upload</h4>
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">{{ $message }}</p>
                                    </div>
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Template Info -->
                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <flux:icon name="book-open" class="w-5 h-5 text-brand-600" />
                        Panduan Template
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex gap-3 text-sm">
                            <span class="flex-none w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300">1</span>
                            <p class="text-slate-600 dark:text-slate-400">Siapkan file Excel dengan header yang sesuai.</p>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <span class="flex-none w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300">2</span>
                            <p class="text-slate-600 dark:text-slate-400">Kolom <strong>Nama, NRP/NIP, No HP</strong> wajib diisi.</p>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <span class="flex-none w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-300">3</span>
                            <p class="text-slate-600 dark:text-slate-400">Format tanggal: dd/mm/yyyy (misal: 25/12/2023).</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <flux:button variant="outline" size="sm" class="w-full" icon="arrow-down-tray" wire:click="downloadTemplate">
                            Download Template
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Template Table (Collapsible) -->
        <div x-data="{ open: false }" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <button @click="open = !open" class="w-full flex items-center justify-between p-4 bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <span class="font-medium text-slate-700 dark:text-slate-300 text-sm flex items-center gap-2">
                    <flux:icon name="table-cells" class="w-4 h-4" />
                    Detail Kolom Excel
                </span>
                <flux:icon name="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
            </button>
            <div x-show="open" x-collapse>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase font-medium">
                            <tr>
                                <th class="px-4 py-2 w-10">Kol</th>
                                <th class="px-4 py-2">Header</th>
                                <th class="px-4 py-2">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                             <tr><td class="px-4 py-2 font-mono text-slate-400">A</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">Nama</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Nama Lengkap Peserta <span class="text-red-500">*</span></td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">B</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">Pangkat</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Pangkat/Golongan</td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">C</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">NRP/NIP</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Nomor Identitas Unik <span class="text-red-500">*</span></td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">D</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">Jabatan</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Jabatan Peserta</td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">E</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">satuan_kerja</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Satuan Kerja</td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">F</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">no_hp</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Nomor WhatsApp (08xxx) <span class="text-red-500">*</span></td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">G</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">tgl_lahir</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Tanggal Lahir (dd/mm/yyyy)</td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">H</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">gender</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Jenis Kelamin (Pria/Wanita)</td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">I</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">no_lab</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Nomor Lab Unik</td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">J</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">tgl_pemeriksaan</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Tanggal Periksa (dd/mm/yyyy) <span class="text-red-500">*</span></td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">K</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">kode_paket</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Kode Paket MCU (sesuai master)</td></tr>
                             <tr><td class="px-4 py-2 font-mono text-slate-400">L</td><td class="px-4 py-2 font-bold text-slate-900 dark:text-white">kode_perusahaan</td><td class="px-4 py-2 text-slate-500 dark:text-slate-400">Kode Instansi (sesuai master)</td></tr>
                        </tbody>
                    </table>
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border-t border-amber-200 dark:border-amber-800">
                        <p class="text-xs text-amber-700 dark:text-amber-400"><span class="text-red-500">*</span> = Kolom wajib diisi</p>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Preview Mode -->
        <div class="space-y-6">
            <!-- Stats Grid - 4 Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                        <flux:icon name="document-text" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Baris</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $this->totalRows }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                        <flux:icon name="check-circle" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Valid</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->validCount }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-amber-100 dark:border-amber-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                        <flux:icon name="exclamation-triangle" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Warning</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $this->warningCount }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-red-100 dark:border-red-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600">
                        <flux:icon name="x-circle" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Error/Blokir</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->invalidCount }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Importable Summary -->
            <div class="bg-gradient-to-r from-brand-50 to-indigo-50 dark:from-brand-900/20 dark:to-indigo-900/20 rounded-2xl p-4 border border-brand-200 dark:border-brand-800">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                            <flux:icon name="arrow-down-tray" class="w-5 h-5 text-brand-600" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-brand-800 dark:text-brand-300">Data yang Dapat Diimport</p>
                            <p class="text-xs text-brand-600 dark:text-brand-400">Valid + Warning = {{ $this->importableCount }} data</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg font-medium">{{ $this->validCount }} Valid</span>
                        <span class="text-slate-400">+</span>
                        <span class="px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg font-medium">{{ $this->warningCount }} Warning</span>
                    </div>
                </div>
            </div>

            <!-- Error Details (jika ada) -->
            @if($this->invalidCount > 0)
                <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-200 dark:border-red-800 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <flux:icon name="exclamation-triangle" class="w-5 h-5 text-red-600" />
                        </div>
                        <div>
                            <h3 class="font-bold text-red-800 dark:text-red-300">{{ $this->invalidCount }} Baris Tidak Valid</h3>
                            <p class="text-xs text-red-600 dark:text-red-400">Data berikut tidak dapat diimport karena format tidak valid</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($this->invalidRows as $invalidRow)
                            <div class="flex items-start gap-3 p-3 bg-white dark:bg-slate-900 rounded-xl border border-red-100 dark:border-red-800">
                                <span class="flex-none px-2 py-0.5 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded text-xs font-bold">
                                    Baris {{ $invalidRow['row_number'] }}
                                </span>
                                <span class="text-sm text-red-700 dark:text-red-300 font-medium">
                                    {{ $invalidRow['error'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 p-4 bg-white/70 dark:bg-slate-900/70 rounded-xl border border-red-100 dark:border-red-800">
                        <h4 class="text-sm font-bold text-red-800 dark:text-red-300 mb-3 flex items-center gap-2">
                            <flux:icon name="information-circle" class="w-5 h-5" />
                            Panduan Format Data yang Benar
                        </h4>
                        
                        <div class="grid md:grid-cols-2 gap-4 text-xs">
                            <div class="space-y-2">
                                <h5 class="font-bold text-red-700 dark:text-red-400">Field Wajib:</h5>
                                <ul class="text-red-600 dark:text-red-400 space-y-1.5">
                                    <li class="flex items-start gap-2">
                                        <span class="px-1.5 py-0.5 bg-red-100 dark:bg-red-900/50 rounded font-mono">NRP/NIP</span>
                                        <span>Tidak boleh kosong</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="px-1.5 py-0.5 bg-red-100 dark:bg-red-900/50 rounded font-mono">Nama</span>
                                        <span>Tidak boleh kosong</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="px-1.5 py-0.5 bg-red-100 dark:bg-red-900/50 rounded font-mono">Tgl Periksa</span>
                                        <span>Tidak boleh kosong</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="space-y-2">
                                <h5 class="font-bold text-red-700 dark:text-red-400">Format Tanggal (Indonesia):</h5>
                                <ul class="text-red-600 dark:text-red-400 space-y-1.5">
                                    <li class="flex items-start gap-2">
                                        <span class="text-slate-500">Format:</span>
                                        <span class="font-mono font-bold">TANGGAL/BULAN/TAHUN</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-green-600">✅ Benar:</span>
                                        <span class="font-mono">20/01/2025</span>
                                        <span class="text-slate-400">(20 Jan 2025)</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-green-600">✅ Benar:</span>
                                        <span class="font-mono">05/12/1990</span>
                                        <span class="text-slate-400">(5 Des 1990)</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-red-500">❌ Salah:</span>
                                        <span class="font-mono">01/20/2025</span>
                                        <span class="text-slate-400">(bulan 20 tidak ada)</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="text-red-500">❌ Salah:</span>
                                        <span class="font-mono">31/02/2025</span>
                                        <span class="text-slate-400">(31 Feb tidak ada)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-3 p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                            <p class="text-xs text-amber-700 dark:text-amber-400">
                                <strong>💡 Tips:</strong> Di Excel, klik kanan kolom tanggal → Format Cells → Custom → ketik <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">dd/mm/yyyy</code>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Import Errors (Error saat commit/simpan ke database) -->
            @if(count($importErrors) > 0)
                <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl border-2 border-red-300 dark:border-red-700 p-6 shadow-lg">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <flux:icon name="exclamation-circle" class="w-7 h-7 text-red-600" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-red-800 dark:text-red-300">Gagal Import ke Database</h3>
                            <p class="text-sm text-red-600 dark:text-red-400">Terjadi error saat menyimpan data ke database</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($importErrors as $error)
                            <div class="p-4 bg-white dark:bg-slate-900 rounded-xl border border-red-200 dark:border-red-800">
                                <div class="flex items-start gap-3">
                                    <span class="flex-none px-2.5 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded-lg text-xs font-bold uppercase">
                                        {{ $error['type'] }}: {{ $error['code'] ?? 'ERR' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-red-700 dark:text-red-300 font-medium">
                                    {{ $error['message'] }}
                                </p>
                                @if(!empty($error['suggestion']))
                                    <p class="mt-2 text-sm text-amber-600 dark:text-amber-400 flex items-center gap-2">
                                        <flux:icon name="light-bulb" class="w-4 h-4" />
                                        <span><strong>Saran:</strong> {{ $error['suggestion'] }}</span>
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Main Content - 3 Preview Tables -->
                <!-- Main Content - Tabbed Preview Tables -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Tab Navigation -->
                    <div class="flex flex-wrap items-center gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
                        <button 
                            wire:click="setTab('valid')" 
                            x-on:click="$dispatch('table-loading')"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'valid' ? 'bg-white dark:bg-slate-700 text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                        >
                            <flux:icon name="check-circle" class="w-4 h-4" />
                            Valid
                            <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'valid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ count($categorizedValid) }}
                            </span>
                        </button>
                        
                        <button 
                            wire:click="setTab('warning')" 
                            x-on:click="$dispatch('table-loading')"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'warning' ? 'bg-white dark:bg-slate-700 text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                        >
                            <flux:icon name="exclamation-triangle" class="w-4 h-4" />
                            Warning
                            <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ count($categorizedWarning) }}
                            </span>
                        </button>
                        
                        <button 
                            wire:click="setTab('invalid')" 
                            x-on:click="$dispatch('table-loading')"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'invalid' ? 'bg-white dark:bg-slate-700 text-red-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                        >
                            <flux:icon name="x-circle" class="w-4 h-4" />
                            Error
                            <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'invalid' ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ count($categorizedInvalid) }}
                            </span>
                        </button>
                    </div>

                    <!-- Dynamic Table Content -->
                    <div 
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative min-h-[400px]"
                        x-data="{ tableLoading: false }"
                        @table-loading.window="tableLoading = true"
                        @table-loaded.window="tableLoading = false"
                    >
                        
                        <!-- Loading State Overlay - Now controlled by Alpine events -->
                        <div x-show="tableLoading" x-cloak class="absolute inset-0 z-10 bg-white/80 dark:bg-slate-900/80 flex items-center justify-center backdrop-blur-sm">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-10 h-10 border-4 border-brand-200 border-t-brand-600 rounded-full animate-spin"></div>
                                <p class="text-sm font-bold text-brand-600">Memuat Data...</p>
                            </div>
                        </div>

                        <!-- Info Header per Tab -->
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            @if($activeTab === 'valid')
                                <div class="flex items-center gap-3 text-emerald-700 dark:text-emerald-400">
                                    <flux:icon name="check-circle" class="w-5 h-5" />
                                    <span class="font-medium text-sm">Data siap diimport. Tidak ada masalah ditemukan.</span>
                                </div>
                            @elseif($activeTab === 'warning')
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3 text-amber-700 dark:text-amber-400">
                                        <flux:icon name="exclamation-triangle" class="w-5 h-5" />
                                        <span class="font-medium text-sm">Data valid tapi duplikat dengan data lama. Pilih strategi import di samping.</span>
                                    </div>
                                    <!-- Penjelasan Strategi -->
                                    <div class="grid grid-cols-2 gap-3 text-xs">
                                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                                            <strong class="text-blue-700 dark:text-blue-300">Strategy Skip:</strong> Lewati (jangan import)
                                        </div>
                                        <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded border border-indigo-200 dark:border-indigo-800">
                                            <strong class="text-indigo-700 dark:text-indigo-300">Strategy Update:</strong> Buat data baru
                                        </div>
                                    </div>
                                </div>
                            @elseif($activeTab === 'invalid')
                                <div class="flex items-center gap-3 text-red-700 dark:text-red-400">
                                    <flux:icon name="x-circle" class="w-5 h-5" />
                                    <span class="font-medium text-sm">Data diblokir. Perbaiki di Excel lalu upload ulang.</span>
                                </div>
                            @endif
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 w-16">No</th>
                                        <th class="px-4 py-3">Nama</th>
                                        <th class="px-4 py-3">NRP/NIP</th>
                                        <th class="px-4 py-3">No Lab</th>
                                        <th class="px-4 py-3">Tgl Periksa</th>
                                        <th class="px-4 py-3">
                                            @if($activeTab === 'valid') Status
                                            @elseif($activeTab === 'warning') Keterangan
                                            @else Error
                                            @endif
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($rows as $row)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-1.5 py-0.5 rounded">
                                                    {{ $row['row_number'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                                {{ $row['data']['nama'] ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">
                                                {{ $row['data']['nrp_nip'] ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded text-xs font-mono">
                                                    {{ $row['data']['no_lab'] ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-slate-500 text-xs">
                                                {{ isset($row['data']['tanggal_periksa']) ? (is_object($row['data']['tanggal_periksa']) ? $row['data']['tanggal_periksa']->format('d/m/Y') : $row['data']['tanggal_periksa']) : '-' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($activeTab === 'valid')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded text-xs font-medium">
                                                        ✓ Valid
                                                    </span>
                                                @elseif($activeTab === 'warning')
                                                    <div class="text-xs max-w-xs">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded font-medium mb-1">
                                                            ⚠️ NRP Sudah Ada
                                                        </span>
                                                        @foreach($row['warnings'] as $warn)
                                                            <p class="text-amber-600 dark:text-amber-400 leading-tight">{{ $warn['message'] }}</p>
                                                        @endforeach
                                                    </div>
                                                @elseif($activeTab === 'invalid')
                                                    <div class="text-xs text-red-600 dark:text-red-400 font-medium max-w-xs">
                                                        @foreach($row['errors'] as $err)
                                                            <p class="mb-1">• {{ $err['message'] }}</p>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                                <div class="flex flex-col items-center gap-2">
                                                    <flux:icon name="document-magnifying-glass" class="w-8 h-8 text-slate-300" />
                                                    <p>Tidak ada data di kategori ini.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            {{ $rows->links() }}
                        </div>
                    </div>
                    
                    <!-- Empty State jika file kosong sama sekali -->
                    @if(count($categorizedValid) === 0 && count($categorizedWarning) === 0 && count($categorizedInvalid) === 0)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-8 text-center">
                        <flux:icon name="document-magnifying-glass" class="w-12 h-12 text-slate-400 mx-auto mb-4" />
                        <p class="text-slate-500">Tidak ada data yang dapat diproses dari file Excel.</p>
                    </div>
                    @endif
                </div>

                <!-- Settings & Action -->
                <div class="space-y-6">
                    <!-- Duplication Strategy -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white mb-3">Strategi Duplikasi</h3>
                        <p class="text-xs text-slate-500 mb-3">Untuk data dengan NRP yang sudah ada (Warning):</p>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all {{ $duplicationStrategy === 'skip' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="duplicationStrategy" value="skip" class="text-brand-600 focus:ring-brand-500" />
                                <div class="text-sm">
                                    <span class="font-medium block text-slate-900 dark:text-white">Skip</span>
                                    <span class="text-xs text-slate-500">Lewati jika NRP+Tanggal sudah ada</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all {{ $duplicationStrategy === 'update' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                                <input type="radio" wire:model.live="duplicationStrategy" value="update" class="text-brand-600 focus:ring-brand-500" />
                                <div class="text-sm">
                                    <span class="font-medium block text-slate-900 dark:text-white">Update</span>
                                    <span class="text-xs text-slate-500">Perbarui data yang sudah ada</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Import Button -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-800">
                        @php
                            $canImport = $this->importableCount > 0;
                        @endphp
                        
                        @if($canImport)
                            <flux:button variant="primary" class="w-full shadow-lg shadow-brand-500/20" icon="check" wire:click="commitImport" wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait">
                                <span wire:loading.remove wire:target="commitImport">Proses Import</span>
                                <span wire:loading wire:target="commitImport" class="flex items-center gap-2">
                                    <flux:icon name="arrow-path" class="w-4 h-4 animate-spin" />
                                    Importing...
                                </span>
                            </flux:button>
                            <p class="text-xs text-center text-slate-500 mt-2">
                                Akan memproses <strong class="text-brand-600">{{ $this->importableCount }}</strong> data 
                                ({{ $this->validCount }} valid + {{ $this->warningCount }} warning)
                            </p>
                            @if($this->invalidCount > 0)
                                <p class="text-xs text-center text-red-500 mt-1">
                                    ⚠️ {{ $this->invalidCount }} data error akan dilewati
                                </p>
                            @endif
                        @else
                            <flux:button variant="danger" class="w-full opacity-50 cursor-not-allowed" icon="x-mark" disabled>
                                Tidak Ada Data Valid
                            </flux:button>
                            <p class="text-xs text-center text-red-500 mt-2 font-medium">
                                Semua data memiliki error dan tidak dapat diimport
                            </p>
                        @endif
                    </div>
                    
                    <!-- Legend -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-3">Keterangan Warna:</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                                <span class="text-slate-600 dark:text-slate-400"><strong>Hijau:</strong> Valid, langsung diimport</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-amber-500 rounded-full"></span>
                                <span class="text-slate-600 dark:text-slate-400"><strong>Kuning:</strong> Valid + NRP sudah ada</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                <span class="text-slate-600 dark:text-slate-400"><strong>Merah:</strong> Error, diblokir</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>