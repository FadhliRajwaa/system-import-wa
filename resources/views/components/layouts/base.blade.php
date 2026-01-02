<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.head')
        <!-- Chart.js (local) -->
        <script src="{{ asset('js/chart.min.js') }}"></script>
    </head>
    <body class="h-full bg-slate-50 dark:bg-slate-950 font-sans antialiased selection:bg-brand-500 selection:text-white">
        {{ $slot }}

        @fluxScripts
        @livewireScripts
        @stack('scripts')
    </body>
</html>
