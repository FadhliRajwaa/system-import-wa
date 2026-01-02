<div class="space-y-6 animate-fade-in-up">
    <!-- Enhanced Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <flux:icon name="building-office" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                Pengaturan Instansi
            </h1>
            <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Kelola instansi dan template pesan WhatsApp</p>
        </div>
        <flux:button variant="primary" icon="plus" href="{{ route('settings.instansi.create') }}" class="shadow-xl shadow-brand-500/25 hover:shadow-xl hover:shadow-brand-500/40 transition-all">
            <span class="hidden sm:inline">Tambah Perusahaan</span>
            <span class="sm:hidden">Tambah</span>
        </flux:button>
    </div>

    <!-- Enhanced Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden">
        <!-- Table Header -->
        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl">
                    <flux:icon name="list-bullet" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Daftar Instansi</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Semua instansi / perusahaan yang terdaftar</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative hidden sm:block">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="Cari kode atau nama..."
                        class="w-64 pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border-0 rounded-xl focus:ring-2 focus:ring-indigo-500/50 transition-all"
                    />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-sm border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 cursor-pointer hover:text-indigo-600 transition-colors" wire:click="sortBy('kode')">
                            <div class="flex items-center gap-2">
                                Kode Perusahaan
                                <flux:icon name="chevron-up-down" class="w-3 h-3" />
                            </div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:text-indigo-600 transition-colors" wire:click="sortBy('nama')">
                            <div class="flex items-center gap-2">
                                Nama Perusahaan
                                <flux:icon name="chevron-up-down" class="w-3 h-3" />
                            </div>
                        </th>
                        <th class="px-6 py-4">Template Pesan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($instansiList as $instansi)
                        <tr wire:key="instansi-{{ $instansi->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-200 group">
                            <td class="px-6 py-4">
                                <span class="font-mono font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-xs">{{ $instansi->kode }}</span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ $instansi->nama }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-md">
                                <div class="truncate" title="{{ $instansi->template_prolog }}">
                                    {{ Str::limit($instansi->template_prolog, 80) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" href="{{ route('settings.instansi.edit', $instansi->id) }}" class="hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-400" />
                                    <flux:button variant="ghost" size="sm" icon="trash" class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" wire:click="delete({{ $instansi->id }})" wire:confirm="Apakah Anda yakin ingin menghapus instansi ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-4 ring-4 ring-slate-100 dark:ring-slate-700/50">
                                        <flux:icon name="building-office" class="w-8 h-8 text-slate-400" />
                                    </div>
                                    <h3 class="font-bold text-slate-900 dark:text-white mb-1">Tidak ada data instansi</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Mulai dengan menambahkan instansi baru</p>
                                    <flux:button variant="primary" icon="plus" href="{{ route('settings.instansi.create') }}" size="sm">
                                        Tambah Instansi Pertama
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($instansiList->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                 <div class="text-sm text-slate-500 dark:text-slate-400">
                    Menampilkan <span class="font-bold text-slate-900 dark:text-white">{{ $instansiList->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-900 dark:text-white">{{ $instansiList->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-900 dark:text-white">{{ $instansiList->total() }}</span> instansi
                </div>
                <div class="flex-1 sm:flex-none">
                     {{ $instansiList->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
