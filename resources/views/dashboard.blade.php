<x-layouts.app title="Dashboard">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard Overview</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Ringkasan aktivitas sistem dan data terbaru hari ini, {{ date('d M Y') }}.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                    <flux:icon name="arrow-path" class="w-4 h-4" />
                    <span>Refresh Data</span>
                </button>
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-brand-500/20">
                    <flux:icon name="plus" class="w-4 h-4" />
                    <span>Import Baru</span>
                </button>
            <button class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-brand-500/20">
                <flux:icon name="plus" class="w-4 h-4" />
                <span>Import Baru</span>
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Card 1 -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200/50 dark:border-slate-700 relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 bg-brand-50 dark:bg-brand-900/20 rounded-xl text-brand-600 dark:text-brand-400 group-hover:scale-110 transition-transform">
                            <flux:icon name="users" class="size-6" />
                        </div>
                        <span class="flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                            +12%
                        </span>
                    </div>
                <span class="flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    +12%
                </span>
            </div>
            <div class="relative z-10">
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Peserta</h3>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">2,845</p>
            </div>
            <!-- Decorative bg -->
            <div class="absolute right-0 bottom-0 opacity-5 transform translate-y-2 translate-x-2">
                <flux:icon name="users" class="w-24 h-24" />
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200/50 dark:border-slate-700 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
                    <flux:icon name="paper-airplane" class="size-6" />
                </div>
                <span class="flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    +5.2%
                </span>
            </div>
                <span class="flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    +5.2%
                </span>
            </div>
            <div class="relative z-10">
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Pesan Terkirim</h3>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">1,240</p>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200/50 dark:border-slate-700 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-xl text-orange-600 dark:text-orange-400 group-hover:scale-110 transition-transform">
                    <flux:icon name="clock" class="size-6" />
                </div>
                <span class="flex items-center text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
                    Pending
                </span>
            </div>
                <span class="flex items-center text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
                    Pending
                </span>
            </div>
            <div class="relative z-10">
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Antrian Pesan</h3>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">45</p>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200/50 dark:border-slate-700 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl text-red-600 dark:text-red-400 group-hover:scale-110 transition-transform">
                    <flux:icon name="exclamation-triangle" class="size-6" />
                </div>
                <span class="flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">
                    3 Gagal
                </span>
            </div>
                <span class="flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">
                    3 Gagal
                </span>
            </div>
            <div class="relative z-10">
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">Gagal Kirim</h3>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">12</p>
            </div>
        </div>
    </div>

    <!-- Main Grid: Activity & Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Recent Activity Table -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="font-bold text-slate-900 dark:text-white">Aktivitas Terbaru</h3>
                    <a href="#" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">Lihat Semua</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 font-medium">
                            <tr>
                                <th class="px-6 py-4">Peserta</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <!-- Row 1 -->
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 font-bold text-xs">
                                            AD
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">Andi Darmawan</div>
                                            <div class="text-xs text-slate-500">+62 812 3456 7890</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Terkirim
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    2 menit yang lalu
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-400 hover:text-brand-600 transition-colors">
                                        <flux:icon name="ellipsis-horizontal" class="w-5 h-5" />
                                    </button>
                                </td>

                            <!-- Row 3 -->
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 font-bold text-xs">
                                            SA
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">Siti Aminah</div>
                                            <div class="text-xs text-slate-500">+62 856 7890 1234</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        Menunggu
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    5 menit yang lalu
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-400 hover:text-brand-600 transition-colors">
                                        <flux:icon name="ellipsis-horizontal" class="w-5 h-5" />
                                    </button>
                                </td>

                            <!-- Row 2 -->
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 font-bold text-xs">
                                            SA
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">Siti Aminah</div>
                                            <div class="text-xs text-slate-500">+62 856 7890 1234</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        Menunggu
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    5 menit yang lalu
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-400 hover:text-brand-600 transition-colors">
                                        <flux:icon name="ellipsis-horizontal" class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 font-bold text-xs">
                                            BU
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">Budi Utama</div>
                                            <div class="text-xs text-slate-500">+62 811 2233 4455</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Terkirim
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    12 menit yang lalu
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-400 hover:text-brand-600 transition-colors">
                                        <flux:icon name="ellipsis-horizontal" class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 font-bold text-xs">
                                            BU
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">Budi Utama</div>
                                            <div class="text-xs text-slate-500">+62 811 2233 4455</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Terkirim
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    12 menit yang lalu
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-400 hover:text-brand-600 transition-colors">
                                        <flux:icon name="ellipsis-horizontal" class="w-5 h-5" />
                                    </button>
                                </td>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: System Status / Quick Actions -->
        <div class="space-y-6">
            
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700 p-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    <button class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 transition-colors border border-transparent hover:border-brand-200 dark:hover:border-brand-800/50 group">
                        <flux:icon name="cloud-arrow-up" class="w-6 h-6 text-slate-500 group-hover:text-brand-600 dark:text-slate-400 dark:group-hover:text-brand-400" />
                        <span class="text-xs font-medium">Upload Excel</span>
                    </button>
                    <button class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 transition-colors border border-transparent hover:border-brand-200 dark:hover:border-brand-800/50 group">
                        <flux:icon name="plus-circle" class="w-6 h-6 text-slate-500 group-hover:text-brand-600 dark:text-slate-400 dark:group-hover:text-brand-400" />
                        <span class="text-xs font-medium">Tambah Peserta</span>
                    </button>
                    <button class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 transition-colors border border-transparent hover:border-brand-200 dark:hover:border-brand-800/50 group">
                        <flux:icon name="cog" class="w-6 h-6 text-slate-500 group-hover:text-brand-600 dark:text-slate-400 dark:group-hover:text-brand-400" />
                        <span class="text-xs font-medium">Pengaturan</span>
                    </button>
                    <button class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 transition-colors border border-transparent hover:border-brand-200 dark:hover:border-brand-800/50 group">
                        <flux:icon name="document-text" class="w-6 h-6 text-slate-500 group-hover:text-brand-600 dark:text-slate-400 dark:group-hover:text-brand-400" />
                        <span class="text-xs font-medium">Laporan</span>
                    </button>
                    <button class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 transition-colors border border-transparent hover:border-brand-200 dark:hover:border-brand-800/50 group">
                        <flux:icon name="plus-circle" class="w-6 h-6 text-slate-500 group-hover:text-brand-600 dark:text-slate-400 dark:group-hover:text-brand-400" />
                        <span class="text-xs font-medium">Tambah Peserta</span>
                    </button>
                    <button class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 transition-colors border border-transparent hover:border-brand-200 dark:hover:border-brand-800/50 group">
                        <flux:icon name="cog" class="w-6 h-6 text-slate-500 group-hover:text-brand-600 dark:text-slate-400 dark:group-hover:text-brand-400" />
                        <span class="text-xs font-medium">Pengaturan</span>
                    </button>
                    <button class="flex flex-col items-center justify-center gap-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400 transition-colors border border-transparent hover:border-brand-200 dark:hover:border-brand-800/50 group">
                        <flux:icon name="document-text" class="w-6 h-6 text-slate-500 group-hover:text-brand-600 dark:text-slate-400 dark:group-hover:text-brand-400" />
                        <span class="text-xs font-medium">Laporan</span>
                    </button>
                </div>
            </div>

            <!-- Server Status -->
            <div class="bg-gradient-to-br from-indigo-600 to-brand-700 rounded-2xl shadow-lg shadow-indigo-500/20 p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-white/90">Status Server</h3>
                        <span class="flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-xs text-indigo-100 mb-1">
                                <span>CPU Usage</span>
                                <span>24%</span>
                            </div>
                            <div class="w-full bg-black/20 rounded-full h-1.5">
                                <div class="bg-white/80 h-1.5 rounded-full" style="width: 24%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs text-indigo-100 mb-1">
                                <span>Memory</span>
                                <span>58%</span>
                            </div>
                            <div class="w-full bg-black/20 rounded-full h-1.5">
                                <div class="bg-white/80 h-1.5 rounded-full" style="width: 58%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center gap-2 text-xs text-indigo-200">
                        <flux:icon name="check-circle" class="w-4 h-4" />
                        <span>Semua layanan berjalan normal</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>