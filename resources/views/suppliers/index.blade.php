@extends('layouts.app')

@section('title', __('nav.suppliers'))

@section('content')
    <x-page-header :title="__('suppliers.title')" :subtitle="__('app.results_count', ['count' => $suppliers->total()])" icon="truck">
        @if (auth()->user()->canManage())
            <a href="{{ route('suppliers.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> {{ __('suppliers.new') }}
            </a>
        @endif
    </x-page-header>

    {{-- Filtres --}}
    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label">{{ __('app.search') }}</label>
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" name="q" value="{{ request('q') }}" class="input pl-9" placeholder="{{ __('suppliers.name') }} / {{ __('suppliers.email') }}">
            </div>
        </div>
        <div class="flex items-center gap-4 pb-2.5">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                <input type="checkbox" name="inactive" value="1" class="checkbox" @checked(request('inactive'))>
                {{ __('suppliers.filter_inactive') }}
            </label>
        </div>
        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> {{ __('app.filters') }}</button>
            <a href="{{ route('suppliers.index') }}" class="btn-ghost" title="{{ __('app.reset') }}"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    @if ($suppliers->isEmpty())
        <x-empty-state icon="truck" :title="__('suppliers.empty')" :hint="__('suppliers.empty_hint')">
            @if (auth()->user()->canManage())
                <a href="{{ route('suppliers.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('suppliers.new') }}</a>
            @endif
        </x-empty-state>
    @else
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('suppliers.name') }}</th>
                            <th class="hidden md:table-cell">{{ __('suppliers.email') }}</th>
                            <th class="hidden lg:table-cell">{{ __('suppliers.phone') }}</th>
                            <th class="text-right">{{ __('suppliers.products_count') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="text-right">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliers as $supplier)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-400 to-accent-600 text-xs font-semibold text-white">
                                            {{ strtoupper(mb_substr($supplier->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-ink-800 hover:text-accent-500 dark:text-ink-100">
                                                {{ $supplier->name }}
                                            </a>
                                            @if ($supplier->contact_name)
                                                <p class="text-[11px] text-ink-400">{{ $supplier->contact_name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell">
                                    @if ($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}" class="text-ink-500 hover:text-accent-500 dark:text-ink-400">{{ $supplier->email }}</a>
                                    @else
                                        <span class="text-ink-300">—</span>
                                    @endif
                                </td>
                                <td class="hidden lg:table-cell">
                                    <span class="tabular-nums text-ink-500 dark:text-ink-400">{{ $supplier->phone ?? '—' }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="badge-info">{{ $supplier->products_count }}</span>
                                </td>
                                <td>
                                    @if ($supplier->is_active)
                                        <span class="badge-success">{{ __('app.active') }}</span>
                                    @else
                                        <span class="badge-neutral">{{ __('app.inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn-ghost !p-2" title="{{ __('app.view') }}">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                        @if (auth()->user()->canManage())
                                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-ghost !p-2" title="{{ __('app.edit') }}">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </a>
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="{{ __('suppliers.delete_confirm') }}">
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
            {{ $suppliers->links() }}
        </div>
    @endif
@endsection
