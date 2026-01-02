<div>
    <div class="space-y-6 animate-fade-in-up">
        <!-- Enhanced Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                        <flux:icon name="cube" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                    </div>
                    Pengaturan Paket
                </h1>
                <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Kelola paket pemeriksaan kesehatan</p>
            </div>
            <a href="{{ route('settings.paket.create') }}" wire:navigate>
                <flux:button variant="primary" icon="plus" class="shadow-xl shadow-brand-500/25 hover:shadow-xl hover:shadow-brand-500/40 transition-all">
                    <span class="hidden sm:inline">Tambah Paket</span>
                    <span class="sm:hidden">Tambah</span>
                </flux:button>
            </a>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 rounded-2xl border border-cyan-100 dark:border-cyan-800/30 p-5 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-cyan-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <p class="text-xs font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">Total Paket</p>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $paketList->total() }}</p>
                    <div class="flex items-center gap-1 mt-2 text-xs text-cyan-600 dark:text-cyan-400">
                        <flux:icon name="cube" class="w-3.5 h-3.5" />
                        <span>Paket terdaftar</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-slate-50 to-zinc-50 dark:from-slate-800/50 dark:to-zinc-800/50 rounded-2xl border border-slate-200 dark:border-slate-700/50 p-5 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-400/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total Peserta</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalPeserta) }}</p>
                    </div>
                    <div class="flex items-center gap-1 mt-2 text-xs text-slate-600 dark:text-slate-400">
                        <flux:icon name="users" class="w-3.5 h-3.5" />
                        <span>Terdaftar di paket</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Table Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden">
            <!-- Table Header -->
            <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-cyan-50 dark:bg-cyan-900/30 rounded-xl">
                        <flux:icon name="list-bullet" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white">Daftar Paket</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Semua paket pemeriksaan yang terdaftar</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative hidden sm:block">
                        <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="search"
                            placeholder="Cari kode atau nama..."
                            class="w-64 pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border-0 rounded-xl focus:ring-2 focus:ring-cyan-500/50 transition-all"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-sm border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 cursor-pointer hover:text-cyan-600 transition-colors" wire:click="sortBy('kode')">
                                <div class="flex items-center gap-2">
                                    Kode Paket
                                    <flux:icon name="chevron-up-down" class="w-3 h-3" />
                                </div>
                            </th>
                            <th class="px-6 py-4 cursor-pointer hover:text-cyan-600 transition-colors" wire:click="sortBy('nama')">
                                <div class="flex items-center gap-2">
                                    Nama Paket
                                    <flux:icon name="chevron-up-down" class="w-3 h-3" />
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($paketList as $paket)
                            <tr wire:key="paket-{{ $paket->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-200 group">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-xs">{{ $paket->kode }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    {{ $paket->nama }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('settings.paket.edit', $paket) }}" wire:navigate>
                                            <flux:button variant="ghost" size="sm" icon="pencil-square" class="hover:bg-cyan-50 hover:text-cyan-600 dark:hover:bg-cyan-900/20 dark:hover:text-cyan-400" />
                                        </a>
                                        <button type="button" wire:click="confirmDelete({{ $paket->id }})" class="p-2 text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 rounded-md transition-colors cursor-pointer relative z-10">
                                            <flux:icon name="trash" class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-4 ring-4 ring-slate-100 dark:ring-slate-700/50">
                                            <flux:icon name="cube" class="w-8 h-8 text-slate-400" />
                                        </div>
                                        <h3 class="font-bold text-slate-900 dark:text-white mb-1">Tidak ada data paket</h3>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Mulai dengan menambahkan paket baru</p>
                                        <a href="{{ route('settings.paket.create') }}" wire:navigate>
                                            <flux:button variant="primary" icon="plus" size="sm">
                                                Tambah Paket Pertama
                                            </flux:button>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($paketList->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                     <div class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan <span class="font-bold text-slate-900 dark:text-white">{{ $paketList->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-900 dark:text-white">{{ $paketList->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-900 dark:text-white">{{ $paketList->total() }}</span> paket
                    </div>
                    <div class="flex-1 sm:flex-none">
                         {{ $paketList->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal (Manual Implementation) -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center px-4 sm:px-0"
             x-data
             x-on:keydown.escape.window="$wire.cancelDelete()">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
                 wire:click="cancelDelete"></div>

            <!-- Modal Panel -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-2xl transform transition-all w-full max-w-lg p-6 relative z-10 animate-fade-in-up border border-slate-200 dark:border-slate-800">
                
                <!-- Close Button -->
                <button type="button" wire:click="cancelDelete" class="absolute top-4 right-4 text-slate-400 hover:text-slate-500 dark:hover:text-slate-300 transition-colors">
                    <flux:icon name="x-mark" class="w-5 h-5" />
                </button>

                <div class="space-y-6">
                    <div class="text-center sm:text-left">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10 mb-4 sm:mb-0 sm:inline-flex sm:mr-3">
                            <flux:icon name="exclamation-triangle" class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <h3 class="text-lg font-medium leading-6 text-slate-900 dark:text-white inline-block">
                            Hapus Paket?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Anda akan menghapus paket: <span class="font-bold text-slate-900 dark:text-white">"{{ $deletingPaketNama }}"</span>
                            </p>
                        </div>
                    </div>

                    <!-- Warning Box -->
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800/30">
                        <div class="flex gap-3">
                            <div class="text-sm text-red-700 dark:text-red-300">
                                <p class="font-semibold">Perhatian!</p>
                                <p class="mt-1">Tindakan ini tidak dapat dibatalkan. Data akan dihapus secara permanen.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" 
                                wire:click="deleteConfirmed" 
                                class="inline-flex w-full justify-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Ya, Hapus
                        </button>
                        <button type="button" 
                                wire:click="cancelDelete" 
                                class="mt-3 inline-flex w-full justify-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-base font-medium text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
