@props(['icon' => 'box', 'title', 'hint' => null])

<div {{ $attributes->merge(['class' => 'card flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-ink-100 text-ink-400 dark:bg-white/10">
        <x-icon :name="$icon" class="h-6 w-6" />
    </div>
    <p class="mt-4 text-base font-medium text-ink-700 dark:text-ink-100">{{ $title }}</p>
    @if ($hint)
        <p class="mt-1 max-w-sm text-sm text-ink-400">{{ $hint }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
