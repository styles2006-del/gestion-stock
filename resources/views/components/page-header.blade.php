@props(['title', 'subtitle' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="flex items-start gap-3.5">
        @if ($icon)
            <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-500/10 text-accent-500">
                <x-icon :name="$icon" class="h-5 w-5" />
            </div>
        @endif
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if ($subtitle)
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if (! $slot->isEmpty())
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
