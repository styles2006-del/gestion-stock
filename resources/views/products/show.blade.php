@extends('layouts.app')

@section('title', $product->name)

@section('content')
    @php
        $currency = \App\Models\Setting::getValue('currency', 'EUR');
        $total = $product->stocks->sum('quantity');
        $low = $product->min_stock > 0 && $total <= $product->min_stock;
        $margin = (float) $product->purchase_price > 0
            ? ((float) $product->sale_price - (float) $product->purchase_price) / (float) $product->purchase_price * 100
            : 0;
    @endphp

    <x-page-header :title="$product->name" :subtitle="$product->sku" icon="box">
        <a href="{{ route('products.index') }}" class="btn-ghost">
            <x-icon name="chevron-left" class="h-4 w-4" /> {{ __('app.back') }}
        </a>
        @if (auth()->user()->canManage())
            <a href="{{ route('movements.create', ['product_id' => $product->id]) }}" class="btn-secondary">
                <x-icon name="arrows" class="h-4 w-4" /> {{ __('movements.new') }}
            </a>
            <a href="{{ route('products.edit', $product) }}" class="btn-primary">
                <x-icon name="pencil" class="h-4 w-4" /> {{ __('app.edit') }}
            </a>
        @endif
    </x-page-header>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            {{-- Indicateurs --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="card p-4">
                    <p class="text-xs font-medium text-ink-500 dark:text-ink-400">{{ __('products.total_stock') }}</p>
                    <p class="mt-1.5 text-2xl font-semibold tabular-nums {{ $low ? 'text-red-500' : 'text-ink-800 dark:text-white' }}">{{ $total }}</p>
                    <p class="text-[11px] text-ink-400">{{ $product->unit }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-medium text-ink-500 dark:text-ink-400">{{ __('products.min_stock') }}</p>
                    <p class="mt-1.5 text-2xl font-semibold tabular-nums text-ink-800 dark:text-white">{{ $product->min_stock }}</p>
                    <p class="text-[11px] text-ink-400">
                        @if ($low)
                            <span class="text-red-500">{{ __('products.low_stock') }}</span>
                        @else
                            {{ __('products.in_stock') }}
                        @endif
                    </p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-medium text-ink-500 dark:text-ink-400">{{ __('products.margin') }}</p>
                    <p class="mt-1.5 text-2xl font-semibold tabular-nums text-emerald-500">{{ number_format($margin, 1, ',', ' ') }}%</p>
                    <p class="text-[11px] text-ink-400">{{ number_format($product->sale_price - $product->purchase_price, 2, ',', ' ') }} {{ $currency }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-medium text-ink-500 dark:text-ink-400">{{ __('products.stock_value') }}</p>
                    <p class="mt-1.5 text-2xl font-semibold tabular-nums text-ink-800 dark:text-white">{{ number_format($total * $product->purchase_price, 0, ',', ' ') }}</p>
                    <p class="text-[11px] text-ink-400">{{ $currency }}</p>
                </div>
            </div>

            {{-- Stock par dépôt --}}
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('products.stock_by_warehouse') }}</h2>
                @forelse ($product->stocks as $stock)
                    @php $pct = $total > 0 ? round($stock->quantity / $total * 100) : 0; @endphp
                    <div class="mb-3 last:mb-0">
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-ink-700 dark:text-ink-100">
                                <x-icon name="warehouse" class="h-4 w-4 text-ink-400" />
                                {{ $stock->warehouse?->name }}
                            </span>
                            <span class="font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ $stock->quantity }} {{ $product->unit }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-black/5 dark:bg-white/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-accent-400 to-accent-600" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-ink-400">{{ __('products.no_stock') }}</p>
                @endforelse
            </div>

            {{-- Historique --}}
            <div class="card overflow-hidden">
                <h2 class="px-6 pt-6 text-sm font-semibold text-ink-800 dark:text-white">{{ __('products.movement_history') }}</h2>
                <div class="table-wrap mt-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('app.date') }}</th>
                                <th>{{ __('movements.type') }}</th>
                                <th class="hidden sm:table-cell">{{ __('movements.warehouse') }}</th>
                                <th class="text-right">{{ __('movements.quantity') }}</th>
                                <th class="hidden md:table-cell">{{ __('movements.reference') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr>
                                    <td class="text-xs text-ink-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td><x-movement-type :type="$movement->type" /></td>
                                    <td class="hidden sm:table-cell text-ink-500">{{ $movement->warehouse?->name }}</td>
                                    <td class="text-right font-medium tabular-nums">
                                        <span class="text-ink-800 dark:text-ink-100">{{ $movement->quantity_before }}</span>
                                        <span class="text-ink-300">→</span>
                                        <span class="{{ $movement->quantity_after > $movement->quantity_before ? 'text-emerald-500' : 'text-red-500' }}">{{ $movement->quantity_after }}</span>
                                    </td>
                                    <td class="hidden md:table-cell text-xs text-ink-400">{{ $movement->reference }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-sm text-ink-400">{{ __('movements.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Colonne latérale : infos + codes --}}
        <div class="space-y-5">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('app.details') }}</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">{{ __('products.category') }}</dt>
                        <dd class="font-medium text-ink-800 dark:text-ink-100">{{ $product->category?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">{{ __('products.supplier') }}</dt>
                        <dd class="font-medium text-ink-800 dark:text-ink-100">
                            @if ($product->supplier)
                                <a href="{{ route('suppliers.show', $product->supplier) }}" class="link">{{ $product->supplier->name }}</a>
                            @else — @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">{{ __('products.purchase_price') }}</dt>
                        <dd class="font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ number_format($product->purchase_price, 2, ',', ' ') }} {{ $currency }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">{{ __('products.sale_price') }}</dt>
                        <dd class="font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ number_format($product->sale_price, 2, ',', ' ') }} {{ $currency }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-ink-500 dark:text-ink-400">{{ __('app.status') }}</dt>
                        <dd>
                            @if ($product->is_active)
                                <span class="badge-success">{{ __('app.active') }}</span>
                            @else
                                <span class="badge-neutral">{{ __('app.inactive') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
                @if ($product->description)
                    <p class="mt-4 border-t border-black/5 pt-4 text-sm text-ink-500 dark:border-white/10 dark:text-ink-400">{{ $product->description }}</p>
                @endif
            </div>

            {{-- Codes-barres et QR --}}
            <div class="card p-6">
                <h2 class="mb-1 text-sm font-semibold text-ink-800 dark:text-white">{{ __('products.print_labels') }}</h2>
                <p class="mb-4 text-xs text-ink-400">{{ __('products.scan_hint') }}</p>

                @if ($barcodeSvg)
                    <div class="rounded-xl border border-black/5 bg-white p-4 dark:border-white/10">
                        <div class="[&_svg]:h-16 [&_svg]:w-full">{!! $barcodeSvg !!}</div>
                        <p class="mt-2 text-center font-mono text-xs tracking-widest text-ink-500">{{ $product->barcode }}</p>
                    </div>
                @endif

                <div class="mt-4 flex items-center gap-4">
                    <div class="h-28 w-28 shrink-0 rounded-xl border border-black/5 bg-white p-2 dark:border-white/10 [&_svg]:h-full [&_svg]:w-full">
                        {!! $qrSvg !!}
                    </div>
                    <div class="text-xs text-ink-500 dark:text-ink-400">
                        <p class="flex items-center gap-1.5 font-medium text-ink-700 dark:text-ink-200">
                            <x-icon name="qrcode" class="h-4 w-4" /> {{ __('products.barcode') }} QR
                        </p>
                        <p class="mt-1">{{ __('products.scan_hint') }}</p>
                    </div>
                </div>

                <button type="button" onclick="window.print()" class="btn-secondary mt-4 w-full">
                    <x-icon name="printer" class="h-4 w-4" /> {{ __('app.print') }}
                </button>
            </div>
        </div>
    </div>
@endsection
