<div class="space-y-6 sm:space-y-8 animate-fade-in-up">
    <!-- Header Section -->
    <div class="relative">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Dashboard <span class="text-gradient">Overview</span>
                </h1>
                <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 mt-1">
                    Ringkasan aktivitas hari ini, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 w-full lg:w-auto">
                <flux:button variant="ghost" icon="arrow-path" wire:click="$refresh" class="flex-1 lg:flex-none hover:bg-slate-100 dark:hover:bg-slate-800">
                    <span class="hidden sm:inline">Refresh</span>
                </flux:button>
                <flux:button variant="primary" icon="cloud-arrow-up" href="{{ route('uploads.data') }}" class="flex-1 lg:flex-none shadow-lg shadow-brand-500/20 hover:shadow-xl hover:shadow-brand-500/30 transition-all">
                    <span class="hidden sm:inline">Import Baru</span>
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
        <!-- Total Participants -->
        <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-500/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="glass-panel rounded-2xl p-5 sm:p-6 hover-lift">
                <div class="flex justify-between items-start mb-4">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-br from-brand-500 to-brand-600 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <div class="relative p-2.5 sm:p-3 bg-gradient-to-br from-brand-50 to-brand-100 dark:from-brand-900/30 dark:to-brand-800/30 rounded-xl text-brand-600 dark:text-brand-400 transition-transform group-hover:scale-110 duration-300">
                            <flux:icon name="users" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </div>
                    </div>
                    <span class="flex items-center text-[10px] sm:text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400 px-2 sm:px-2.5 py-1 rounded-full">
                        <flux:icon name="arrow-trending-up" class="w-3 h-3 mr-1" />
                        {{ count($recentPeserta ?? []) }}
                    </span>
                </div>
                <div class="relative">
                    <h3 class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Peserta</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
                        {{ number_format($totalPeserta ?? 0) }}
                    </p>
                </div>
                <div class="absolute -bottom-2 -right-2 w-16 h-16 opacity-5 group-hover:scale-125 group-hover:opacity-10 transition-all duration-500">
                    <flux:icon name="users" class="w-full h-full" />
                </div>
            </div>
        </div>

        <!-- WA Sent -->
        <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="glass-panel rounded-2xl p-5 sm:p-6 hover-lift">
                <div class="flex justify-between items-start mb-4">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <div class="relative p-2.5 sm:p-3 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-xl text-purple-600 dark:text-purple-400 transition-transform group-hover:scale-110 duration-300">
                            <flux:icon name="paper-airplane" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </div>
                    </div>
                    <span class="flex items-center text-[10px] sm:text-xs font-semibold text-slate-600 bg-slate-100 dark:bg-slate-700 dark:text-slate-300 px-2 sm:px-2.5 py-1 rounded-full">
                        {{ $totalPeserta > 0 ? number_format(($sentWa / $totalPeserta) * 100, 1) : 0 }}% terkirim
                    </span>
                </div>
                <div class="relative">
                    <h3 class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Pesan Terkirim</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
                        {{ number_format($this->sentWa) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- PDF Status -->
        <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-teal-500/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="glass-panel rounded-2xl p-5 sm:p-6 hover-lift">
                <div class="flex justify-between items-start mb-4">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <div class="relative p-2.5 sm:p-3 bg-gradient-to-br from-teal-50 to-cyan-100 dark:from-teal-900/30 dark:to-cyan-800/30 rounded-xl text-teal-600 dark:text-teal-400 transition-transform group-hover:scale-110 duration-300">
                            <flux:icon name="document" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </div>
                    </div>
                    <span class="flex items-center text-[10px] sm:text-xs font-semibold text-teal-600 bg-teal-50 dark:bg-teal-900/20 dark:text-teal-400 px-2 sm:px-2.5 py-1 rounded-full">
                        PDF
                    </span>
                </div>
                <div class="relative">
                    <h3 class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Ada PDF</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
                        {{ number_format($hasPdf ?? 0) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Failed -->
        <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/20 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="glass-panel rounded-2xl p-5 sm:p-6 hover-lift">
                <div class="flex justify-between items-start mb-4">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-br from-red-500 to-red-600 rounded-xl blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <div class="relative p-2.5 sm:p-3 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 rounded-xl text-red-600 dark:text-red-400 transition-transform group-hover:scale-110 duration-300">
                            <flux:icon name="exclamation-triangle" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </div>
                    </div>
                    <span class="flex items-center text-[10px] sm:text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 px-2 sm:px-2.5 py-1 rounded-full">
                        Perlu Cek
                    </span>
                </div>
                <div class="relative">
                    <h3 class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Belum Kirim</h3>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">
                        {{ number_format($this->waStats['belum_kirim']) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 sm:gap-8">

        <!-- Left Column: Recent Activity -->
        <div class="xl:col-span-2">
            <div class="glass-panel rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white">Aktivitas Terbaru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Data peserta yang baru saja ditambahkan</p>
                    </div>
                    <a href="{{ route('participants.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 transition-colors whitespace-nowrap">
                        Lihat Semua
                        <flux:icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 font-medium text-xs uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 sm:py-4">Peserta</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4">Status</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 hidden md:table-cell">Tanggal</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($recentPeserta ?? [] as $peserta)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-brand-100 to-brand-200 dark:from-brand-900/40 dark:to-brand-800/40 flex items-center justify-center text-brand-600 dark:text-brand-400 font-bold text-xs border border-brand-200 dark:border-brand-800">
                                                {{ strtoupper(substr($peserta->nama, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $peserta->nama }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400 truncate md:hidden">{{ $peserta->no_hp_wa }}</div>
                                                <div class="text-xs text-slate-500 hidden md:block">{{ $peserta->no_hp_wa }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                                        @if($peserta->status_wa === 'sent')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Terkirim
                                            </span>
                                        @elseif($peserta->status_wa === 'failed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Gagal
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> {{ ucfirst($peserta->status_wa) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs text-slate-500 dark:text-slate-400 hidden md:table-cell">
                                        {{ $peserta->created_at->locale('id')->diffForHumans() }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-right">
                                        <flux:button variant="ghost" size="sm" icon="eye" class="opacity-0 group-hover:opacity-100 transition-opacity hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 dark:hover:text-brand-400" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 sm:px-6 py-8 sm:py-12 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                                <flux:icon name="users" class="w-6 h-6 opacity-40" />
                                            </div>
                                            <span class="text-sm">Belum ada aktivitas terbaru</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: WA Statistics Summary -->
        <div class="space-y-6">

            <!-- WA Delivery Summary -->
            <div class="glass-panel rounded-2xl p-5 sm:p-6">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white mb-4">Ringkasan Pengiriman WA</h3>
                <div class="space-y-4">
                    <!-- Success -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-800/30 rounded-lg">
                                <flux:icon name="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Berhasil Terkirim</span>
                        </div>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($waStats['success']) }}</span>
                    </div>

                    <!-- Belum Kirim -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-slate-100 dark:bg-slate-700/30 rounded-lg">
                                <flux:icon name="clock" class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                            </div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum Kirim</span>
                        </div>
                        <span class="text-lg font-bold text-slate-600 dark:text-slate-400">{{ number_format($waStats['belum_kirim']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Last Import Info -->
            @if($lastUpload)
            <div class="glass-panel rounded-2xl p-5 sm:p-6">
                <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white mb-4">Import Terakhir</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                        <div class="p-2 bg-brand-100 dark:bg-brand-800/30 rounded-lg">
                            <flux:icon name="document-text" class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $lastUpload->nama_file_asli ?? '-' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $lastUpload->created_at->locale('id')->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8"
         x-data="{
            chartPaket: null,
            chartTahun: null,
            paketData: @js($chartPaketTahunan),
            tahunData: @js($chartPerTahun),
            chartReady: false,
            initialized: false,

            init() {
                if (this.initialized) return;
                this.initialized = true;

                this.waitForChart().then(() => {
                    this.chartReady = true;
                    this.$nextTick(() => {
                        this.createPaketChart();
                        this.createTahunChart();
                    });
                });
            },

            waitForChart() {
                return new Promise((resolve) => {
                    if (typeof Chart !== 'undefined') {
                        resolve();
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = '/js/chart.min.js';
                    script.onload = () => resolve();
                    document.head.appendChild(script);
                });
            },

            updateCharts(detail) {
                if (!this.chartReady) return;

                // Store new data
                if (detail.paketData) {
                    this.paketData = detail.paketData;
                }
                if (detail.tahunData) {
                    this.tahunData = detail.tahunData;
                }

                // Recreate charts instead of updating to avoid Chart.js state corruption
                this.$nextTick(() => {
                    this.createPaketChart();
                    this.createTahunChart();
                });
            },

            updateLegend(data) {
                if (!this.$refs.legendPaket || !data.labels) return;
                const labels = data.labels || [];
                const bgColors = data.backgroundColor || [];
                this.$refs.legendPaket.innerHTML = labels.map((label, i) => `
                    <div class='flex items-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700'>
                        <div class='w-3 h-3 rounded-sm' style='background-color: ${bgColors[i] || '#ccc'}'></div>
                        <span class='text-xs font-medium text-slate-700 dark:text-slate-300'>${label}</span>
                    </div>
                `).join('');
            },

            createPaketChart() {
                const canvas = this.$refs.chartPaket;
                if (!canvas) return;
                if (this.chartPaket) {
                    this.chartPaket.destroy();
                    this.chartPaket = null;
                }

                const ctx = canvas.getContext('2d');
                const isDark = document.documentElement.classList.contains('dark');

                this.chartPaket = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.paketData.labels || [],
                        datasets: [{
                            data: this.paketData.data || [],
                            backgroundColor: this.paketData.backgroundColor || [],
                            borderColor: this.paketData.borderColor || [],
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 80,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                titleColor: isDark ? '#f1f5f9' : '#1e293b',
                                bodyColor: isDark ? '#cbd5e1' : '#475569',
                                borderColor: isDark ? 'rgba(100, 116, 139, 0.3)' : 'rgba(203, 213, 225, 0.8)',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: (context) => ` ${context.parsed.y.toLocaleString()} peserta`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11, weight: '500' } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: isDark ? 'rgba(100, 116, 139, 0.2)' : 'rgba(203, 213, 225, 0.5)', drawBorder: false },
                                ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 }, callback: (value) => value.toLocaleString() }
                            }
                        },
                        animation: { duration: 500 }
                    }
                });

                this.updateLegend(this.paketData);
            },

            createTahunChart() {
                const canvas = this.$refs.chartTahun;
                if (!canvas) return;
                if (this.chartTahun) {
                    this.chartTahun.destroy();
                    this.chartTahun = null;
                }

                const ctx = canvas.getContext('2d');
                const isDark = document.documentElement.classList.contains('dark');

                this.chartTahun = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.tahunData.labels || [],
                        datasets: [{
                            data: this.tahunData.data || [],
                            backgroundColor: this.tahunData.backgroundColor || [],
                            borderColor: this.tahunData.borderColor || [],
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 80,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                titleColor: isDark ? '#f1f5f9' : '#1e293b',
                                bodyColor: isDark ? '#cbd5e1' : '#475569',
                                borderColor: isDark ? 'rgba(100, 116, 139, 0.3)' : 'rgba(203, 213, 225, 0.8)',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    title: (context) => `Tahun ${context[0].label}`,
                                    label: (context) => ` ${context.parsed.y.toLocaleString()} peserta`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 12, weight: '600' } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: isDark ? 'rgba(100, 116, 139, 0.2)' : 'rgba(203, 213, 225, 0.5)', drawBorder: false },
                                ticks: { color: isDark ? '#94a3b8' : '#64748b', font: { size: 11 }, callback: (value) => value.toLocaleString() }
                            }
                        },
                        animation: { duration: 500 }
                    }
                });
            }
         }"
         @chart-data-updated.window="updateCharts($event.detail)">

        <!-- Laporan Per Paket Tahunan -->
        <div class="glass-panel rounded-2xl overflow-hidden group">
            <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25">
                            <flux:icon name="chart-bar" class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white">Laporan Per Paket Tahunan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Distribusi peserta berdasarkan paket</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <select wire:model.live="selectedYear"
                                class="text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 font-medium text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div class="chart-container relative" style="height: 280px;">
                    <!-- Loading indicator -->
                    <div x-show="!chartReady" x-cloak class="absolute inset-0 flex items-center justify-center bg-white dark:bg-slate-900 z-10">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin"></div>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Memuat grafik...</span>
                        </div>
                    </div>
                    <canvas x-ref="chartPaket"></canvas>
                </div>
                <!-- Legend -->
                <div class="mt-4 flex flex-wrap justify-center gap-3" x-ref="legendPaket"></div>
            </div>
        </div>

        <!-- Laporan Per Tahun -->
        <div class="glass-panel rounded-2xl overflow-hidden group">
            <div class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl shadow-lg shadow-teal-500/25">
                        <flux:icon name="chart-bar" class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white">Laporan Per Tahun</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total peserta per tahun</p>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div class="chart-container relative" style="height: 280px;">
                    <!-- Loading indicator -->
                    <div x-show="!chartReady" x-cloak class="absolute inset-0 flex items-center justify-center bg-white dark:bg-slate-900 z-10">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-8 h-8 border-4 border-teal-200 border-t-teal-500 rounded-full animate-spin"></div>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Memuat grafik...</span>
                        </div>
                    </div>
                    <canvas x-ref="chartTahun"></canvas>
                </div>
                <!-- Total Summary -->
                <div class="mt-4 flex justify-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-teal-50 dark:bg-teal-900/20 rounded-xl border border-teal-200 dark:border-teal-800">
                        <flux:icon name="users" class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                        <span class="text-sm font-medium text-teal-700 dark:text-teal-300">Total Keseluruhan:</span>
                        <span class="text-lg font-bold text-teal-600 dark:text-teal-400">{{ number_format($totalPeserta) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>