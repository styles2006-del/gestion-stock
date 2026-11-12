@extends('layouts.app')

@section('title', __('nav.warehouses'))

@section('content')
    <x-page-header :title="__('warehouses.title')" :subtitle="__('app.results_count', ['count' => $warehouses->count()])" icon="warehouse">
        @if (auth()->user()->canManage())
            <a href="{{ route('warehouses.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> {{ __('warehouses.new') }}
            </a>
        @endif
    </x-page-header>

    @if ($warehouses->isEmpty())
        <x-empty-state icon="warehouse" :title="__('warehouses.empty')" :hint="__('warehouses.empty_hint')">
            @if (auth()->user()->canManage())
                <a href="{{ route('warehouses.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('warehouses.new') }}</a>
            @endif
        </x-empty-state>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($warehouses as $warehouse)
                <div class="card group p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-ink-700 to-ink-900 text-white dark:from-ink-200 dark:to-white dark:text-ink-900">
                                <x-icon name="warehouse" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-ink-800 dark:text-white">{{ $warehouse->name }}</p>
                                    @if ($warehouse->is_default)
                                        <span class="badge-info">{{ __('warehouses.default_badge') }}</span>
                                    @endif
                                </div>
                                @if ($warehouse->code)
                                    <p class="font-mono text-[11px] text-ink-400">{{ $warehouse->code }}</p>
                                @endif
                            </div>
                        </div>
                        @if (auth()->user()->canManage())
                            <div class="flex items-center gap-1 opacity-0 transition group-hover:opacity-100">
                                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn-ghost !p-1.5"><x-icon name="pencil" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" data-confirm="{{ __('warehouses.delete_confirm') }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost !p-1.5 text-red-500 hover:bg-red-500/10"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        @endif
                    </div>

                    @if ($warehouse->location)
                        <p class="mt-3 flex items-center gap-1.5 text-sm text-ink-500 dark:text-ink-400">
                            <x-icon name="building" class="h-3.5 w-3.5" /> {{ $warehouse->location }}
                        </p>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-black/5 pt-4 dark:border-white/10">
                        <div>
                            <p class="text-xs text-ink-400">{{ __('warehouses.products_count') }}</p>
                            <p class="text-lg font-semibold tabular-nums text-ink-800 dark:text-white">{{ $warehouse->stocks_count }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-ink-400">{{ __('warehouses.movements_count') }}</p>
                            <p class="text-lg font-semibold tabular-nums text-ink-800 dark:text-white">{{ $warehouse->movements_count }}</p>
                        </div>
                    </div>

                    @unless ($warehouse->is_active)
                        <span class="badge-neutral mt-3">{{ __('app.inactive') }}</span>
                    @endunless
                </div>
            @endforeach
        </div>
    @endif
@endsection
