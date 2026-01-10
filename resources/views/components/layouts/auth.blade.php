<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Rikkes Berkala' }}</title>

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
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    </noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-[#0B0F19] antialiased selection:bg-brand-500/30 selection:text-brand-600">

    <div class="min-h-screen w-full flex relative overflow-hidden items-center justify-center bg-gradient-to-br from-slate-50 via-brand-50 to-indigo-50 dark:from-[#0B0F19] dark:via-[#111827] dark:to-[#1e1b4b]">

        <!-- Static Background - High Performance -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <!-- Static gradients instead of animated blobs -->
            <div class="absolute top-0 right-0 w-3/4 h-3/4 bg-gradient-to-br from-brand-400/10 to-transparent rounded-full blur-3xl opacity-50"></div>
            <div class="absolute bottom-0 left-0 w-3/4 h-3/4 bg-gradient-to-tr from-indigo-400/10 to-transparent rounded-full blur-3xl opacity-50"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 w-full max-w-lg p-4 sm:p-6 lg:p-8">

            <!-- Logo Section -->
            <div class="mb-8 text-center animate-fade-in-up">
                <div class="inline-flex items-center justify-center mb-6 gap-3 sm:gap-4">
                    <img src="{{ asset('Logo-Jabar.png') }}" alt="Polda Jawa Barat" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-lg" />
                    <img src="{{ asset('logo-biddokkes-no-wm.png') }}" alt="Biddokkes" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-lg" />
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
                    RIKKES<span class="text-gradient"> BERKALA</span>
                </h1>
                <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    ANGGOTA DAN ASN POLDA JAWA BARAT
                </p>
            </div>

            <!-- Card - Optimized -->
            <div class="relative group">
                <div class="relative bg-white border border-slate-200 shadow-xl rounded-3xl p-6 sm:p-8 lg:p-10 dark:bg-[#111827] dark:border-white/5">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center space-y-3">
                <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500">
                    &copy; {{ date('Y') }} Rikkes Berkala - Polda Jawa Barat. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
