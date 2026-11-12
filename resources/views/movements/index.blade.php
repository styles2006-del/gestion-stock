@extends('layouts.app')

@section('title', __('movements.title'))

@section('content')
    <x-page-header :title="__('movements.title')" :subtitle="__('app.results_count', ['count' => $movements->total()])" icon="arrows">
        @if (auth()->user()->canManage())
            <a href="{{ route('movements.transfer.form') }}" class="btn-secondary">
                <x-icon name="transfer" class="h-4 w-4" /> {{ __('movements.transfer') }}
            </a>
            <a href="{{ route('movements.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> {{ __('movements.new') }}
            </a>
        @endif
    </x-page-header>

    {{-- Filtres --}}
    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label">{{ __('app.search') }}</label>
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" name="q" value="{{ request('q') }}" class="input pl-9" placeholder="{{ __('movements.reference') }} / {{ __('movements.product') }}">
            </div>
        </div>

        <div class="w-full sm:w-44">
            <label class="label">{{ __('movements.type') }}</label>
            <select name="type" class="input">
                <option value="">{{ __('app.all') }}</option>
                @foreach (['in', 'out', 'adjustment', 'transfer'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ __('movements.types.' . $type) }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-48">
            <label class="label">{{ __('movements.warehouse') }}</label>
            <select name="warehouse_id" class="input">
                <option value="">{{ __('app.all') }}</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-40">
            <label class="label">{{ __('app.from') }}</label>
            <input type="date" name="from" value="{{ request('from') }}" class="input">
        </div>

        <div class="w-full sm:w-40">
            <label class="label">{{ __('app.to') }}</label>
            <input type="date" name="to" value="{{ request('to') }}" class="input">
        </div>

        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> {{ __('app.filters') }}</button>
            <a href="{{ route('movements.index') }}" class="btn-ghost" title="{{ __('app.reset') }}"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    @if ($movements->isEmpty())
        <x-empty-state icon="arrows" :title="__('movements.empty')" :hint="__('movements.empty_hint')">
            @if (auth()->user()->canManage())
                <a href="{{ route('movements.create') }}" class="btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('movements.new') }}
                </a>
            @endif
        </x-empty-state>
    @else
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('app.date') }}</th>
                            <th>{{ __('movements.type') }}</th>
                            <th>{{ __('movements.product') }}</th>
                            <th class="hidden sm:table-cell">{{ __('movements.warehouse') }}</th>
                            <th class="hidden text-right sm:table-cell">{{ __('movements.quantity') }}</th>
                            <th class="hidden lg:table-cell">{{ __('app.reference') }}</th>
                            <th class="hidden md:table-cell">{{ __('app.user') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $m)
                            @php
                                $increased = $m->quantity_after > $m->quantity_before;
                                $decreased = $m->quantity_after < $m->quantity_before;
                                $delta = $m->quantity_after - $m->quantity_before;
                                $deltaClass = $increased ? 'text-emerald-500' : ($decreased ? 'text-red-500' : 'text-ink-400');
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap text-xs text-ink-400">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                <td><x-movement-type :type="$m->type" /></td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-100 text-ink-500 dark:bg-white/10">
                                            <x-icon name="box" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-ink-800 dark:text-ink-100">{{ $m->product?->name ?? '—' }}</p>
                                            <p class="text-[11px] text-ink-400">{{ $m->product?->sku }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell text-ink-500">{{ $m->warehouse?->name ?? '—' }}</td>
                                <td class="hidden text-right whitespace-nowrap sm:table-cell">
                                    <span class="tabular-nums text-ink-500">{{ $m->quantity_before }} → {{ $m->quantity_after }}</span>
                                    <span class="ml-1.5 font-semibold tabular-nums {{ $deltaClass }}">{{ $delta > 0 ? '+' : '' }}{{ $delta }}</span>
                                </td>
                                <td class="hidden lg:table-cell">
                                    @if ($m->reference)
                                        <span class="font-medium text-ink-700 dark:text-ink-200">{{ $m->reference }}</span>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                    @if ($m->note)
                                        <p class="mt-0.5 max-w-[220px] truncate text-[11px] text-ink-400">{{ $m->note }}</p>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell text-ink-500">{{ $m->user?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $movements->links() }}
        </div>
    @endif
@endsection
