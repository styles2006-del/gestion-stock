@extends('layouts.app')

@section('title', $document->number)

@section('content')
    @php
        $currency = $document->currency ?: $org['currency'];
        $money = fn ($v) => number_format((float) $v, 2, ',', ' ') . ' ' . $currency;
    @endphp

    <x-page-header :title="$document->number" :subtitle="__('documents.types.' . $document->type)" icon="document">
        <a href="{{ route('documents.index') }}" class="btn-ghost">
            <x-icon name="chevron-left" class="h-4 w-4" /> {{ __('app.back') }}
        </a>
        <a href="{{ route('documents.print', $document) }}" target="_blank" class="btn-secondary">
            <x-icon name="printer" class="h-4 w-4" /> {{ __('app.print') }}
        </a>
        <a href="{{ route('documents.pdf', $document) }}" class="btn-secondary">
            <x-icon name="download" class="h-4 w-4" /> {{ __('app.export_pdf') }}
        </a>
        @if (auth()->user()->canManage())
            @if (! $document->stock_effect_done)
                <form method="POST" action="{{ route('documents.apply-stock', $document) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        <x-icon name="arrows" class="h-4 w-4" /> {{ __('documents.apply_stock') }}
                    </button>
                </form>
            @endif
            <a href="{{ route('documents.edit', $document) }}" class="btn-primary">
                <x-icon name="pencil" class="h-4 w-4" /> {{ __('app.edit') }}
            </a>
        @endif
    </x-page-header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            {{-- Lignes du document --}}
            <div class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-black/5 px-6 py-4 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <x-document-status :status="$document->status" />
                        @if ($document->stock_effect_done)
                            <span class="badge-success"><x-icon name="check" class="h-3.5 w-3.5" /> {{ __('documents.stock_applied') }}</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] font-semibold tracking-wide text-ink-400 uppercase">{{ __('app.total') }}</p>
                        <p class="text-lg font-semibold tabular-nums text-ink-800 dark:text-white">{{ $money($document->total) }}</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('documents.line_product') }}</th>
                                <th class="text-right">{{ __('documents.line_quantity') }}</th>
                                <th class="text-right">{{ __('documents.line_unit_price') }}</th>
                                <th class="text-right">{{ __('documents.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($document->lines as $line)
                                <tr>
                                    <td>
                                        <p class="font-medium text-ink-800 dark:text-ink-100">{{ $line->product?->name ?? '—' }}</p>
                                        @if ($line->product?->sku)
                                            <p class="text-[11px] text-ink-400">{{ $line->product->sku }}</p>
                                        @endif
                                    </td>
                                    <td class="text-right tabular-nums text-ink-500">
                                        {{ $line->quantity }}
                                        <span class="text-xs">{{ $line->product?->unit }}</span>
                                    </td>
                                    <td class="text-right tabular-nums text-ink-500">{{ $money($line->unit_price) }}</td>
                                    <td class="text-right font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ $money($line->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-10 text-center text-sm text-ink-400">{{ __('documents.no_lines') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Totaux --}}
                <div class="flex justify-end border-t border-black/5 px-6 py-5 dark:border-white/10">
                    <dl class="w-full max-w-xs space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-500 dark:text-ink-400">{{ __('documents.subtotal') }}</dt>
                            <dd class="font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ $money($document->subtotal) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-500 dark:text-ink-400">{{ __('documents.tax') }} ({{ rtrim(rtrim(number_format((float) $document->tax_rate, 2, ',', ' '), '0'), ',') }} %)</dt>
                            <dd class="font-medium tabular-nums text-ink-800 dark:text-ink-100">{{ $money($document->tax_amount) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-black/5 pt-3 dark:border-white/10">
                            <dt class="font-semibold text-ink-800 dark:text-white">{{ __('documents.grand_total') }}</dt>
                            <dd class="text-lg font-semibold tabular-nums text-accent-500">{{ $money($document->total) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ($document->notes)
                <div class="card p-6">
                    <h2 class="mb-2 text-sm font-semibold text-ink-800 dark:text-white">{{ __('documents.notes') }}</h2>
                    <p class="text-sm whitespace-pre-line text-ink-500 dark:text-ink-400">{{ $document->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Colonne latérale --}}
        <div class="space-y-5">
            <div class="card p-6">
                <h2 class="mb-4 text-sm font-semibold text-ink-800 dark:text-white">{{ __('app.details') }}</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('documents.type') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ __('documents.types.' . $document->type) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('documents.document_date') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ optional($document->document_date)->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('documents.due_date') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ optional($document->due_date)->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('documents.warehouse') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $document->warehouse?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('documents.supplier') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $document->supplier?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('documents.issued_by') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $document->user?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="shrink-0 text-ink-500 dark:text-ink-400">{{ __('app.created_at') }}</dt>
                        <dd class="text-right font-medium text-ink-800 dark:text-ink-100">{{ $document->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            @if ($document->client_name || $document->client_address)
                <div class="card p-6">
                    <h2 class="mb-2 text-sm font-semibold text-ink-800 dark:text-white">{{ __('documents.bill_to') }}</h2>
                    <p class="text-sm font-medium text-ink-800 dark:text-ink-100">{{ $document->client_name }}</p>
                    @if ($document->client_address)
                        <p class="mt-1 text-sm whitespace-pre-line text-ink-500 dark:text-ink-400">{{ $document->client_address }}</p>
                    @endif
                </div>
            @endif

            @if (auth()->user()->canManage())
                <form method="POST" action="{{ route('documents.destroy', $document) }}"
                      data-confirm="{{ __('documents.delete_confirm') }}" class="card p-3">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger w-full">
                        <x-icon name="trash" class="h-4 w-4" /> {{ __('app.delete') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
