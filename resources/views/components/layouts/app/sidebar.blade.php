<div class="flex flex-col h-full bg-gradient-to-b from-white to-slate-50 dark:from-slate-900 dark:to-slate-950 border-r border-slate-200/50 dark:border-slate-800/50">
    <!-- Logo Section -->
    <div class="h-16 sm:h-20 flex items-center px-4 sm:px-6 border-b border-slate-100 dark:border-slate-800/50 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-brand-500/5 via-transparent to-transparent dark:from-brand-500/10"></div>
        <a href="{{ route('dashboard') }}" class="relative flex items-center gap-3 group w-full">
            <div class="relative flex-shrink-0">
                <img src="{{ asset('Logo-Jabar.png') }}" alt="Polda Jabar" class="w-10 h-10 sm:w-12 sm:h-12 object-contain" />
            </div>
            <div class="flex flex-col flex-1 min-w-0">
                <span class="font-bold text-base sm:text-lg text-slate-900 dark:text-white leading-none tracking-tight group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">RIKKES<span class="text-gradient"> BERKALA</span></span>
                <span class="text-[10px] sm:text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-0.5">Polda Jawa Barat</span>
            </div>
        </a>
    </div>

    <!-- Scrollable Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 sm:py-6 space-y-6 sm:space-y-8 scrollbar-thin">
        <!-- Menu Utama -->
        <div class="space-y-1">
            <p class="px-3 text-[10px] sm:text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500/50"></span>
                Menu Utama
            </p>

            <a href="{{ route('dashboard') }}"
               class="nav-link group {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('dashboard') ? 'nav-icon-active' : '' }}">
                    <flux:icon name="home" class="w-5 h-5" />
                </div>
                <span class="text-sm">Dashboard</span>
                @if(request()->routeIs('dashboard'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                @endif
            </a>

            <a href="{{ route('participants.index') }}"
               class="nav-link group {{ request()->routeIs('participants.*') ? 'nav-active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('participants.*') ? 'nav-icon-active' : '' }}">
                    <flux:icon name="users" class="w-5 h-5" />
                </div>
                <span class="text-sm">Data Peserta</span>
                @if(request()->routeIs('participants.*'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                @endif
            </a>

            <!-- Konfigurasi WA -->
            <a href="{{ route('settings.wablas') }}"
               class="nav-link group {{ request()->routeIs('settings.wablas') ? 'nav-active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('settings.wablas') ? 'nav-icon-active' : '' }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </div>
                <span class="text-sm">Konfigurasi WA</span>
                @if(request()->routeIs('settings.wablas'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-green-500"></span>
                @endif
            </a>

            <!-- Import Data dengan Sub Menu -->
            <div x-data="{ open: {{ request()->routeIs('uploads.*') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" 
                        class="nav-link group w-full {{ request()->routeIs('uploads.*') ? 'nav-active' : '' }}">
                    <div class="nav-icon {{ request()->routeIs('uploads.*') ? 'nav-icon-active' : '' }}">
                        <flux:icon name="cloud-arrow-up" class="w-5 h-5" />
                    </div>
                    <span class="text-sm flex-1 text-left">Import Data</span>
                    <flux:icon name="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
                </button>
                
                <div x-show="open" x-collapse class="ml-4 pl-4 border-l border-slate-200 dark:border-slate-700 space-y-1">
                    <a href="{{ route('uploads.data') }}"
                       class="nav-link group text-sm {{ request()->routeIs('uploads.data') ? 'nav-active' : '' }}">
                        <div class="nav-icon {{ request()->routeIs('uploads.data') ? 'nav-icon-active' : '' }}">
                            <flux:icon name="table-cells" class="w-4 h-4" />
                        </div>
                        <span>Import Excel</span>
                        @if(request()->routeIs('uploads.data'))
                            <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        @endif
                    </a>
                    <a href="{{ route('uploads.pdf') }}"
                       class="nav-link group text-sm {{ request()->routeIs('uploads.pdf') ? 'nav-active' : '' }}">
                        <div class="nav-icon {{ request()->routeIs('uploads.pdf') ? 'nav-icon-active' : '' }}">
                            <flux:icon name="document" class="w-4 h-4" />
                        </div>
                        <span>Import PDF</span>
                        @if(request()->routeIs('uploads.pdf'))
                            <span class="ml-auto w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Pengaturan Data (Admin Only) -->
        @if(auth()->user()->isAdmin())
            <div class="space-y-1">
                <p class="px-3 text-[10px] sm:text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500/50"></span>
                    Pengaturan Data
                </p>

                <a href="{{ route('settings.paket') }}"
                   class="nav-link group {{ request()->routeIs('settings.paket') ? 'nav-active' : '' }}">
                    <div class="nav-icon {{ request()->routeIs('settings.paket') ? 'nav-icon-active' : '' }}">
                        <flux:icon name="cube" class="w-5 h-5" />
                    </div>
                    <span class="text-sm">Pengaturan Paket</span>
                    @if(request()->routeIs('settings.paket'))
                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    @endif
                </a>

                <a href="{{ route('settings.instansi') }}"
                   class="nav-link group {{ request()->routeIs('settings.instansi') ? 'nav-active' : '' }}">
                    <div class="nav-icon {{ request()->routeIs('settings.instansi') ? 'nav-icon-active' : '' }}">
                        <flux:icon name="building-office" class="w-5 h-5" />
                    </div>
                    <span class="text-sm">Pengaturan Instansi</span>
                    @if(request()->routeIs('settings.instansi'))
                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    @endif
                </a>
            </div>
        @endif

        {{-- Setting Akun (Admin Only) - HIDDEN FOR NOW, DO NOT DELETE
        @if(auth()->user()->isAdmin())
            <div class="space-y-1 mt-6">
                <p class="px-3 text-[10px] sm:text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500/50"></span>
                    Setting Akun
                </p>

                <a href="{{ route('settings.admin-users') }}"
                   class="nav-link group {{ request()->routeIs('settings.admin-users') ? 'nav-active' : '' }}">
                    <div class="nav-icon {{ request()->routeIs('settings.admin-users') ? 'nav-icon-active' : '' }}">
                        <flux:icon name="user-group" class="w-5 h-5" />
                    </div>
                    <span class="text-sm">Admin & User</span>
                    @if(request()->routeIs('settings.admin-users'))
                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    @endif
                </a>
            </div>
        @endif
        --}}
    </nav>

    <!-- User Profile Section -->
    <div class="p-3 sm:p-4 border-t border-slate-200 dark:border-slate-800 bg-gradient-to-t from-slate-50/80 to-white dark:from-slate-900/80 dark:to-slate-900">
        <div class="flex items-center gap-3 group">
            <div class="relative flex-shrink-0">
                <div class="absolute -inset-0.5 bg-gradient-to-br from-brand-500 to-indigo-500 rounded-full blur opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
                <div class="relative w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gradient-to-br from-brand-100 to-indigo-100 dark:from-brand-900/50 dark:to-indigo-900/50 flex items-center justify-center text-brand-600 dark:text-brand-400 font-bold text-sm border-2 border-white dark:border-slate-800 shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                </div>
                            <!-- Online status indicator - no animation for better performance -->
                                <div class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3">
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
                                </div>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 truncate flex items-center gap-1">
                    @if(auth()->user()->isAdmin())
                        <flux:icon name="shield-check" class="w-3 h-3" />
                        Administrator
                    @else
                        <flux:icon name="user" class="w-3 h-3" />
                        User
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-1">
                <a href="{{ route('profile.edit') }}" class="p-2 text-slate-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-colors" title="Profile">
                    <flux:icon name="cog" class="w-4 h-4" />
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Keluar">
                        <flux:icon name="x-circle" class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0.875rem;
            border-radius: 0.75rem;
            transition: background 0.15s ease;
            position: relative;
        }
        .nav-link:not(.nav-active):hover {
            background: linear-gradient(to right, rgba(46, 102, 245, 0.05), transparent);
        }
        .nav-link.nav-active {
            background: linear-gradient(135deg, rgba(46, 102, 245, 0.1), rgba(99, 102, 241, 0.1));
            color: #2563EB;
        }
        .dark .nav-link.nav-active {
            background: linear-gradient(135deg, rgba(46, 102, 245, 0.2), rgba(99, 102, 241, 0.2));
            color: #60A5FA;
        }
        .nav-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 0.5rem;
            color: #94A3B8;
            transition: color 0.15s ease;
        }
        .nav-icon.nav-icon-active {
            color: #2563EB;
            background: rgba(46, 102, 245, 0.1);
        }
        .dark .nav-icon.nav-icon-active {
            color: #60A5FA;
            background: rgba(96, 165, 250, 0.2);
        }
        .nav-link:hover .nav-icon {
            color: #2563EB;
        }
        .dark .nav-link:hover .nav-icon {
            color: #60A5FA;
        }
    </style>
</div>