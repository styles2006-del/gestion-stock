@php
    $currency = \App\Models\Setting::getValue('currency', 'EUR');
    $isSaleType = in_array($document->type, [\App\Models\Document::TYPE_INVOICE, \App\Models\Document::TYPE_DELIVERY_NOTE], true);
    $priceFor = fn ($product) => $isSaleType ? $product->sale_price : $product->purchase_price;

    $lines = old('lines');
    if ($lines === null) {
        $lines = $document->exists
            ? $document->lines->map(fn ($l) => [
                'product_id' => $l->product_id,
                'quantity' => $l->quantity,
                'unit_price' => (float) $l->unit_price,
            ])->values()->all()
            : [];
    }
    if (empty($lines)) {
        $lines = [['product_id' => '', 'quantity' => 1, 'unit_price' => '']];
    }

    $taxRateValue = old('tax_rate', $document->tax_rate ?? \App\Models\Setting::getValue('tax_rate', 20));
    $subtotalValue = collect($lines)->sum(fn ($l) => (float) ($l['quantity'] ?? 0) * (float) ($l['unit_price'] ?? 0));
    $taxValue = round($subtotalValue * ((float) $taxRateValue / 100), 2);
    $totalValue = $subtotalValue + $taxValue;
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        {{-- Informations générales --}}
        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('app.overview') }}</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="type" class="label">{{ __('documents.type') }} *</label>
                    @if ($document->exists)
                        <input id="type" value="{{ __('documents.types.' . $document->type) }}" class="input" disabled>
                    @else
                        <select id="type" name="type" class="input" required>
                            @foreach (\App\Models\Document::TYPES as $type)
                                <option value="{{ $type }}" @selected(old('type', $document->type) === $type)>{{ __('documents.types.' . $type) }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="warehouse_id" class="label">{{ __('documents.warehouse') }}</label>
                    <select id="warehouse_id" name="warehouse_id" class="input">
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $document->warehouse_id) == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="supplier_id" class="label">{{ __('documents.supplier') }}</label>
                    <select id="supplier_id" name="supplier_id" class="input">
                        <option value="">{{ __('app.none') }}</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $document->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="client_name" class="label">{{ __('documents.client_name') }}</label>
                    <input id="client_name" name="client_name" value="{{ old('client_name', $document->client_name) }}" class="input">
                    @error('client_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="client_address" class="label">{{ __('documents.client_address') }}</label>
                    <input id="client_address" name="client_address" value="{{ old('client_address', $document->client_address) }}" class="input">
                    @error('client_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Lignes --}}
        <div class="card p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-ink-800 dark:text-white">{{ __('documents.lines') }}</h2>
                <button type="button" id="add-line" class="btn-secondary btn-sm">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('app.add_line') }}
                </button>
            </div>

            {{-- En-têtes --}}
            <div class="hidden grid-cols-12 gap-3 px-0 pb-2 text-[11px] font-semibold tracking-wide text-ink-400 uppercase sm:grid">
                <div class="col-span-5">{{ __('documents.line_product') }}</div>
                <div class="col-span-2 text-right">{{ __('documents.line_quantity') }}</div>
                <div class="col-span-2 text-right">{{ __('documents.line_unit_price') }}</div>
                <div class="col-span-2 text-right">{{ __('documents.line_total') }}</div>
                <div class="col-span-1"></div>
            </div>

            <div id="lines-container">
                @foreach ($lines as $index => $line)
                    @include('documents._line', ['index' => $index, 'line' => $line])
                @endforeach
            </div>

            {{-- Gabarit pour les nouvelles lignes --}}
            <template id="line-template">
                @include('documents._line', ['index' => '__INDEX__', 'line' => ['product_id' => '', 'quantity' => 1, 'unit_price' => '']])
            </template>

            @error('lines') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Colonne latérale --}}
    <div class="space-y-5">
        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('app.details') }}</h2>

            <div class="space-y-4">
                <div>
                    <label for="status" class="label">{{ __('documents.status') }} *</label>
                    <select id="status" name="status" class="input" required>
                        @foreach (\App\Models\Document::STATUSES as $status)
                            <option value="{{ $status }}" @selected(old('status', $document->status) === $status)>{{ __('documents.statuses.' . $status) }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="document_date" class="label">{{ __('documents.document_date') }} *</label>
                    <input id="document_date" name="document_date" type="date" class="input" required
                           value="{{ old('document_date', optional($document->document_date)->format('Y-m-d') ?? now()->toDateString()) }}">
                    @error('document_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="due_date" class="label">{{ __('documents.due_date') }}</label>
                    <input id="due_date" name="due_date" type="date" class="input"
                           value="{{ old('due_date', optional($document->due_date)->format('Y-m-d')) }}">
                    @error('due_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tax_rate" class="label">{{ __('app.tax_rate') }}</label>
                    <input id="tax_rate" name="tax_rate" type="number" step="0.01" min="0" max="100" class="input"
                           value="{{ $taxRateValue }}">
                    @error('tax_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <input type="hidden" name="currency" value="{{ old('currency', $document->currency ?? $currency) }}">

                <div>
                    <label for="notes" class="label">{{ __('documents.notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $document->notes) }}</textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Récapitulatif --}}
        <div class="card p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('reports.summary') }}</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-ink-500 dark:text-ink-400">{{ __('documents.subtotal') }}</dt>
                    <dd class="font-medium tabular-nums text-ink-800 dark:text-ink-100">
                        <span id="summary-subtotal">{{ number_format($subtotalValue, 2, ',', ' ') }}</span> {{ $currency }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-ink-500 dark:text-ink-400">{{ __('documents.tax') }}</dt>
                    <dd class="font-medium tabular-nums text-ink-800 dark:text-ink-100">
                        <span id="summary-tax">{{ number_format($taxValue, 2, ',', ' ') }}</span> {{ $currency }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-black/5 pt-3 dark:border-white/10">
                    <dt class="font-semibold text-ink-800 dark:text-white">{{ __('documents.grand_total') }}</dt>
                    <dd class="text-lg font-semibold tabular-nums text-accent-500">
                        <span id="summary-total">{{ number_format($totalValue, 2, ',', ' ') }}</span> {{ $currency }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-2">
    <a href="{{ $document->exists ? route('documents.show', $document) : route('documents.index') }}" class="btn-secondary">
        {{ __('app.cancel') }}
    </a>
    <button type="submit" class="btn-primary">
        <x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}
    </button>
</div>
