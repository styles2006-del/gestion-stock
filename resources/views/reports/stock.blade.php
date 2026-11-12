@extends('layouts.app')

@section('title', __('reports.stock_report'))

@section('content')
    @php
        $currency = \App\Models\Setting::getValue('currency', 'EUR');
        $money = fn ($v) => number_format((float) $v, 2, ',', ' ') . ' ' . $currency;
        $query = array_filter(['warehouse_id' => $warehouseId]);
    @endphp

    <x-page-header :title="__('reports.stock_report')" :subtitle="__('reports.stock_report_desc')" icon="warehouse">
        <a href="{{ route('reports.index') }}" class="btn-ghost">
            <x-icon name="chevron-left" class="h-4 w-4" /> {{ __('app.back') }}
        </a>
        <a href="{{ route('reports.stock.csv', $query) }}" class="btn-secondary">
            <x-icon name="download" class="h-4 w-4" /> {{ __('app.export_csv') }}
        </a>
        <a href="{{ route('reports.stock.pdf', $query) }}" class="btn-secondary">
            <x-icon name="download" class="h-4 w-4" /> {{ __('app.export_pdf') }}
        </a>
    </x-page-header>

    {{-- Valeur totale --}}
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card :label="__('reports.stock_value')" :value="$money($totalValue)" icon="coins" tone="success" />
        <x-stat-card :label="__('reports.sku')" :value="$rows->count()" icon="box" tone="accent" />
        <x-stat-card :label="__('reports.product')" :value="$rows->where('low', true)->count()" icon="alert" tone="warning" :hint="__('products.low_stock')" />
    </div>

    {{-- Filtre dépôt --}}
    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="w-full sm:w-64">
            <label class="label">{{ __('reports.warehouse') }}</label>
            <select name="warehouse_id" class="input">
                <option value="">{{ __('app.all') }}</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> {{ __('app.filters') }}</button>
            <a href="{{ route('reports.stock') }}" class="btn-ghost" title="{{ __('app.reset') }}"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    @if ($rows->isEmpty())
        <x-empty-state icon="warehouse" :title="__('reports.no_data')" />
    @else
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('reports.sku') }}</th>
                            <th>{{ __('reports.product') }}</th>
                            <th class="hidden md:table-cell">{{ __('reports.category') }}</th>
                            <th class="hidden lg:table-cell">{{ __('reports.warehouse') }}</th>
                            <th class="text-right">{{ __('reports.quantity') }}</th>
                            <th class="hidden sm:table-cell text-right">{{ __('reports.min_stock') }}</th>
                            <th class="hidden sm:table-cell text-right">{{ __('reports.purchase_price') }}</th>
                            <th class="text-right">{{ __('reports.value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="font-mono text-xs text-ink-400">{{ $row['sku'] }}</td>
                                <td class="font-medium text-ink-800 dark:text-ink-100">{{ $row['name'] }}</td>
                                <td class="hidden md:table-cell text-ink-500">{{ $row['category'] }}</td>
                                <td class="hidden lg:table-cell text-ink-500">{{ $row['warehouse'] }}</td>
                                <td class="text-right">
                                    <span class="font-semibold tabular-nums {{ $row['low'] ? 'text-red-500' : 'text-ink-800 dark:text-ink-100' }}">
                                        {{ $row['quantity'] }}
                                    </span>
                                    <span class="text-xs text-ink-400">{{ $row['unit'] }}</span>
                                    @if ($row['low'])
                                        <span class="badge-danger ml-1.5"><x-icon name="alert" class="h-3 w-3" /></span>
                                    @endif
                                </td>
                                <td class="hidden sm:table-cell text-right tabular-nums text-ink-500">{{ $row['min_stock'] }}</td>
                                <td class="hidden sm:table-cell text-right tabular-nums text-ink-500">{{ $money($row['purchase_price']) }}</td>
                                <td class="text-right font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ $money($row['value']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-black/10 dark:border-white/15">
                            <td colspan="7" class="px-4 py-4 text-right text-sm font-semibold text-ink-600 dark:text-ink-300">
                                {{ __('reports.stock_value') }}
                            </td>
                            <td class="px-4 py-4 text-right text-base font-semibold tabular-nums text-accent-500">
                                {{ $money($totalValue) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
@endsection
