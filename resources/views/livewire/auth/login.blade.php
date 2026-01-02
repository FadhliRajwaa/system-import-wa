<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <!-- Welcome Section -->
        <div class="flex flex-col gap-2 text-center mb-2">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                Selamat Datang Kembali
            </h2>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400">
                Masuk ke dashboard untuk mengelola sistem
            </p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm text-emerald-700 dark:text-emerald-300 flex items-start gap-2 animate-scale-in">
                <flux:icon name="check-circle" class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" />
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div class="space-y-2">
                <flux:label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Email Address</flux:label>
                <div class="relative group">
                    <flux:icon name="envelope" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors" />
                    <input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="input-modern pl-12"
                    />
                </div>
                @error('email')
                    <flux:error name="email" />
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <flux:label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Kata Sandi</flux:label>
                <div class="relative group">
                    <flux:icon name="lock-closed" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors" />
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        class="input-modern pl-12"
                    />
                </div>
                @error('password')
                    <flux:error name="password" />
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800 dark:focus:ring-offset-slate-900" />
                    <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-300 transition-colors">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors flex items-center gap-1 group">
                        Lupa sandi?
                        <flux:icon name="arrow-right" class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" />
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit" class="group relative w-full py-3.5 px-6 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-brand-500/30 hover:shadow-xl hover:shadow-brand-500/40 transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-shimmer"></span>
                <div class="relative flex items-center justify-center gap-2">
                    <flux:icon name="arrow-right" class="w-5 h-5" />
                    <span>Masuk ke Sistem</span>
                </div>
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="px-4 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400">Secure Login</span>
            </div>
        </div>

        <!-- Security Badge -->
        <div class="flex items-center justify-center text-xs text-slate-400 dark:text-slate-600">
            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 dark:bg-slate-800/50 rounded-full border border-slate-200 dark:border-slate-700">
                <flux:icon name="shield-check" class="w-3.5 h-3.5" />
                <span>256-bit SSL Secured</span>
            </div>
        </div>
    </div>
</x-layouts.auth>
