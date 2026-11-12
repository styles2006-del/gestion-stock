<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->number }} · {{ __('documents.types.' . $document->type) }}</title>
</head>
<body>
    @include('documents._paper', ['document' => $document, 'org' => $org])
</body>
</html>
