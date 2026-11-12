@extends('layouts.app')

@section('title', __('nav.documents'))

@section('content')
    @php
        $currency = \App\Models\Setting::getValue('currency', 'EUR');
        $money = fn ($v) => number_format((float) $v, 2, ',', ' ') . ' ' . $currency;
    @endphp

    <x-page-header :title="__('documents.title')" :subtitle="__('app.results_count', ['count' => $documents->total()])" icon="document">
        @if (auth()->user()->canManage())
            <details class="relative">
                <summary class="btn-primary cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('documents.new') }}
                    <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                </summary>
                <div class="card absolute right-0 z-20 mt-2 w-52 overflow-hidden p-1.5">
                    @foreach (\App\Models\Document::TYPES as $type)
                        <a href="{{ route('documents.create', ['type' => $type]) }}"
                           class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-ink-700 transition hover:bg-black/[0.04] dark:text-ink-200 dark:hover:bg-white/[0.07]">
                            <x-icon name="document" class="h-4 w-4 text-ink-400" />
                            {{ __('documents.types.' . $type) }}
                        </a>
                    @endforeach
                </div>
            </details>
        @endif
    </x-page-header>

    {{-- Filtres --}}
    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label">{{ __('app.search') }}</label>
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" name="q" value="{{ request('q') }}" class="input pl-9" placeholder="{{ __('documents.number') }} / {{ __('documents.client') }} / {{ __('documents.supplier') }}">
            </div>
        </div>
        <div class="w-full sm:w-48">
            <label class="label">{{ __('documents.type') }}</label>
            <select name="type" class="input">
                <option value="">{{ __('app.all') }}</option>
                @foreach (\App\Models\Document::TYPES as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ __('documents.types.' . $type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-44">
            <label class="label">{{ __('documents.status') }}</label>
            <select name="status" class="input">
                <option value="">{{ __('app.all') }}</option>
                @foreach (\App\Models\Document::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('documents.statuses.' . $status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> {{ __('app.filters') }}</button>
            <a href="{{ route('documents.index') }}" class="btn-ghost" title="{{ __('app.reset') }}"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    @if ($documents->isEmpty())
        <x-empty-state icon="document" :title="__('documents.empty')" :hint="__('documents.empty_hint')">
            @if (auth()->user()->canManage())
                <a href="{{ route('documents.create') }}" class="btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('documents.new') }}
                </a>
            @endif
        </x-empty-state>
    @else
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('documents.number') }}</th>
                            <th class="hidden md:table-cell">{{ __('documents.client') }}</th>
                            <th class="hidden sm:table-cell">{{ __('app.date') }}</th>
                            <th class="text-right">{{ __('app.total') }}</th>
                            <th>{{ __('documents.status') }}</th>
                            <th class="text-right">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-500/12 text-accent-500">
                                            <x-icon name="document" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('documents.show', $document) }}" class="font-medium text-ink-800 hover:text-accent-500 dark:text-ink-100">
                                                {{ $document->number }}
                                            </a>
                                            <p class="text-[11px] text-ink-400">{{ __('documents.types.' . $document->type) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    <p class="text-ink-600 dark:text-ink-300">{{ $document->client_name ?? '—' }}</p>
                                    @if ($document->supplier)
                                        <p class="text-[11px] text-ink-400">{{ $document->supplier->name }}</p>
                                    @endif
                                </td>
                                <td class="hidden sm:table-cell whitespace-nowrap text-xs text-ink-400">
                                    {{ optional($document->document_date)->format('d/m/Y') }}
                                </td>
                                <td class="text-right font-medium tabular-nums text-ink-800 dark:text-ink-100">
                                    {{ $money($document->total) }}
                                </td>
                                <td><x-document-status :status="$document->status" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('documents.show', $document) }}" class="btn-ghost !p-2" title="{{ __('app.view') }}">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('documents.print', $document) }}" target="_blank" class="btn-ghost !p-2" title="{{ __('app.print') }}">
                                            <x-icon name="printer" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('documents.pdf', $document) }}" class="btn-ghost !p-2" title="{{ __('app.export_pdf') }}">
                                            <x-icon name="download" class="h-4 w-4" />
                                        </a>
                                        @if (auth()->user()->canManage())
                                            <a href="{{ route('documents.edit', $document) }}" class="btn-ghost !p-2" title="{{ __('app.edit') }}">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </a>
                                            <form method="POST" action="{{ route('documents.destroy', $document) }}" data-confirm="{{ __('documents.delete_confirm') }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-ghost !p-2 text-red-500 hover:bg-red-500/10" title="{{ __('app.delete') }}">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $documents->links() }}
        </div>
    @endif
@endsection
