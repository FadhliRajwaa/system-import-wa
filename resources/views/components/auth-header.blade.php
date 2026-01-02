@props([
    'title',
    'description' => null,
])

<div class="flex flex-col gap-2 text-center mb-6">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h2>
    @if($description)
        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $description }}</p>
    @endif
</div>
