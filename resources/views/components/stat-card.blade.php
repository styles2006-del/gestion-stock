@props(['label', 'value', 'icon' => 'box', 'hint' => null, 'tone' => 'neutral', 'href' => null])

@php
    $tones = [
        'neutral' => 'bg-ink-800/5 text-ink-700 dark:bg-white/10 dark:text-ink-100',
        'accent' => 'bg-accent-500/12 text-accent-500',
        'success' => 'bg-emerald-500/12 text-emerald-500',
        'warning' => 'bg-amber-500/15 text-amber-500',
        'danger' => 'bg-red-500/12 text-red-500',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card group relative overflow-hidden p-5 transition-transform duration-300 hover:-translate-y-0.5']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-ink-500 dark:text-ink-400">{{ $label }}</p>
            <p class="stat-value mt-2">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-ink-400">{{ $hint }}</p>
            @endif
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $tones[$tone] ?? $tones['neutral'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </div>
    </div>
</{{ $tag }}>
