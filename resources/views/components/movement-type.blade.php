@props(['type' => 'in'])

@php
    $map = [
        'in' => ['badge-success', 'arrow-in'],
        'out' => ['badge-danger', 'arrow-out'],
        'adjustment' => ['badge-warning', 'refresh'],
        'transfer' => ['badge-info', 'transfer'],
    ];
    [$class, $icon] = $map[$type] ?? ['badge-neutral', 'arrows'];
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    <x-icon :name="$icon" class="h-3.5 w-3.5" />
    {{ __('movements.types.' . $type) }}
</span>
