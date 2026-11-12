@extends('layouts.app')

@section('title', __('movements.new'))

@section('content')
    @php
        $currency = \App\Models\Setting::getValue('currency', 'EUR');
        $currentType = old('type', $selectedType);
        $currentType = in_array($currentType, ['in', 'out', 'adjustment'], true) ? $currentType : 'in';
        $defaultWarehouseId = $warehouses->firstWhere('is_default', true)?->id ?? $warehouses->first()?->id;
    @endphp

    <x-page-header :title="__('movements.new')" :subtitle="__('app.required_fields')" icon="arrows" />

    <form method="POST" action="{{ route('movements.store') }}" class="max-w-3xl">
        @csrf

        {{-- Type de mouvement --}}
        <div class="card p-6">
            <label class="label">{{ __('movements.type') }} *</label>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="type" value="in" class="peer sr-only" @checked($currentType === 'in')>
                    <div class="card flex items-center gap-3 p-4 transition peer-checked:border-emerald-500 peer-checked:ring-2 peer-checked:ring-emerald-500/25 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500/40 hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/12 text-emerald-500">
                            <x-icon name="arrow-in" class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-medium text-ink-800 dark:text-ink-100">{{ __('movements.types.in') }}</span>
                    </div>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" name="type" value="out" class="peer sr-only" @checked($currentType === 'out')>
                    <div class="card flex items-center gap-3 p-4 transition peer-checked:border-red-500 peer-checked:ring-2 peer-checked:ring-red-500/25 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500/40 hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/12 text-red-500">
                            <x-icon name="arrow-out" class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-medium text-ink-800 dark:text-ink-100">{{ __('movements.types.out') }}</span>
                    </div>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" name="type" value="adjustment" class="peer sr-only" @checked($currentType === 'adjustment')>
                    <div class="card flex items-center gap-3 p-4 transition peer-checked:border-amber-500 peer-checked:ring-2 peer-checked:ring-amber-500/25 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500/40 hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-500">
                            <x-icon name="refresh" class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-medium text-ink-800 dark:text-ink-100">{{ __('movements.types.adjustment') }}</span>
                    </div>
                </label>
            </div>
            @error('type') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Détails du mouvement --}}
        <div class="card mt-5 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="product_id" class="label">{{ __('movements.product') }} *</label>
                    <select id="product_id" name="product_id" class="input" required>
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}"
                                    @selected((int) old('product_id', $selectedProduct) === $product->id)>
                                {{ $product->name }}@if ($product->sku) — {{ $product->sku }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="warehouse_id" class="label">{{ __('movements.warehouse') }} *</label>
                    <select id="warehouse_id" name="warehouse_id" class="input" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((int) old('warehouse_id', $defaultWarehouseId) === $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="quantity" class="label">{{ __('movements.quantity') }} *</label>
                    <input id="quantity" name="quantity" type="number" min="0" value="{{ old('quantity', 0) }}" class="input" required>
                    <p class="mt-1 text-xs text-ink-400">{{ __('movements.quantity_hint') }}</p>
                    @error('quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="unit_price" class="label">{{ __('movements.unit_price') }} ({{ $currency }})</label>
                    <input id="unit_price" name="unit_price" type="number" step="0.01" min="0" value="{{ old('unit_price') }}" class="input">
                    @error('unit_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="reference" class="label">{{ __('movements.reference') }}</label>
                    <input id="reference" name="reference" value="{{ old('reference') }}" class="input" placeholder="{{ __('app.optional') }}">
                    @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="note" class="label">{{ __('movements.note') }}</label>
                    <textarea id="note" name="note" rows="3" class="input">{{ old('note') }}</textarea>
                    @error('note') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2">
            <a href="{{ route('movements.index') }}" class="btn-secondary">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}</button>
        </div>
    </form>
@endsection
