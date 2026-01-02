<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sign In' }} | {{ config('app.name') }}</title>

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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .animate-blob { animation: blob 7s infinite; }
        .animate-shimmer { animation: shimmer 2s infinite; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 antialiased overflow-hidden">
    <div class="min-h-full flex relative overflow-hidden">
        
        <!-- Animated Background Mesh -->
        <div class="absolute inset-0 w-full h-full bg-slate-900 z-0">
            <div class="absolute top-0 -left-4 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-0 -right-4 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>
        </div>

        <!-- Left Side: Brand Visuals (Desktop) -->
        <div class="hidden lg:flex flex-1 flex-col justify-center px-12 xl:px-24 relative z-10">
            <div class="max-w-2xl">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-4 mb-12 group">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-2xl group-hover:scale-110 transition-transform duration-500">
                        <flux:icon name="chart-bar" class="w-8 h-8 text-white" />
                    </div>
                    <span class="text-3xl font-bold text-white tracking-tight group-hover:text-blue-200 transition-colors">System Import WA</span>
                </div>

                <!-- Hero Text -->
                <h1 class="text-6xl font-extrabold text-white leading-[1.1] mb-8 tracking-tight">
                    Manage Data <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-400 to-purple-400 animate-gradient-x">Effortlessly.</span>
                </h1>
                
                <p class="text-xl text-slate-300 mb-12 leading-relaxed max-w-lg font-light">
                    Advanced administration platform for managing participant data and automated WhatsApp broadcasts with real-time queueing.
                </p>

                <!-- Social Proof / Stats -->
                <div class="flex items-center gap-8 p-6 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 w-fit">
                    <div class="flex -space-x-4">
                        <img class="w-12 h-12 rounded-full border-2 border-slate-900 shadow-lg" src="https://ui-avatars.com/api/?name=Admin+1&background=random" alt="Admin 1">
                        <img class="w-12 h-12 rounded-full border-2 border-slate-900 shadow-lg" src="https://ui-avatars.com/api/?name=Admin+2&background=random" alt="Admin 2">
                        <img class="w-12 h-12 rounded-full border-2 border-slate-900 shadow-lg" src="https://ui-avatars.com/api/?name=Admin+3&background=random" alt="Admin 3">
                        <div class="w-12 h-12 rounded-full border-2 border-slate-900 bg-slate-800 flex items-center justify-center text-xs font-bold text-white shadow-lg">
                            +5
                        </div>
                    </div>
                    <div>
                        <p class="text-white font-bold text-lg">Trusted by Teams</p>
                        <div class="flex items-center gap-1 text-yellow-400">
                            <flux:icon name="star" class="w-4 h-4 fill-current" />
                            <flux:icon name="star" class="w-4 h-4 fill-current" />
                            <flux:icon name="star" class="w-4 h-4 fill-current" />
                            <flux:icon name="star" class="w-4 h-4 fill-current" />
                            <flux:icon name="star" class="w-4 h-4 fill-current" />
                            <span class="text-slate-400 text-sm ml-2 font-medium">5.0 Rating</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="flex-1 flex flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24 w-full lg:w-[540px] bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl border-l border-white/20 shadow-2xl relative z-20">
            <div class="mx-auto w-full max-w-sm">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex justify-center mb-10">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center shadow-lg text-white transform rotate-3">
                        <flux:icon name="chart-bar" class="w-9 h-9" />
                    </div>
                </div>

                {{ $slot }}

                <div class="mt-10 pt-6 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex justify-center items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <flux:icon name="shield-check" class="w-4 h-4 text-green-500" />
                        <span>Secured by Laravel Fortify &bull; {{ date('Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>