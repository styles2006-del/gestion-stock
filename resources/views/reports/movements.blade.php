@extends('layouts.app')

@section('title', __('reports.movements_report'))

@section('content')
    @php
        $query = ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')];
    @endphp

    <x-page-header :title="__('reports.movements_report')" :subtitle="__('reports.movements_report_desc')" icon="arrows">
        <a href="{{ route('reports.index') }}" class="btn-ghost">
            <x-icon name="chevron-left" class="h-4 w-4" /> {{ __('app.back') }}
        </a>
        <a href="{{ route('reports.movements.csv', $query) }}" class="btn-secondary">
            <x-icon name="download" class="h-4 w-4" /> {{ __('app.export_csv') }}
        </a>
        <a href="{{ route('reports.movements.pdf', $query) }}" class="btn-secondary">
            <x-icon name="download" class="h-4 w-4" /> {{ __('app.export_pdf') }}
        </a>
    </x-page-header>

    {{-- Période --}}
    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="w-full sm:w-44">
            <label class="label">{{ __('app.from') }}</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input">
        </div>
        <div class="w-full sm:w-44">
            <label class="label">{{ __('app.to') }}</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input">
        </div>
        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> {{ __('app.filters') }}</button>
            <a href="{{ route('reports.movements') }}" class="btn-ghost" title="{{ __('app.reset') }}"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    @if ($movements->isEmpty())
        <x-empty-state icon="arrows" :title="__('reports.no_data')" :hint="__('movements.empty_hint')" />
    @else
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('reports.date') }}</th>
                            <th>{{ __('reports.type') }}</th>
                            <th>{{ __('reports.product') }}</th>
                            <th class="hidden md:table-cell">{{ __('reports.category') }}</th>
                            <th class="hidden sm:table-cell">{{ __('reports.warehouse') }}</th>
                            <th class="text-right">{{ __('movements.variation') }}</th>
                            <th class="hidden lg:table-cell">{{ __('app.reference') }}</th>
                            <th class="hidden lg:table-cell">{{ __('reports.user') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $m)
                            @php
                                $delta = $m->quantity_after - $m->quantity_before;
                                $deltaClass = $delta > 0 ? 'text-emerald-500' : ($delta < 0 ? 'text-red-500' : 'text-ink-400');
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap text-xs text-ink-400">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td><x-movement-type :type="$m->type" /></td>
                                <td>
                                    <p class="font-medium text-ink-800 dark:text-ink-100">{{ $m->product?->name ?? '—' }}</p>
                                    <p class="text-[11px] text-ink-400">{{ $m->product?->sku }}</p>
                                </td>
                                <td class="hidden md:table-cell text-ink-500">{{ $m->product?->category?->name ?? '—' }}</td>
                                <td class="hidden sm:table-cell text-ink-500">{{ $m->warehouse?->name ?? '—' }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <span class="font-semibold tabular-nums {{ $deltaClass }}">{{ $delta > 0 ? '+' : '' }}{{ $delta }}</span>
                                    <span class="ml-1.5 text-xs tabular-nums text-ink-400">{{ $m->quantity_before }} → {{ $m->quantity_after }}</span>
                                </td>
                                <td class="hidden lg:table-cell text-ink-500">{{ $m->reference ?? '—' }}</td>
                                <td class="hidden lg:table-cell text-ink-500">{{ $m->user?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-4 text-xs text-ink-400">{{ __('app.results_count', ['count' => $movements->count()]) }}</p>
    @endif
@endsection
