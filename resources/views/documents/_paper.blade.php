@php
    $currency = $document->currency ?: ($org['currency'] ?? 'EUR');
    $money = fn ($v) => number_format((float) $v, 2, ',', ' ') . ' ' . $currency;
    $taxLabel = rtrim(rtrim(number_format((float) $document->tax_rate, 2, ',', ' '), '0'), ',');
@endphp

<style>
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; color: #1d1d1f; font-size: 12px; margin: 0; background: #fff; }
    .sheet { max-width: 800px; margin: 0 auto; padding: 36px 40px; }
    .head { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
    .head td { vertical-align: top; }
    .org-name { font-size: 18px; font-weight: 700; margin: 0 0 4px; }
    .muted { color: #6e6e73; margin: 0; line-height: 1.5; }
    .doc-title { text-align: right; }
    .doc-type { font-size: 20px; font-weight: 700; margin: 0 0 4px; text-transform: uppercase; letter-spacing: .04em; }
    .doc-number { font-size: 13px; font-weight: 600; color: #0071e3; margin: 0 0 6px; }
    .badge { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #eef2f7; color: #363638; }
    .cols { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .cols td { width: 50%; vertical-align: top; padding-right: 18px; }
    .cols td:last-child { padding-right: 0; }
    .box { border: 1px solid #e8e8ed; border-radius: 10px; padding: 12px 14px; }
    .box-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #8e8e93; margin: 0 0 6px; }
    .box-name { font-weight: 700; margin: 0 0 3px; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.items thead th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #8e8e93; border-bottom: 2px solid #1d1d1f; padding: 8px 10px; }
    table.items tbody td { padding: 9px 10px; border-bottom: 1px solid #e8e8ed; }
    table.items .num { text-align: right; white-space: nowrap; }
    table.totals { width: 46%; margin-left: 54%; border-collapse: collapse; margin-top: 8px; }
    table.totals td { padding: 7px 10px; }
    table.totals .num { text-align: right; white-space: nowrap; }
    table.totals tr.grand td { border-top: 2px solid #1d1d1f; font-size: 15px; font-weight: 700; color: #0071e3; }
    .note { margin-top: 26px; padding: 12px 14px; border: 1px solid #e8e8ed; border-radius: 10px; }
    .note-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #8e8e93; margin: 0 0 5px; }
    .sign { margin-top: 40px; width: 100%; border-collapse: collapse; }
    .sign td { width: 50%; vertical-align: bottom; }
    .sign .line { margin-top: 46px; border-top: 1px solid #aeaeb2; padding-top: 6px; font-size: 11px; color: #8e8e93; }
    .sign .right { text-align: right; }
</style>

<div class="sheet">
    <table class="head">
        <tr>
            <td>
                <p class="org-name">{{ $org['name'] }}</p>
                @if ($org['address'])<p class="muted">{{ $org['address'] }}</p>@endif
                <p class="muted">
                    @if ($org['phone']){{ $org['phone'] }}@endif
                    @if ($org['phone'] && $org['email']) · @endif
                    @if ($org['email']){{ $org['email'] }}@endif
                </p>
            </td>
            <td class="doc-title">
                <p class="doc-type">{{ __('documents.types.' . $document->type) }}</p>
                <p class="doc-number">{{ $document->number }}</p>
                <span class="badge">{{ __('documents.statuses.' . $document->status) }}</span>
            </td>
        </tr>
    </table>

    <table class="cols">
        <tr>
            <td>
                <div class="box">
                    <p class="box-title">{{ __('documents.document_date') }}</p>
                    <p class="box-name">{{ optional($document->document_date)->format('d/m/Y') ?? '—' }}</p>
                    @if ($document->due_date)
                        <p class="muted" style="margin-top:6px">{{ __('documents.due_date') }} : {{ $document->due_date->format('d/m/Y') }}</p>
                    @endif
                    @if ($document->warehouse)
                        <p class="muted" style="margin-top:6px">{{ __('documents.warehouse') }} : {{ $document->warehouse->name }}</p>
                    @endif
                </div>
            </td>
            <td>
                @if ($document->client_name || $document->client_address)
                    <div class="box">
                        <p class="box-title">{{ __('documents.bill_to') }}</p>
                        <p class="box-name">{{ $document->client_name }}</p>
                        @if ($document->client_address)<p class="muted" style="white-space:pre-line">{{ $document->client_address }}</p>@endif
                    </div>
                @elseif ($document->supplier)
                    <div class="box">
                        <p class="box-title">{{ __('documents.supplier') }}</p>
                        <p class="box-name">{{ $document->supplier->name }}</p>
                        @if ($document->supplier->address)<p class="muted">{{ $document->supplier->address }}</p>@endif
                        @if ($document->supplier->city)<p class="muted">{{ $document->supplier->city }}</p>@endif
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>{{ __('documents.line_product') }}</th>
                <th class="num">{{ __('documents.line_quantity') }}</th>
                <th class="num">{{ __('documents.line_unit_price') }}</th>
                <th class="num">{{ __('documents.line_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($document->lines as $line)
                <tr>
                    <td>
                        {{ $line->product?->name ?? '—' }}
                        @if ($line->product?->sku)
                            <span class="muted" style="font-size:10px"> — {{ $line->product->sku }}</span>
                        @endif
                    </td>
                    <td class="num">{{ $line->quantity }} {{ $line->product?->unit }}</td>
                    <td class="num">{{ $money($line->unit_price) }}</td>
                    <td class="num">{{ $money($line->total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:18px; color:#8e8e93">{{ __('documents.no_lines') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="muted">{{ __('documents.subtotal') }}</td>
            <td class="num">{{ $money($document->subtotal) }}</td>
        </tr>
        <tr>
            <td class="muted">{{ __('documents.tax') }} ({{ $taxLabel }} %)</td>
            <td class="num">{{ $money($document->tax_amount) }}</td>
        </tr>
        <tr class="grand">
            <td>{{ __('documents.grand_total') }}</td>
            <td class="num">{{ $money($document->total) }}</td>
        </tr>
    </table>

    @if ($document->notes)
        <div class="note">
            <p class="note-title">{{ __('documents.notes') }}</p>
            <p class="muted" style="white-space:pre-line; margin:0">{{ $document->notes }}</p>
        </div>
    @endif

    <table class="sign">
        <tr>
            <td>
                <p class="muted" style="margin:0">{{ __('documents.issued_by') }} : {{ $document->user?->name ?? '—' }}</p>
            </td>
            <td class="right">
                <p class="muted" style="margin:0">{{ __('documents.signature') }}</p>
                <div class="line right">{{ $org['name'] }}</div>
            </td>
        </tr>
    </table>

    <p class="muted" style="text-align:center; margin-top:28px; font-size:10px">{{ __('documents.thank_you') }}</p>
</div>
