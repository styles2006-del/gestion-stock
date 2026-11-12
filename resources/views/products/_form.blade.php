@php
    $currency = \App\Models\Setting::getValue('currency', 'EUR');
    $product = $product ?? new \App\Models\Product(['unit' => 'pcs', 'is_active' => true, 'min_stock' => 0]);
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">
        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('app.overview') }}</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="label">{{ __('products.name') }} *</label>
                    <input id="name" name="name" value="{{ old('name', $product->name) }}" class="input" required>
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="sku" class="label">{{ __('products.sku') }} *</label>
                    <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" class="input" required placeholder="{{ __('products.sku_placeholder') }}">
                    @error('sku') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="barcode" class="label">{{ __('products.barcode') }}</label>
                    <input id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="input">
                    <p class="mt-1 text-xs text-ink-400">{{ __('products.barcode_hint') }}</p>
                    @error('barcode') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="category_id" class="label">{{ __('products.category') }}</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="supplier_id" class="label">{{ __('products.supplier') }}</label>
                    <select id="supplier_id" name="supplier_id" class="input">
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="label">{{ __('products.description') }}</label>
                    <textarea id="description" name="description" rows="3" class="input">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('products.purchase_price') }} / {{ __('products.sale_price') }}</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="purchase_price" class="label">{{ __('products.purchase_price') }} * ({{ $currency }})</label>
                    <input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" class="input" required>
                    @error('purchase_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sale_price" class="label">{{ __('products.sale_price') }} * ({{ $currency }})</label>
                    <input id="sale_price" name="sale_price" type="number" step="0.01" min="0" value="{{ old('sale_price', $product->sale_price ?? 0) }}" class="input" required>
                    @error('sale_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('products.min_stock') }}</h2>
            <div class="space-y-4">
                <div>
                    <label for="unit" class="label">{{ __('products.unit') }} *</label>
                    <input id="unit" name="unit" value="{{ old('unit', $product->unit) }}" class="input" required list="units">
                    <datalist id="units">
                        @foreach (['pcs', 'kg', 'g', 'L', 'mL', 'm', 'box', 'pack', 'palette'] as $unit)
                            <option value="{{ $unit }}"></option>
                        @endforeach
                    </datalist>
                    @error('unit') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="min_stock" class="label">{{ __('products.min_stock') }} *</label>
                    <input id="min_stock" name="min_stock" type="number" min="0" value="{{ old('min_stock', $product->min_stock) }}" class="input" required>
                    <p class="mt-1 text-xs text-ink-400">{{ __('products.min_stock_hint') }}</p>
                    @error('min_stock') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card p-6">
            <label class="flex cursor-pointer items-center gap-3">
                <input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $product->is_active))>
                <span class="text-sm font-medium text-ink-700 dark:text-ink-100">{{ __('app.active') }}</span>
            </label>
        </div>
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-2">
    <a href="{{ route('products.index') }}" class="btn-secondary">{{ __('app.cancel') }}</a>
    <button type="submit" class="btn-primary">
        <x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}
    </button>
</div>
