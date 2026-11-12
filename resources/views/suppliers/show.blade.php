@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
    <x-page-header :title="$supplier->name" :subtitle="__('suppliers.details')" icon="truck">
        <a href="{{ route('suppliers.index') }}" class="btn-ghost">
            <x-icon name="chevron-left" class="h-4 w-4" /> {{ __('app.back') }}
        </a>
        @if (auth()->user()->canManage())
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-primary">
                <x-icon name="pencil" class="h-4 w-4" /> {{ __('app.edit') }}
            </a>
        @endif
    </x-page-header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        {{-- Articles fournis --}}
        <div class="card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between gap-3 px-6 pt-6">
                <h2 class="text-sm font-semibold text-ink-800 dark:text-white">{{ __('suppliers.supplied_products') }}</h2>
                <span class="badge-neutral">{{ $supplier->products->count() }}</span>
            </div>

            @if ($supplier->products->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-ink-400">{{ __('suppliers.no_products') }}</p>
            @else
                <div class="table-wrap mt-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('products.name') }}</th>
                                <th class="hidden sm:table-cell">{{ __('products.sku') }}</th>
                                <th class="text-right">{{ __('products.total_stock') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($supplier->products as $product)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-100 text-ink-500 dark:bg-white/10">
                                                <x-icon name="box" class="h-4 w-4" />
                                            </div>
                                            <a href="{{ route('products.show', $product) }}" class="font-medium text-ink-800 hover:text-accent-500 dark:text-ink-100">
                                                {{ $product->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="hidden sm:table-cell">
                                        <span class="font-mono text-xs text-ink-400">{{ $product->sku }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ $product->stocks_count }}</span>
                                        <span class="text-xs text-ink-400">{{ $product->unit }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Coordonnées --}}
        <div class="space-y-5">
            <div class="card p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-ink-800 dark:text-white">{{ __('app.details') }}</h2>
                    @if ($supplier->is_active)
                        <span class="badge-success">{{ __('app.active') }}</span>
                    @else
                        <span class="badge-neutral">{{ __('app.inactive') }}</span>
                    @endif
                </div>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('suppliers.contact_name') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $supplier->contact_name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('suppliers.email') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">
                            @if ($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" class="link break-all">{{ $supplier->email }}</a>
                            @else — @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('suppliers.phone') }}</dt>
                        <dd class="text-right font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ $supplier->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('suppliers.address') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $supplier->address ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('suppliers.city') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $supplier->city ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('suppliers.country') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $supplier->country ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('suppliers.website') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">
                            @if ($supplier->website)
                                <a href="{{ $supplier->website }}" target="_blank" rel="noopener" class="link break-all">{{ $supplier->website }}</a>
                            @else — @endif
                        </dd>
                    </div>
                </dl>
            </div>

            @if ($supplier->notes)
                <div class="card p-6">
                    <h2 class="mb-2 text-sm font-semibold text-ink-800 dark:text-white">{{ __('suppliers.notes') }}</h2>
                    <p class="text-sm whitespace-pre-line text-ink-500 dark:text-ink-400">{{ $supplier->notes }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
