<section class="w-full max-w-4xl mx-auto animate-fade-in-up">
    <!-- Enhanced Header -->
    <div class="mb-8 space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center shadow-lg shadow-pink-500/30">
                <flux:icon name="paint-brush" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
            </div>
            Tampilan
        </h1>
        <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Sesuaikan tema aplikasi sesuai preferensi Anda</p>
    </div>

    <!-- Enhanced Theme Selection Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="space-y-8">
                <!-- Theme Options -->
                <div x-data="{
                    theme: localStorage.getItem('theme') || 'system',
                    setTheme(value) {
                        this.theme = value;
                        localStorage.setItem('theme', value);

                        if (value === 'dark' || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    }
                }" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <!-- Light Mode -->
                    <button
                        @click="setTheme('light')"
                        :class="theme === 'light' ? 'ring-2 ring-amber-500 ring-offset-2 dark:ring-offset-slate-900' : ''"
                        class="flex flex-col items-center p-6 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border-2 border-transparent hover:border-amber-200 dark:hover:border-amber-800 transition-all group cursor-pointer"
                    >
                        <div class="w-16 h-16 rounded-2xl bg-white dark:bg-slate-800 shadow-lg shadow-amber-200/50 dark:shadow-black/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <flux:icon name="sun" class="w-8 h-8 text-amber-500" />
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-2">Light Mode</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center">Tampilan cerah dan bersih</p>
                        <div x-show="theme === 'light'" class="mt-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                <flux:icon name="check" class="w-3 h-3" /> Aktif
                            </span>
                        </div>
                    </button>

                    <!-- Dark Mode -->
                    <button
                        @click="setTheme('dark')"
                        :class="theme === 'dark' ? 'ring-2 ring-indigo-500 ring-offset-2 dark:ring-offset-slate-900' : ''"
                        class="flex flex-col items-center p-6 bg-gradient-to-br from-slate-700 to-slate-900 rounded-2xl border-2 border-transparent hover:border-slate-500 transition-all group cursor-pointer"
                    >
                        <div class="w-16 h-16 rounded-2xl bg-slate-800 shadow-lg shadow-black/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <flux:icon name="moon" class="w-8 h-8 text-indigo-400" />
                        </div>
                        <h4 class="font-bold text-white mb-2">Dark Mode</h4>
                        <p class="text-xs text-slate-300 text-center">Nyaman untuk mata di malam hari</p>
                        <div x-show="theme === 'dark'" class="mt-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400">
                                <flux:icon name="check" class="w-3 h-3" /> Aktif
                            </span>
                        </div>
                    </button>

                    <!-- System Mode -->
                    <button
                        @click="setTheme('system')"
                        :class="theme === 'system' ? 'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900' : ''"
                        class="flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl border-2 border-transparent hover:border-blue-200 dark:hover:border-blue-800 transition-all group cursor-pointer"
                    >
                        <div class="w-16 h-16 rounded-2xl bg-white dark:bg-slate-800 shadow-lg shadow-blue-200/50 dark:shadow-black/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <flux:icon name="computer-desktop" class="w-8 h-8 text-blue-500" />
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-2">System</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center">Ikuti pengaturan perangkat</p>
                        <div x-show="theme === 'system'" class="mt-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">
                                <flux:icon name="check" class="w-3 h-3" /> Aktif
                            </span>
                        </div>
                    </button>
                </div>

                <!-- Info Section -->
                <div class="flex items-start gap-4 p-5 bg-gradient-to-r from-brand-50 to-indigo-50 dark:from-brand-900/20 dark:to-indigo-900/20 rounded-2xl border border-brand-200 dark:border-brand-800">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center flex-shrink-0">
                        <flux:icon name="information-circle" class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                    </div>
                    <div>
                        <h4 class="font-bold text-brand-800 dark:text-brand-300 mb-1">Info Tema</h4>
                        <p class="text-sm text-brand-700 dark:text-brand-400 leading-relaxed">
                            Pengaturan tema akan disimpan secara lokal di browser Anda. Jika Anda login di perangkat lain, Anda mungkin perlu mengatur ulang preferensi tema ini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
