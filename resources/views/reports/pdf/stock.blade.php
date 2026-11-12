<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.stock_report') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; color: #1d1d1f; font-size: 10px; margin: 0; }
        .head { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .head td { vertical-align: top; }
        .org-name { font-size: 16px; font-weight: 700; margin: 0 0 3px; }
        .muted { color: #6e6e73; margin: 0; line-height: 1.5; }
        .title { text-align: right; }
        .title h1 { font-size: 16px; margin: 0 0 3px; text-transform: uppercase; letter-spacing: .04em; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data thead th { text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: #8e8e93; border-bottom: 2px solid #1d1d1f; padding: 6px 7px; }
        table.data tbody td { padding: 5px 7px; border-bottom: 1px solid #e8e8ed; }
        table.data .num { text-align: right; white-space: nowrap; }
        tr.low td { background: #fdecec; }
        table.data tfoot td { padding: 8px 7px; border-top: 2px solid #1d1d1f; font-size: 12px; font-weight: 700; }
        .total { color: #0071e3; text-align: right; }
    </style>
</head>
<body>
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
            <td class="title">
                <h1>{{ __('reports.stock_report') }}</h1>
                <p class="muted">{{ __('reports.generated_on', ['date' => $date]) }}</p>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>{{ __('reports.sku') }}</th>
                <th>{{ __('reports.product') }}</th>
                <th>{{ __('reports.category') }}</th>
                <th>{{ __('reports.warehouse') }}</th>
                <th class="num">{{ __('reports.quantity') }}</th>
                <th class="num">{{ __('reports.min_stock') }}</th>
                <th class="num">{{ __('reports.purchase_price') }}</th>
                <th class="num">{{ __('reports.value') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr @class(['low' => $row['low']])>
                    <td>{{ $row['sku'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['warehouse'] }}</td>
                    <td class="num">{{ $row['quantity'] }} {{ $row['unit'] }}</td>
                    <td class="num">{{ $row['min_stock'] }}</td>
                    <td class="num">{{ number_format((float) $row['purchase_price'], 2, ',', ' ') }}</td>
                    <td class="num">{{ number_format((float) $row['value'], 2, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:16px; color:#8e8e93">{{ __('reports.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align:right">{{ __('reports.stock_value') }}</td>
                <td class="total">{{ number_format((float) $totalValue, 2, ',', ' ') }} {{ $org['currency'] }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
