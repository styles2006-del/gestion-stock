<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->number }} · {{ __('documents.types.' . $document->type) }}</title>
    <style>
        body { background: #f5f5f7; }
        .toolbar {
            position: sticky; top: 0; z-index: 10; display: flex; justify-content: center; gap: 10px;
            padding: 14px; background: rgba(255, 255, 255, .8); backdrop-filter: blur(12px);
            border-bottom: 1px solid #e8e8ed;
        }
        .toolbar button, .toolbar a {
            display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
            border: 0; border-radius: 999px; padding: 9px 18px; font-size: 13px; font-weight: 600;
            text-decoration: none; font-family: inherit;
        }
        .toolbar button { background: #0071e3; color: #fff; }
        .toolbar a { background: #fff; color: #1d1d1f; box-shadow: inset 0 0 0 1px rgba(0,0,0,.1); }
        .sheet { background: #fff; margin: 24px auto; border-radius: 14px; box-shadow: 0 8px 30px -12px rgba(0,0,0,.18); }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">{{ __('app.print') }}</button>
        <a href="{{ route('documents.show', $document) }}">{{ __('app.close') }}</a>
    </div>

    @include('documents._paper', ['document' => $document, 'org' => $org])

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
