@props(['status'])

@php
    $map = [
        'draft' => 'badge-neutral',
        'confirmed' => 'badge-info',
        'delivered' => 'badge-success',
        'cancelled' => 'badge-danger',
    ];
@endphp

<span {{ $attributes->merge(['class' => $map[$status] ?? 'badge-neutral']) }}>
    {{ __('documents.statuses.' . $status) }}
</span>
