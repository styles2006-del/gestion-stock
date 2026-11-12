<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.movements_report') }}</title>
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
        .pos { color: #059669; font-weight: 700; }
        .neg { color: #dc2626; font-weight: 700; }
        .foot { margin-top: 12px; font-size: 10px; color: #6e6e73; }
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
                <h1>{{ __('reports.movements_report') }}</h1>
                <p class="muted">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</p>
                <p class="muted">{{ __('reports.generated_on', ['date' => $date]) }}</p>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>{{ __('reports.date') }}</th>
                <th>{{ __('reports.type') }}</th>
                <th>{{ __('reports.sku') }}</th>
                <th>{{ __('reports.product') }}</th>
                <th>{{ __('reports.warehouse') }}</th>
                <th class="num">{{ __('movements.variation') }}</th>
                <th class="num">{{ __('movements.before') }} / {{ __('movements.after') }}</th>
                <th>{{ __('app.reference') }}</th>
                <th>{{ __('reports.user') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $m)
                @php $delta = $m->quantity_after - $m->quantity_before; @endphp
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ __('movements.types.' . $m->type) }}</td>
                    <td>{{ $m->product?->sku }}</td>
                    <td>{{ $m->product?->name }}</td>
                    <td>{{ $m->warehouse?->name }}</td>
                    <td class="num {{ $delta > 0 ? 'pos' : ($delta < 0 ? 'neg' : '') }}">{{ $delta > 0 ? '+' : '' }}{{ $delta }}</td>
                    <td class="num">{{ $m->quantity_before }} / {{ $m->quantity_after }}</td>
                    <td>{{ $m->reference }}</td>
                    <td>{{ $m->user?->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:16px; color:#8e8e93">{{ __('reports.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="foot">{{ __('app.results_count', ['count' => $movements->count()]) }}</p>
</body>
</html>
