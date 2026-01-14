<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Import File PDF</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Upload file PDF hasil pemeriksaan peserta. Nama file harus sesuai dengan Nomor Lab.</p>
        </div>
        @if($showResults)
            <flux:button variant="subtle" wire:click="resetUpload" icon="x-mark">Reset</flux:button>
        @endif
    </div>

    @if(!$showResults)
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
                                fallbackTimeout: null,
                                
                                // Total progress: Upload = 0-60%, Processing = 60-100%
                                get totalProgress() {
                                    if (this.processing) {
                                        return 60 + (this.processingProgress * 0.4);
                                    }
                                    return this.progress * 0.6;
                                },
                                
                                get statusText() {
                                    if (this.processing) {
                                        if (this.processingProgress >= 100) return 'Selesai!';
                                        if (this.processingProgress >= 80) return 'Menyimpan file...';
                                        if (this.processingProgress >= 50) return 'Mencocokkan No Lab...';
                                        return 'Membaca PDF...';
                                    }
                                    return 'Mengupload PDF...';
                                },
                                
                                startProcessing() {
                                    this.processing = true;
                                    this.processingProgress = 0;
                                    
                                    // Clear any existing intervals/timeouts
                                    this.clearTimers();
                                    
                                    // Progress simulation
                                    this.processingInterval = setInterval(() => {
                                        if (this.processingProgress < 90) {
                                            this.processingProgress += Math.random() * 12 + 3;
                                            if (this.processingProgress > 90) this.processingProgress = 90;
                                        }
                                    }, 400);
                                    
                                    // Fallback timeout: auto-complete after 120 seconds if no response
                                    this.fallbackTimeout = setTimeout(() => {
                                        console.warn('PDF processing fallback timeout triggered after 120s');
                                        if (this.processing) {
                                            this.completeProcessing();
                                        }
                                    }, 120000);
                                },
                                
                                completeProcessing() {
                                    this.clearTimers();
                                    this.processingProgress = 100;
                                    setTimeout(() => {
                                        this.processing = false;
                                        this.uploading = false;
                                        this.progress = 0;
                                        this.processingProgress = 0;
                                    }, 500);
                                },
                                
                                resetState() {
                                    this.clearTimers();
                                    this.uploading = false;
                                    this.processing = false;
                                    this.progress = 0;
                                    this.processingProgress = 0;
                                },
                                
                                clearTimers() {
                                    if (this.processingInterval) {
                                        clearInterval(this.processingInterval);
                                        this.processingInterval = null;
                                    }
                                    if (this.fallbackTimeout) {
                                        clearTimeout(this.fallbackTimeout);
                                        this.fallbackTimeout = null;
                                    }
                                },
                                
                                init() {
                                    // Listen for Livewire errors to reset state
                                    Livewire.hook('request', ({ fail }) => {
                                        fail(({ preventDefault }) => {
                                            console.error('Livewire request failed, resetting state');
                                            this.resetState();
                                        });
                                    });
                                }
                            }"
                            x-on:livewire-upload-start="uploading = true; progress = 0"
                            x-on:livewire-upload-finish="startProcessing()"
                            x-on:livewire-upload-cancel="resetState()"
                            x-on:livewire-upload-error="resetState()"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                            @pdf-processing-complete.window="completeProcessing()"
                            wire:ignore.self
                            class="relative min-h-[16rem]"
                        >
                            <!-- Drop Zone -->
                            <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 sm:p-10 text-center hover:border-red-500 hover:bg-red-50/30 dark:hover:bg-red-900/10 transition-all group relative overflow-hidden h-full flex flex-col items-center justify-center">
                                <input type="file" 
                                    wire:model="pdfFiles" 
                                    accept=".pdf" 
                                    multiple
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    :disabled="uploading || processing"
                                    x-on:change="
                                        if ($event.target.files.length > 0) {
                                            uploading = true;
                                            progress = 0;
                                        }
                                    "
                                />
                                
                                <div class="flex flex-col items-center justify-center transition-transform group-hover:scale-105 duration-300 relative z-0">
                                    <div class="p-4 sm:p-5 bg-red-50 dark:bg-red-900/20 rounded-full mb-4 sm:mb-6 group-hover:shadow-lg group-hover:shadow-red-500/20 transition-all">
                                        <flux:icon name="document-arrow-up" class="w-10 h-10 sm:w-12 sm:h-12 text-red-600 dark:text-red-400" />
                                    </div>
                                    
                                    <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white mb-2">
                                        Klik atau Drag File PDF
                                    </h3>
                                    <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto mb-4 sm:mb-6 leading-relaxed">
                                        Anda dapat memilih <strong>maksimal 50 file PDF</strong> sekaligus (maks. <strong>5MB/file</strong>). Nama file harus sama dengan Nomor Lab peserta.
                                    </p>
                                    
                                    <flux:button variant="filled" class="pointer-events-none bg-red-600 hover:bg-red-700 w-full sm:w-auto">Pilih File PDF</flux:button>
                                </div>
                            </div>

                            <!-- Combined Progress Bar (Upload + Processing) -->
                            <div x-show="uploading || processing" x-transition.opacity.duration.300ms class="absolute inset-x-0 bottom-0 p-4 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm rounded-b-2xl border-t border-slate-200 dark:border-slate-800 z-30">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 flex items-center justify-center" :class="processing ? '' : 'animate-pulse'">
                                            <flux:icon x-show="!processing" name="arrow-up-tray" class="w-4 h-4 text-red-500" />
                                            <flux:icon x-show="processing" x-cloak name="cog-6-tooth" class="w-4 h-4 text-red-500 animate-spin" />
                                        </div>
                                        <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="statusText"></span>
                                    </div>
                                    <span class="text-sm font-bold text-red-600 dark:text-red-400" x-text="Math.round(totalProgress) + '%'"></span>
                                </div>
                                
                                <!-- Progress Track -->
                                <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <!-- Progress Fill -->
                                    <div class="h-full rounded-full transition-all duration-300 ease-out relative"
                                         :class="processing ? 'bg-gradient-to-r from-orange-500 to-red-500' : 'bg-gradient-to-r from-red-500 to-rose-500'"
                                         :style="'width: ' + Math.round(totalProgress) + '%'">
                                        <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                                    </div>
                                </div>
                                
                                <!-- Phase Indicators -->
                                <div class="flex justify-between mt-1.5 text-xs text-slate-400">
                                    <span :class="progress > 0 ? 'text-red-600 font-medium' : ''">Upload</span>
                                    <span :class="processing ? 'text-orange-600 font-medium' : ''">Proses</span>
                                    <span :class="processingProgress >= 100 ? 'text-emerald-600 font-medium' : ''">Selesai</span>
                                </div>
                            </div>

                            <!-- Server Processing State (Reading PDFs) -->
                            <div x-show="processing" x-cloak class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm rounded-2xl animate-fade-in">
                                <div class="flex flex-col items-center p-6 text-center max-w-xs">
                                    <!-- Circular Progress -->
                                    <div class="relative w-20 h-20 mb-4">
                                        <svg class="w-20 h-20 transform -rotate-90">
                                            <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="6" fill="none" class="text-slate-200 dark:text-slate-700"/>
                                            <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="6" fill="none"
                                                    class="text-red-500"
                                                    stroke-linecap="round"
                                                    :stroke-dasharray="226.2"
                                                    :stroke-dashoffset="226.2 - (processingProgress / 100 * 226.2)"/>
                                        </svg>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-lg font-bold text-red-600" x-text="Math.round(processingProgress) + '%'"></span>
                                        </div>
                                    </div>
                                    <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1" x-text="statusText"></h4>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Mohon tunggu sebentar...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Files Preview -->
                        @if(count($pdfFiles) > 0)
                            <div class="mt-6 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-slate-700 dark:text-slate-300">File yang dipilih ({{ count($pdfFiles) }})</h4>
                                    <flux:button variant="primary" wire:click="uploadFiles" icon="arrow-up-tray" class="bg-red-600 hover:bg-red-700" wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait">
                                        <span wire:loading.remove wire:target="uploadFiles">Upload & Proses</span>
                                        <span wire:loading wire:target="uploadFiles" class="flex items-center gap-2">
                                            <flux:icon name="arrow-path" class="w-4 h-4 animate-spin" />
                                            Memproses...
                                        </span>
                                    </flux:button>
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-2">
                                    @foreach($pdfFiles as $index => $file)
                                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                                            <flux:icon name="document" class="w-5 h-5 text-red-500" />
                                            <span class="text-sm text-slate-700 dark:text-slate-300 flex-1 truncate">{{ $file->getClientOriginalName() }}</span>
                                            <span class="text-xs text-slate-400">{{ number_format($file->getSize() / 1024, 1) }} KB</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Error State -->
                        @error('pdfFiles')
                            <div class="mt-6 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-100 dark:border-red-800 animate-shake">
                                <flux:icon name="exclamation-circle" class="w-5 h-5 text-red-600" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-red-700 dark:text-red-300">Gagal Upload</p>
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                </div>
                            </div>
                        @enderror
                        @error('pdfFiles.*')
                            <div class="mt-6 flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-100 dark:border-red-800 animate-shake">
                                <flux:icon name="exclamation-circle" class="w-5 h-5 text-red-600" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-red-700 dark:text-red-300">File Error</p>
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                </div>
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Statistics Cards -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <flux:icon name="chart-bar" class="w-5 h-5 text-red-600" />
                        Statistik PDF
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $pdfStats['total'] }}</p>
                            <p class="text-xs text-slate-500">Total</p>
                        </div>
                        <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                            <p class="text-2xl font-bold text-emerald-600">{{ $pdfStats['uploaded'] }}</p>
                            <p class="text-xs text-emerald-600">Ada PDF</p>
                        </div>
                        <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                            <p class="text-2xl font-bold text-amber-600">{{ $pdfStats['not_uploaded'] }}</p>
                            <p class="text-xs text-amber-600">Belum</p>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <flux:icon name="information-circle" class="w-5 h-5 text-red-600" />
                        Panduan Upload PDF
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex gap-3 text-sm">
                            <span class="flex-none w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-xs font-bold text-red-600">1</span>
                            <p class="text-slate-600 dark:text-slate-400">Nama file <strong>harus sama</strong> dengan Nomor Lab peserta.</p>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <span class="flex-none w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-xs font-bold text-red-600">2</span>
                            <p class="text-slate-600 dark:text-slate-400">Contoh: <code class="bg-slate-200 dark:bg-slate-700 px-1 rounded">12345.pdf</code> untuk No Lab <strong>12345</strong></p>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <span class="flex-none w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-xs font-bold text-red-600">3</span>
                            <p class="text-slate-600 dark:text-slate-400">Data peserta harus sudah ada (di-import dari Excel terlebih dahulu).</p>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <span class="flex-none w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-xs font-bold text-red-600">4</span>
                            <p class="text-slate-600 dark:text-slate-400">WhatsApp hanya bisa dikirim jika PDF sudah di-upload.</p>
                        </div>
                    </div>
                </div>

                <!-- Important Note -->
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-5">
                    <div class="flex gap-3">
                        <flux:icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 flex-none" />
                        <div class="text-sm text-amber-700 dark:text-amber-300">
                            <p class="font-medium mb-1">Penting!</p>
                            <p>File PDF yang tidak cocok dengan Nomor Lab manapun akan ditampilkan sebagai "Tidak Cocok".</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Results Mode -->
        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                        <flux:icon name="document" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total File</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $importResults['total'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                        <flux:icon name="check-circle" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Cocok</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->matchedCount }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-amber-100 dark:border-amber-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                        <flux:icon name="question-mark-circle" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Tidak Cocok</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $this->unmatchedCount }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-red-100 dark:border-red-800 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600">
                        <flux:icon name="x-circle" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Error</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->errorCount }}</p>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Matched Files -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-emerald-200 dark:border-emerald-800 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-900/20">
                        <h3 class="font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                            <flux:icon name="check-circle" class="w-5 h-5" />
                            File Berhasil Cocok ({{ $this->matchedCount }})
                        </h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        @forelse($importResults['matched'] ?? [] as $item)
                            <div class="flex items-center gap-3 p-3 border-b border-slate-100 dark:border-slate-800 last:border-0 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 transition-colors cursor-pointer">
                                <flux:icon name="document-check" class="w-5 h-5 text-emerald-500" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{{ $item['filename'] }}</p>
                                    <p class="text-xs text-slate-500">No Lab: {{ $item['no_lab'] }} &rarr; {{ $item['nama'] }}</p>
                                </div>
                                <span class="text-xs px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 rounded-full">Tersimpan</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-slate-500 text-sm">Tidak ada file yang cocok</div>
                        @endforelse
                    </div>
                </div>

                <!-- Unmatched Files -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-200 dark:border-amber-800 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/20">
                        <h3 class="font-bold text-amber-700 dark:text-amber-300 flex items-center gap-2">
                            <flux:icon name="question-mark-circle" class="w-5 h-5" />
                            File Tidak Cocok ({{ $this->unmatchedCount }})
                        </h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        @forelse($importResults['unmatched'] ?? [] as $item)
                            <div class="flex items-center gap-3 p-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                                <flux:icon name="document-minus" class="w-5 h-5 text-amber-500" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{{ $item['filename'] }}</p>
                                    <p class="text-xs text-amber-600">{{ $item['reason'] }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-slate-500 text-sm">Semua file berhasil dicocokkan</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Errors (if any) -->
            @if(count($importResults['errors'] ?? []) > 0)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-red-200 dark:border-red-800 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-red-200 dark:border-red-800 bg-red-50/50 dark:bg-red-900/20">
                        <h3 class="font-bold text-red-700 dark:text-red-300 flex items-center gap-2">
                            <flux:icon name="x-circle" class="w-5 h-5" />
                            Error ({{ $this->errorCount }})
                        </h3>
                    </div>
                    <div class="max-h-48 overflow-y-auto">
                        @foreach($importResults['errors'] as $error)
                            <div class="flex items-center gap-3 p-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                                <flux:icon name="exclamation-circle" class="w-5 h-5 text-red-500" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">{{ $error['filename'] }}</p>
                                    <p class="text-xs text-red-600">{{ $error['error'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex justify-center gap-4">
                <flux:button variant="subtle" wire:click="resetUpload" icon="arrow-path">Upload Lagi</flux:button>
                <flux:button variant="primary" :href="route('participants.index')" icon="table-cells">Lihat Data Peserta</flux:button>
            </div>
        </div>
    @endif

    <!-- PDF History Table Section -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-800 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-800/30">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="document-text" class="w-5 h-5 text-red-600" />
                        Riwayat Upload PDF
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar peserta dan status PDF mereka</p>
                </div>
                
                <!-- Stats Pills -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-full text-xs font-medium text-slate-600 dark:text-slate-400">
                        <flux:icon name="document" class="w-3.5 h-3.5" />
                        Total: {{ $pdfStats['total'] }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 rounded-full text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        <flux:icon name="check-circle" class="w-3.5 h-3.5" />
                        Ada PDF: {{ $pdfStats['uploaded'] }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 dark:bg-amber-900/30 rounded-full text-xs font-medium text-amber-600 dark:text-amber-400">
                        <flux:icon name="clock" class="w-3.5 h-3.5" />
                        Belum: {{ $pdfStats['not_uploaded'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Per Page -->
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap hidden sm:inline">Tampilkan</span>
                    <select 
                        wire:model.live="perPage" 
                        class="block w-20 rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500 shadow-sm py-2"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap hidden sm:inline">data</span>
                </div>

                <!-- Search -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <flux:icon name="magnifying-glass" class="h-5 w-5 text-slate-400" />
                    </div>
                    <input 
                        type="text"
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari nama, NRP/NIP, No Lab..." 
                        class="block w-full pl-10 pr-10 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 shadow-sm transition-shadow"
                    />
                    @if($search)
                        <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-red-500 transition-colors">
                            <flux:icon name="x-mark" class="h-5 w-5" />
                        </button>
                    @endif
                </div>

                <!-- Filter Status -->
                <div class="flex items-center gap-2">
                    <select 
                        wire:model.live="filterStatus" 
                        class="block w-full sm:w-44 rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:border-red-500 shadow-sm py-2.5"
                    >
                        <option value="">Semua Status</option>
                        <option value="uploaded">Ada PDF</option>
                        <option value="not_uploaded">Belum Ada PDF</option>
                    </select>

                    @if($search || $filterStatus)
                        <flux:button variant="ghost" size="sm" wire:click="resetFilters" class="text-red-600 hover:text-red-700 whitespace-nowrap">
                            <flux:icon name="x-mark" class="w-4 h-4" />
                            <span class="hidden sm:inline">Reset</span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="relative">
            <!-- Mobile scroll hint -->
            <div class="lg:hidden absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white dark:from-slate-900 to-transparent pointer-events-none z-10"></div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr class="text-slate-600 dark:text-slate-400">
                            <th class="px-4 py-3.5 font-semibold text-center w-12">#</th>
                            <th class="px-4 py-3.5 font-semibold">No Lab</th>
                            <th class="px-4 py-3.5 font-semibold">Nama Peserta</th>
                            <th class="px-4 py-3.5 font-semibold hidden md:table-cell">NRP/NIP</th>
                            <th class="px-4 py-3.5 font-semibold hidden lg:table-cell">Satuan Kerja</th>
                            <th class="px-4 py-3.5 font-semibold hidden sm:table-cell">Tgl Periksa</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Status PDF</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($pdfHistory as $peserta)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group" wire:key="pdf-{{ $peserta->nrp_nip }}-{{ $peserta->tanggal_periksa->format('Ymd') }}">
                                <td class="px-4 py-3.5 text-center text-slate-500 font-medium">
                                    {{ $loop->iteration + ($pdfHistory->currentPage() - 1) * $pdfHistory->perPage() }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded">
                                        {{ $peserta->no_lab ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $peserta->nama }}</div>
                                    <!-- Mobile only: Show extra info -->
                                    <div class="md:hidden text-xs text-slate-500 mt-1">
                                        {{ $peserta->nrp_nip }} &bull; {{ $peserta->tanggal_periksa->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400 hidden md:table-cell font-mono text-xs">
                                    {{ $peserta->nrp_nip }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400 hidden lg:table-cell max-w-[200px] truncate" title="{{ $peserta->satuan_kerja }}">
                                    {{ $peserta->satuan_kerja ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400 hidden sm:table-cell whitespace-nowrap">
                                    {{ $peserta->tanggal_periksa->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($peserta->status_pdf === 'uploaded')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-semibold">
                                            <flux:icon name="check-circle" class="w-3.5 h-3.5" />
                                            Ada PDF
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full text-xs font-semibold">
                                            <flux:icon name="clock" class="w-3.5 h-3.5" />
                                            Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                            @if($peserta->status_pdf === 'uploaded' && $peserta->path_pdf)
                                        <!-- Download Button -->
                                        <a 
                                            href="{{ Storage::disk('public')->url($peserta->path_pdf) }}"
                                            download="{{ $peserta->no_lab }}_{{ $peserta->nama }}.pdf"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors cursor-pointer"
                                            title="Download PDF"
                                        >
                                            <flux:icon name="arrow-down-tray" class="w-4 h-4" />
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <flux:icon name="document-magnifying-glass" class="w-12 h-12 mb-3" />
                                        <p class="font-medium text-slate-600 dark:text-slate-300">Tidak ada data ditemukan</p>
                                        <p class="text-sm mt-1">
                                            @if($search || $filterStatus)
                                                Coba ubah filter atau kata kunci pencarian
                                            @else
                                                Belum ada data peserta. Silakan import data Excel terlebih dahulu.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($pdfHistory->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                {{ $pdfHistory->links() }}
            </div>
        @endif
    </div>
</div>
