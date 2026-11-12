@php
    $index = $index ?? 0;
    $line = $line ?? ['product_id' => '', 'quantity' => 1, 'unit_price' => ''];
    $selectedProduct = (int) ($line['product_id'] ?? 0);
    $quantity = $line['quantity'] ?? 1;
    $unitPrice = $line['unit_price'] ?? '';
    $hasPrice = $unitPrice !== '' && $unitPrice !== null && (float) $unitPrice > 0;
    $lineTotal = round((float) $quantity * (float) ($unitPrice ?: 0), 2);
@endphp

<div data-line class="grid grid-cols-12 items-center gap-3 border-t border-black/5 py-3 first:border-t-0 dark:border-white/10">
    <div class="col-span-12 sm:col-span-5">
        <select data-product-select data-name="lines[{{ $index }}][product_id]" class="input">
            <option value="">{{ __('app.none') }}</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" data-price="{{ $priceFor($product) }}" @selected($selectedProduct === $product->id)>
                    {{ $product->name }}@if ($product->sku) — {{ $product->sku }}@endif
                </option>
            @endforeach
        </select>
        @error('lines.' . $index . '.product_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-4 sm:col-span-2">
        <input data-qty data-name="lines[{{ $index }}][quantity]" type="number" min="1" step="1"
               value="{{ $quantity }}" class="input text-right" placeholder="{{ __('documents.line_quantity') }}">
        @error('lines.' . $index . '.quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-4 sm:col-span-2">
        <input data-price data-name="lines[{{ $index }}][unit_price]" type="number" step="0.01" min="0"
               value="{{ $unitPrice }}" class="input text-right" placeholder="{{ __('documents.line_unit_price') }}"
               @if ($hasPrice) data-auto="false" @endif>
        @error('lines.' . $index . '.unit_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    <div class="col-span-3 sm:col-span-2 text-right">
        <span data-line-total class="block font-medium tabular-nums text-ink-800 dark:text-ink-100">
            {{ number_format($lineTotal, 2, ',', ' ') }}
        </span>
        <span class="text-[11px] text-ink-400">{{ $currency }}</span>
    </div>

    <div class="col-span-1 flex justify-end">
        <button type="button" data-remove-line class="btn-ghost !p-2 text-red-500 hover:bg-red-500/10" title="{{ __('app.remove_line') }}">
            <x-icon name="x" class="h-4 w-4" />
        </button>
    </div>
</div>
