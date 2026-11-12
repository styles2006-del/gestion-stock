@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
    @php
        $currency = \App\Models\Setting::getValue('currency', 'EUR');
        $money = fn ($v) => number_format((float) $v, 2, ',', ' ') . ' ' . $currency;
    @endphp

    <x-page-header :title="__('dashboard.welcome', ['name' => auth()->user()->name])"
                   :subtitle="__('dashboard.subtitle')" icon="dashboard">
        @if (auth()->user()->canManage())
            <a href="{{ route('movements.create') }}" class="btn-secondary">
                <x-icon name="arrow-in" class="h-4 w-4" /> {{ __('movements.new') }}
            </a>
            <a href="{{ route('documents.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> {{ __('documents.new') }}
            </a>
        @endif
    </x-page-header>

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('dashboard.total_products')" :value="$totalProducts" icon="box" tone="accent"
                     :href="route('products.index')" />
        <x-stat-card :label="__('dashboard.stock_value')" :value="$money($totalStockValue)" icon="coins" tone="success"
                     :hint="$warehousesCount . ' ' . __('nav.warehouses')" />
        <x-stat-card :label="__('dashboard.low_stock')" :value="$lowStockProducts->count()" icon="alert"
                     :tone="$lowStockProducts->count() > 0 ? 'danger' : 'neutral'" />
        <x-stat-card :label="__('dashboard.movements_today')" :value="$todayMovements" icon="arrows" tone="warning"
                     :href="route('movements.index')" :hint="__('dashboard.in_this_month') . ' : ' . $inThisMonth . ' · ' . __('dashboard.out_this_month') . ' : ' . $outThisMonth" />
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        {{-- Graphique --}}
        <div class="card animate-[var(--animate-fade-up)] p-6 xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('dashboard.movement_chart') }}</h2>
                    <p class="text-xs text-ink-400">{{ __('dashboard.movements_today') }} · {{ $totalMovements }}</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5 text-ink-500 dark:text-ink-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> {{ __('dashboard.entries') }}
                    </span>
                    <span class="flex items-center gap-1.5 text-ink-500 dark:text-ink-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> {{ __('dashboard.exits') }}
                    </span>
                </div>
            </div>

            @php
                $w = 720; $h = 220; $pad = 24;
                $labels = $chartData['labels'];
                $in = $chartData['in'];
                $out = $chartData['out'];
                $max = max(1, max(array_merge($in, $out)));
                $count = count($labels);
                $stepX = $count > 1 ? ($w - $pad * 2) / ($count - 1) : 0;
                $point = function ($value, $index) use ($h, $pad, $max) {
                    $x = $pad + ($index * ($h > 0 ? 1 : 1));
                    return [$x, $h - $pad - ($value / $max) * ($h - $pad * 2)];
                };
                $path = function ($series) use ($point, $stepX, $pad) {
                    $d = '';
                    foreach ($series as $i => $v) {
                        $x = $pad + $i * $stepX;
                        $y = 220 - 24 - ($v / max(1, max(array_merge($series, [1])))) * (220 - 48);
                        $d .= ($i === 0 ? 'M' : 'L') . round($x, 1) . ' ' . round($y, 1) . ' ';
                    }
                    return trim($d);
                };
                $maxAll = $max;
                $pathFor = function ($series) use ($stepX, $pad, $maxAll, $w, $h) {
                    $d = '';
                    foreach ($series as $i => $v) {
                        $x = $pad + $i * $stepX;
                        $y = $h - $pad - ($v / $maxAll) * ($h - $pad * 2);
                        $d .= ($i === 0 ? 'M' : 'L') . round($x, 1) . ' ' . round($y, 1) . ' ';
                    }
                    return trim($d);
                };
                $areaFor = function ($series) use ($pathFor, $stepX, $pad, $w, $h) {
                    $line = $pathFor($series);
                    $lastX = $pad + (count($series) - 1) * $stepX;
                    return $line . ' L' . round($lastX, 1) . ' ' . ($h - $pad) . ' L' . $pad . ' ' . ($h - $pad) . ' Z';
                };
            @endphp

            <div class="mt-4 overflow-hidden">
                <svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-56 w-full" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="gradIn" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgb(16 185 129)" stop-opacity="0.28"/>
                            <stop offset="100%" stop-color="rgb(16 185 129)" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="gradOut" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgb(239 68 68)" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="rgb(239 68 68)" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    @foreach ([0, 0.25, 0.5, 0.75, 1] as $g)
                        @php $y = $pad + $g * ($h - $pad * 2); @endphp
                        <line x1="{{ $pad }}" y1="{{ $y }}" x2="{{ $w - $pad }}" y2="{{ $y }}"
                              stroke="currentColor" class="text-black/5 dark:text-white/10" stroke-width="1"/>
                    @endforeach

                    <path d="{{ $areaFor($in) }}" fill="url(#gradIn)"/>
                    <path d="{{ $pathFor($in) }}" fill="none" stroke="rgb(16 185 129)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                    <path d="{{ $areaFor($out) }}" fill="url(#gradOut)"/>
                    <path d="{{ $pathFor($out) }}" fill="none" stroke="rgb(239 68 68)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="mt-2 flex justify-between px-1 text-[10px] text-ink-400">
                @foreach ($labels as $label)
                    <span>{{ $label }}</span>
                @endforeach
            </div>
        </div>

        {{-- Alertes stock bas --}}
        <div class="card animate-[var(--animate-fade-up)] p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('dashboard.low_stock_title') }}</h2>
                @if ($lowStockProducts->count() > 0)
                    <span class="badge-danger">{{ $lowStockProducts->count() }}</span>
                @endif
            </div>

            <div class="mt-4 space-y-2">
                @forelse ($lowStockProducts->take(6) as $product)
                    <a href="{{ route('products.show', $product) }}"
                       class="flex items-center gap-3 rounded-xl px-2.5 py-2.5 transition hover:bg-black/[0.03] dark:hover:bg-white/[0.05]">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/12 text-amber-500">
                            <x-icon name="alert" class="h-4 w-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink-700 dark:text-ink-100">{{ $product->name }}</p>
                            <p class="text-[11px] text-ink-400">
                                {{ __('dashboard.remaining', ['count' => $product->total_quantity, 'unit' => $product->unit]) }}
                                · {{ __('dashboard.threshold', ['count' => $product->min_stock]) }}
                            </p>
                        </div>
                        <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-ink-300" />
                    </a>
                @empty
                    <div class="flex flex-col items-center py-8 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/12 text-emerald-500">
                            <x-icon name="check" class="h-6 w-6" />
                        </div>
                        <p class="mt-3 text-sm text-ink-500 dark:text-ink-400">{{ __('dashboard.low_stock_empty') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
        {{-- Derniers mouvements --}}
        <div class="card animate-[var(--animate-fade-up)] overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between px-6 pt-6">
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('dashboard.recent_movements') }}</h2>
                <a href="{{ route('movements.index') }}" class="link text-sm">{{ __('dashboard.view_all') }}</a>
            </div>
            <div class="table-wrap mt-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('movements.product') }}</th>
                            <th>{{ __('movements.type') }}</th>
                            <th class="text-right">{{ __('movements.quantity') }}</th>
                            <th class="hidden sm:table-cell">{{ __('movements.warehouse') }}</th>
                            <th class="hidden md:table-cell text-right">{{ __('app.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentMovements as $movement)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-medium text-ink-800 dark:text-ink-100">{{ $movement->product?->name }}</span>
                                        <span class="hidden text-xs text-ink-400 sm:inline">{{ $movement->product?->sku }}</span>
                                    </div>
                                </td>
                                <td><x-movement-type :type="$movement->type" /></td>
                                <td class="text-right font-medium tabular-nums">{{ $movement->quantity }}</td>
                                <td class="hidden sm:table-cell text-ink-500">{{ $movement->warehouse?->name }}</td>
                                <td class="hidden md:table-cell text-right text-xs text-ink-400">{{ $movement->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-sm text-ink-400">{{ __('movements.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Documents en cours --}}
        <div class="card animate-[var(--animate-fade-up)] p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('dashboard.pending_documents') }}</h2>
                <a href="{{ route('documents.index') }}" class="link text-sm">{{ __('dashboard.view_all') }}</a>
            </div>
            <div class="mt-4 space-y-2">
                @forelse ($pendingDocuments as $document)
                    <a href="{{ route('documents.show', $document) }}"
                       class="flex items-center gap-3 rounded-xl px-2.5 py-2.5 transition hover:bg-black/[0.03] dark:hover:bg-white/[0.05]">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-500/12 text-accent-500">
                            <x-icon name="document" class="h-4 w-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink-700 dark:text-ink-100">{{ $document->number }}</p>
                            <p class="truncate text-[11px] text-ink-400">{{ __('documents.types.' . $document->type) }} · {{ $money($document->total) }}</p>
                        </div>
                        <x-document-status :status="$document->status" class="shrink-0" />
                    </a>
                @empty
                    <p class="py-8 text-center text-sm text-ink-400">{{ __('dashboard.no_documents') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
