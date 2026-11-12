@extends('layouts.app')

@section('title', __('nav.products'))

@section('content')
    @php $currency = \App\Models\Setting::getValue('currency', 'EUR'); @endphp

    <x-page-header :title="__('products.title')" :subtitle="__('app.results_count', ['count' => $products->total()])" icon="box">
        @if (auth()->user()->canManage())
            <a href="{{ route('products.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> {{ __('products.new') }}
            </a>
        @endif
    </x-page-header>

    {{-- Filtres --}}
    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label">{{ __('app.search') }}</label>
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" name="q" value="{{ request('q') }}" class="input pl-9" placeholder="{{ __('products.name') }} / SKU / {{ __('products.barcode') }}">
            </div>
        </div>
        <div class="w-full sm:w-48">
            <label class="label">{{ __('products.category') }}</label>
            <select name="category_id" class="input">
                <option value="">{{ __('app.all_f') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-4 pb-2.5">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                <input type="checkbox" name="low" value="1" class="checkbox" @checked(request('low'))>
                {{ __('products.filter_low') }}
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                <input type="checkbox" name="inactive" value="1" class="checkbox" @checked(request('inactive'))>
                {{ __('products.filter_inactive') }}
            </label>
        </div>
        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> {{ __('app.filters') }}</button>
            <a href="{{ route('products.index') }}" class="btn-ghost"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    @if ($products->isEmpty())
        <x-empty-state icon="box" :title="__('products.empty')" :hint="__('products.empty_hint')">
            @if (auth()->user()->canManage())
                <a href="{{ route('products.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('products.new') }}</a>
            @endif
        </x-empty-state>
    @else
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('products.name') }}</th>
                            <th class="hidden md:table-cell">{{ __('products.category') }}</th>
                            <th class="text-right">{{ __('products.total_stock') }}</th>
                            <th class="hidden sm:table-cell text-right">{{ __('products.purchase_price') }}</th>
                            <th class="hidden lg:table-cell text-right">{{ __('products.sale_price') }}</th>
                            <th class="text-right">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php $low = $product->min_stock > 0 && $product->total_quantity <= $product->min_stock; @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-100 text-ink-500 dark:bg-white/10">
                                            <x-icon name="box" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $product) }}" class="font-medium text-ink-800 hover:text-accent-500 dark:text-ink-100">
                                                {{ $product->name }}
                                            </a>
                                            <p class="text-[11px] text-ink-400">{{ $product->sku }}</p>
                                        </div>
                                        @unless ($product->is_active)
                                            <span class="badge-neutral">{{ __('app.inactive') }}</span>
                                        @endunless
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <span class="text-ink-500">{{ $product->category?->name ?? '—' }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="font-semibold tabular-nums {{ $low ? 'text-red-500' : 'text-ink-800 dark:text-ink-100' }}">
                                        {{ $product->total_quantity }}
                                    </span>
                                    <span class="text-xs text-ink-400">{{ $product->unit }}</span>
                                    @if ($low)
                                        <span class="badge-danger ml-1.5"><x-icon name="alert" class="h-3 w-3" /></span>
                                    @endif
                                </td>
                                <td class="hidden sm:table-cell text-right tabular-nums text-ink-500">{{ number_format($product->purchase_price, 2, ',', ' ') }} {{ $currency }}</td>
                                <td class="hidden lg:table-cell text-right tabular-nums text-ink-500">{{ number_format($product->sale_price, 2, ',', ' ') }} {{ $currency }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('products.show', $product) }}" class="btn-ghost !p-2" title="{{ __('app.view') }}">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                        @if (auth()->user()->canManage())
                                            <a href="{{ route('movements.create', ['product_id' => $product->id]) }}" class="btn-ghost !p-2" title="{{ __('movements.new') }}">
                                                <x-icon name="arrows" class="h-4 w-4" />
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}" class="btn-ghost !p-2" title="{{ __('app.edit') }}">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </a>
                                            <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="{{ __('products.delete_confirm') }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-ghost !p-2 text-red-500 hover:bg-red-500/10" title="{{ __('app.delete') }}">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $products->links() }}
        </div>
    @endif
@endsection
