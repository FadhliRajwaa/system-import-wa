<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-900 font-sans antialiased overflow-hidden selection:bg-brand-500 selection:text-white">
    <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden">
        
        <!-- Animated Background -->
        <div class="absolute inset-0 w-full h-full bg-slate-900 z-0">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-500/20 rounded-full mix-blend-screen filter blur-3xl opacity-30 animate-float"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full mix-blend-screen filter blur-3xl opacity-30 animate-float" style="animation-delay: 2s"></div>
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 text-center px-6 max-w-3xl mx-auto">
            <div class="mb-8 flex justify-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center shadow-2xl shadow-brand-500/20 animate-scale">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>

            <h1 class="text-5xl md:text-7xl font-bold text-white tracking-tight mb-6 animate-fade-in-up">
                System<span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-indigo-400">Import</span>
            </h1>

            <p class="text-xl text-slate-300 mb-10 leading-relaxed max-w-2xl mx-auto animate-fade-in-up" style="animation-delay: 0.1s">
                Advanced data management platform for WhatsApp broadcasts and participant tracking. Secure, fast, and reliable.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in-up" style="animation-delay: 0.2s">
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white text-slate-900 font-bold rounded-xl hover:bg-slate-50 transition-all transform hover:-translate-y-1 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                    <span>Access Dashboard</span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            <div class="mt-16 pt-8 border-t border-white/10 text-sm text-slate-500 animate-fade-in-up" style="animation-delay: 0.3s">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Authorized Personnel Only.
            </div>
        </div>
    </div>
</body>
</html>
