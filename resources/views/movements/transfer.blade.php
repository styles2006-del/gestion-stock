@extends('layouts.app')

@section('title', __('movements.transfer_title'))

@section('content')
    <x-page-header :title="__('movements.transfer_title')" :subtitle="__('movements.transfer_subtitle')" icon="transfer" />

    <form method="POST" action="{{ route('movements.transfer') }}" class="max-w-3xl">
        @csrf

        {{-- Aperçu source → destination --}}
        <div class="card p-6">
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 sm:gap-5">
                <div class="rounded-xl border border-dashed border-black/10 bg-ink-50/60 p-4 text-center dark:border-white/15 dark:bg-white/5">
                    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/15 text-amber-500">
                        <x-icon name="arrow-out" class="h-5 w-5" />
                    </span>
                    <p class="mt-2 text-[11px] font-semibold tracking-wide text-ink-400 uppercase">{{ __('movements.from_warehouse') }}</p>
                </div>

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-500/12 text-accent-500">
                    <x-icon name="transfer" class="h-5 w-5" />
                </span>

                <div class="rounded-xl border border-dashed border-black/10 bg-ink-50/60 p-4 text-center dark:border-white/15 dark:bg-white/5">
                    <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/12 text-emerald-500">
                        <x-icon name="arrow-in" class="h-5 w-5" />
                    </span>
                    <p class="mt-2 text-[11px] font-semibold tracking-wide text-ink-400 uppercase">{{ __('movements.to_warehouse') }}</p>
                </div>
            </div>
        </div>

        {{-- Détails du transfert --}}
        <div class="card mt-5 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="product_id" class="label">{{ __('movements.product') }} *</label>
                    <select id="product_id" name="product_id" class="input" required>
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }}@if ($product->sku) — {{ $product->sku }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="from_warehouse_id" class="label">{{ __('movements.from_warehouse') }} *</label>
                    <select id="from_warehouse_id" name="from_warehouse_id" class="input" required>
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('from_warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="to_warehouse_id" class="label">{{ __('movements.to_warehouse') }} *</label>
                    <select id="to_warehouse_id" name="to_warehouse_id" class="input" required>
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('to_warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="quantity" class="label">{{ __('movements.quantity') }} *</label>
                    <input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" class="input" required>
                    @error('quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
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
            <button type="submit" class="btn-primary"><x-icon name="transfer" class="h-4 w-4" /> {{ __('app.save') }}</button>
        </div>
    </form>
@endsection
