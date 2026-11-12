@extends('layouts.app')

@section('title', __('nav.reports'))

@section('content')
    @php
        $currency = \App\Models\Setting::getValue('currency', 'EUR');
        $maxTop = max(1, (int) $summary['topProducts']->max('qty'));
        $maxCategory = max(1, (int) $summary['byCategory']->max('products_count'));
    @endphp

    <x-page-header :title="__('reports.title')" :subtitle="__('reports.subtitle')" icon="chart" />

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
            <a href="{{ route('reports.index') }}" class="btn-ghost" title="{{ __('app.reset') }}"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    {{-- Synthèse --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card :label="__('reports.entries')" :value="$summary['in']" icon="arrow-in" tone="success" />
        <x-stat-card :label="__('reports.exits')" :value="$summary['out']" icon="arrow-out" tone="danger" />
        <x-stat-card :label="__('reports.movements_count')" :value="$summary['count']" icon="arrows" tone="accent" />
    </div>

    {{-- Rapports détaillés --}}
    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="card flex flex-col p-6">
            <div class="flex items-start gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-accent-500/12 text-accent-500">
                    <x-icon name="warehouse" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('reports.stock_report') }}</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ __('reports.stock_report_desc') }}</p>
                </div>
            </div>
            <div class="mt-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.stock') }}" class="btn-primary">
                    <x-icon name="eye" class="h-4 w-4" /> {{ __('app.view') }}
                </a>
                <a href="{{ route('reports.stock.csv') }}" class="btn-secondary">
                    <x-icon name="download" class="h-4 w-4" /> CSV
                </a>
                <a href="{{ route('reports.stock.pdf') }}" class="btn-secondary">
                    <x-icon name="download" class="h-4 w-4" /> PDF
                </a>
            </div>
        </div>

        <div class="card flex flex-col p-6">
            <div class="flex items-start gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-500">
                    <x-icon name="arrows" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('reports.movements_report') }}</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ __('reports.movements_report_desc') }}</p>
                </div>
            </div>
            <div class="mt-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.movements') }}" class="btn-primary">
                    <x-icon name="eye" class="h-4 w-4" /> {{ __('app.view') }}
                </a>
                <a href="{{ route('reports.movements.csv', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-secondary">
                    <x-icon name="download" class="h-4 w-4" /> CSV
                </a>
                <a href="{{ route('reports.movements.pdf', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-secondary">
                    <x-icon name="download" class="h-4 w-4" /> PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Détails --}}
    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="card p-6">
            <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('reports.top_products') }}</h2>
            <div class="mt-4 space-y-4">
                @forelse ($summary['topProducts'] as $row)
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="truncate font-medium text-ink-700 dark:text-ink-100">{{ $row->product?->name ?? '—' }}</span>
                            <span class="shrink-0 tabular-nums text-ink-500 dark:text-ink-400">{{ $row->qty }} {{ $row->product?->unit }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-black/[0.05] dark:bg-white/10">
                            <div class="h-full rounded-full bg-accent-500" style="width: {{ round(($row->qty / $maxTop) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-400">{{ __('reports.no_data') }}</p>
                @endforelse
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('reports.by_category') }}</h2>
            <div class="mt-4 space-y-4">
                @forelse ($summary['byCategory'] as $category)
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="truncate font-medium text-ink-700 dark:text-ink-100">{{ $category->name }}</span>
                            <span class="shrink-0 tabular-nums text-ink-500 dark:text-ink-400">{{ $category->products_count }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-black/[0.05] dark:bg-white/10">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ round(($category->products_count / $maxCategory) * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-400">{{ __('reports.no_data') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
