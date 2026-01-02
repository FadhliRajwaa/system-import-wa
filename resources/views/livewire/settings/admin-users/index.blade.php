<div class="space-y-6 animate-fade-in-up">
    <!-- Enhanced Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-brand-500/30">
                    <flux:icon name="user-group" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                Admin Sistem
            </h1>
            <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Kelola akses dan izin pengguna administrator</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create" class="shadow-xl shadow-brand-500/25 hover:shadow-xl hover:shadow-brand-500/40 transition-all">
            <span class="hidden sm:inline">Tambah Admin</span>
            <span class="sm:hidden">Tambah</span>
        </flux:button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-brand-50 to-indigo-50 dark:from-brand-900/20 dark:to-indigo-900/20 rounded-2xl border border-brand-100 dark:border-brand-800/30 p-5 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-brand-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <p class="text-xs font-bold text-brand-600 dark:text-brand-400 uppercase tracking-wider">Total Admin</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $this->users->total() }}</p>
                <div class="flex items-center gap-1 mt-2 text-xs text-brand-600 dark:text-brand-400">
                    <flux:icon name="users" class="w-3.5 h-3.5" />
                    <span>Pengguna aktif</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30 p-5 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Status Aktif</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $this->users->where('is_active', true)->count() }}</p>
                <div class="flex items-center gap-1 mt-2 text-xs text-emerald-600 dark:text-emerald-400">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Online</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-100 dark:border-amber-800/30 p-5 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-amber-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <p class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Status Nonaktif</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $this->users->where('is_active', false)->count() }}</p>
                <div class="flex items-center gap-1 mt-2 text-xs text-amber-600 dark:text-amber-400">
                    <flux:icon name="clock" class="w-3.5 h-3.5" />
                    <span>Menunggu</span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-slate-50 to-zinc-50 dark:from-slate-800/50 dark:to-zinc-800/50 rounded-2xl border border-slate-200 dark:border-slate-700/50 p-5 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-400/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <p class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Per Page</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $this->users->perPage() }}</p>
                <div class="flex items-center gap-1 mt-2 text-xs text-slate-600 dark:text-slate-400">
                    <flux:icon name="document-text" class="w-3.5 h-3.5" />
                    <span>Items</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden">
        <!-- Table Header -->
        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-brand-50 dark:bg-brand-900/30 rounded-xl">
                    <flux:icon name="users" class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Daftar Admin</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelola semua administrator</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative hidden sm:block">
                    <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        type="search"
                        placeholder="Cari admin..."
                        class="w-64 pl-10 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border-0 rounded-xl focus:ring-2 focus:ring-brand-500/50 transition-all"
                    />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-sm border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 cursor-pointer hover:text-brand-600 transition-colors" wire:click="sortBy('name')">
                            <div class="flex items-center gap-2">
                                Nama Admin
                                <flux:icon name="chevron-up-down" class="w-3 h-3" />
                            </div>
                        </th>
                        <th class="px-6 py-4 cursor-pointer hover:text-brand-600 transition-colors hidden md:table-cell" wire:click="sortBy('email')">
                            <div class="flex items-center gap-2">
                                Email
                                <flux:icon name="chevron-up-down" class="w-3 h-3" />
                            </div>
                        </th>
                        <th class="px-6 py-4 hidden lg:table-cell">Terdaftar</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-200 group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-md shadow-brand-500/20 ring-2 ring-white dark:ring-slate-900">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-slate-900"></div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 truncate md:hidden">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <span class="inline-flex items-center gap-1.5 text-xs font-mono text-slate-500 dark:text-slate-400">
                                    <flux:icon name="calendar" class="w-3.5 h-3.5" />
                                    {{ $user->created_at->format('d M Y') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $user->id }})" class="hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 dark:hover:text-brand-400" />
                                    @if(auth()->id() !== $user->id)
                                        <flux:button variant="ghost" size="sm" icon="trash" class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" wire:click="delete({{ $user->id }})" wire:confirm="Apakah Anda yakin ingin menghapus admin ini?" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mb-4 ring-4 ring-slate-100 dark:ring-slate-700/50">
                                        <flux:icon name="user-group" class="w-8 h-8 text-slate-400" />
                                    </div>
                                    <h3 class="font-bold text-slate-900 dark:text-white mb-1">Tidak ada data admin</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Mulai dengan menambahkan admin baru</p>
                                    <flux:button variant="primary" icon="plus" wire:click="create" size="sm">
                                        Tambah Admin Pertama
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Enhanced Pagination -->
        <div class="px-4 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-900 dark:text-white">{{ $this->users->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-900 dark:text-white">{{ $this->users->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-900 dark:text-white">{{ $this->users->total() }}</span> admin
            </div>
            <div class="flex-1 sm:flex-none">
                {{ $this->users->links() }}
            </div>
        </div>
    </div>

    <!-- Enhanced Modal Form -->
    <flux:modal wire:model="showModal" class="md:min-w-[500px]">
        <div class="space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-brand-500/30">
                    <flux:icon name="{{ $editing ? 'pencil-square' : 'plus' }}" class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ $editing ? 'Edit Admin' : 'Tambah Admin Baru' }}
                    </h2>
                    <p class="text-sm text-slate-500">{{ $editing ? 'Update informasi pengguna' : 'Lengkapi data admin baru' }}</p>
                </div>
            </div>

            <div class="space-y-5">
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Nama Lengkap</flux:label>
                        <flux:input wire:model="name" placeholder="Contoh: Ahmad Fadli" icon="user" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email Address</flux:label>
                        <flux:input type="email" wire:model="email" placeholder="admin@contoh.com" icon="envelope" />
                        <flux:error name="email" />
                    </flux:field>
                </div>

                <div class="p-5 bg-gradient-to-br from-slate-50 to-zinc-50 dark:from-slate-900/50 dark:to-zinc-900/50 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon name="shield-check" class="w-4 h-4 text-brand-600 dark:text-brand-400" />
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Keamanan</h4>
                    </div>

                    <flux:field>
                        <flux:label>Password {{ $editing ? '(Biarkan kosong untuk tidak diubah)' : '' }}</flux:label>
                        <flux:input type="password" wire:model="password" icon="key" />
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Konfirmasi Password</flux:label>
                        <flux:input type="password" wire:model="password_confirmation" icon="key" />
                    </flux:field>
                </div>

                @if(auth()->id() !== $userId)
                    <div class="flex items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                                <flux:icon name="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Akun Aktif</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Izinkan admin login ke dashboard</p>
                            </div>
                        </div>
                        <flux:switch wire:model="isActive" />
                    </div>
                @endif
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                <flux:button variant="ghost" wire:click="cancel">Batal</flux:button>
                <flux:button variant="primary" wire:click="save" icon="check" class="shadow-xl shadow-brand-500/25">
                    {{ $editing ? 'Update Admin' : 'Buat Admin' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>