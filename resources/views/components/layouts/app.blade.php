<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Dark Mode Initialization - Must run before page renders to prevent flash --}}
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'system';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Fonts - Optimized loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    </noscript>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 font-sans antialiased selection:bg-brand-500/30 selection:text-brand-600">

    <div class="flex h-screen w-full relative" x-data="{ sidebarOpen: false }">
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <!-- Sidebar -->
        <aside class="fixed lg:static inset-y-0 left-0 z-50 w-72 lg:w-72 transform transition-transform duration-200 ease-out lg:translate-x-0 bg-white lg:bg-transparent dark:bg-[#111827] lg:dark:bg-transparent border-r border-slate-200 dark:border-white/5 lg:border-none h-full flex flex-col will-change-transform"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="h-full p-4 lg:py-6 lg:pl-6">
                <div class="h-full bg-white border border-slate-200 shadow-sm rounded-2xl flex flex-col overflow-hidden dark:bg-[#111827] dark:border-white/5">
                    @include('components.layouts.app.sidebar')
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative z-10">

            <!-- Enhanced Header -->
            <header class="sticky top-0 z-20 bg-white border-b border-slate-200 dark:bg-slate-900 dark:border-slate-800">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16 sm:h-20">
                    <!-- Left: Mobile Menu Toggle & Page Title -->
                    <div class="flex items-center gap-3 sm:gap-4 flex-1">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                            <flux:icon name="bars-3" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </button>

                        <div class="hidden sm:flex flex-col">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ $heading ?? 'Dashboard' }}
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Selamat datang kembali</p>
                        </div>
                    </div>

                    <!-- Right: Search, Notifications, Profile -->
                    <div class="flex items-center gap-2 sm:gap-4">
                        <!-- Search (Desktop) -->
                        <div class="hidden md:flex relative">
                            <div class="relative">
                                <flux:icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                <input
                                    type="search"
                                    placeholder="Cari peserta, NRP..."
                                    class="w-48 sm:w-64 lg:w-80 pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-0 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500/50 focus:w-64 transition-all focus:w-72"
                                />
                            </div>
                        </div>

                        <!-- Notification - Optimized animation -->
                        <button class="relative p-2.5 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors group">
                            <flux:icon name="bell" class="w-5 h-5" />
                            <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                <span class="status-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 ring-2 ring-white dark:ring-slate-900"></span>
                            </span>
                        </button>

                        <!-- System Status Indicator - Optimized -->
                        <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-full">
                            <span class="relative flex h-2 w-2">
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Online</span>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 pl-2 pr-1 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all group">
                                <div class="text-right hidden sm:block">
                                    <p class="text-xs font-semibold text-slate-900 dark:text-white truncate max-w-24">{{ auth()->user()->name ?? 'User' }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ auth()->user()->isAdmin() ? 'Administrator' : 'User' }}</p>
                                </div>
                                <div class="relative">
                                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs sm:text-sm ring-2 ring-white dark:ring-slate-900 shadow-lg shadow-brand-500/30 group-hover:shadow-xl group-hover:shadow-brand-500/40 transition-all group-hover:scale-105">
                                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                                    </div>
                                    <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-slate-900"></div>
                                </div>
                                <flux:icon name="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors sm:block hidden" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                                 x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
                                 class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-2xl bg-white dark:bg-slate-800 py-2 shadow-2xl shadow-slate-900/10 dark:shadow-slate-950/50 ring-1 ring-slate-200 dark:ring-slate-700"
                                 style="display: none;">
                                <div class="px-3 py-3 border-b border-slate-100 dark:border-slate-700">
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Signed in as</p>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->email }}</p>
                                </div>

                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 mx-1 rounded-xl transition-colors group">
                                        <flux:icon name="user" class="w-4 h-4 text-slate-400 group-hover:text-brand-500" />
                                        <span>Profile</span>
                                    </a>
                                    <a href="{{ route('user-password.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 mx-1 rounded-xl transition-colors group">
                                        <flux:icon name="lock-closed" class="w-4 h-4 text-slate-400 group-hover:text-brand-500" />
                                        <span>Password</span>
                                    </a>
                                    <a href="{{ route('appearance.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 mx-1 rounded-xl transition-colors group">
                                        <flux:icon name="paint-brush" class="w-4 h-4 text-slate-400 group-hover:text-brand-500" />
                                        <span>Appearance</span>
                                    </a>
                                </div>

                                <div class="h-px bg-slate-100 dark:bg-slate-700 my-1 mx-2"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 mx-1 rounded-xl transition-colors group">
                                        <flux:icon name="x-circle" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                                        <span>Sign out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Area -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 lg:p-8 lg:pt-0 scroll-smooth">
                <div class="max-w-7xl mx-auto w-full pb-10 animate-fade-in-up">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div 
        x-data="toastNotification()" 
        x-on:show-toast.window="addToast($event.detail)"
        class="fixed bottom-4 right-4 z-50 space-y-2"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div 
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-8"
                :class="{
                    'bg-emerald-500': toast.type === 'success',
                    'bg-red-500': toast.type === 'error',
                    'bg-amber-500': toast.type === 'warning',
                    'bg-blue-500': toast.type === 'info'
                }"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white shadow-lg min-w-80 max-w-md"
            >
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </template>
                <p class="text-sm font-medium flex-1" x-text="toast.message"></p>
                <button @click="removeToast(toast.id)" class="text-white/70 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        function toastNotification() {
            return {
                toasts: [],
                addToast(detail) {
                    const id = Date.now();
                    const toast = {
                        id: id,
                        type: detail.type || 'info',
                        message: detail.message || 'Notification',
                        visible: true
                    };
                    this.toasts.push(toast);
                    
                    // Log to console
                    console.log(`[${toast.type.toUpperCase()}]`, toast.message);
                    
                    // Auto remove after 5 seconds
                    setTimeout(() => {
                        this.removeToast(id);
                    }, 5000);
                },
                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index > -1) {
                        this.toasts[index].visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 200);
                    }
                }
            }
        }
    </script>

    @livewireScripts
</body>
</html>
