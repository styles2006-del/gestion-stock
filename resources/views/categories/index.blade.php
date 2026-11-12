@extends('layouts.app')

@section('title', __('nav.categories'))

@section('content')
    <x-page-header :title="__('categories.title')" :subtitle="__('app.results_count', ['count' => $categories->total()])" icon="tag">
        @if (auth()->user()->canManage())
            <a href="{{ route('categories.create') }}" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" /> {{ __('categories.new') }}
            </a>
        @endif
    </x-page-header>

    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label">{{ __('app.search') }}</label>
            <input type="search" name="q" value="{{ request('q') }}" class="input" placeholder="{{ __('categories.name') }} / {{ __('categories.code') }}">
        </div>
        <button type="submit" class="btn-secondary"><x-icon name="search" class="h-4 w-4" /> {{ __('app.search') }}</button>
        <a href="{{ route('categories.index') }}" class="btn-ghost"><x-icon name="refresh" class="h-4 w-4" /></a>
    </form>

    @if ($categories->isEmpty())
        <x-empty-state icon="tag" :title="__('categories.empty')" :hint="__('categories.empty_hint')">
            @if (auth()->user()->canManage())
                <a href="{{ route('categories.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('categories.new') }}</a>
            @endif
        </x-empty-state>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <div class="card group p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                                <x-icon name="tag" class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-ink-800 dark:text-white">{{ $category->name }}</p>
                                @if ($category->code)
                                    <p class="font-mono text-[11px] text-ink-400">{{ $category->code }}</p>
                                @endif
                            </div>
                        </div>
                        @if (auth()->user()->canManage())
                            <div class="flex items-center gap-1 opacity-0 transition group-hover:opacity-100">
                                <a href="{{ route('categories.edit', $category) }}" class="btn-ghost !p-1.5"><x-icon name="pencil" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm="{{ __('categories.delete_confirm') }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost !p-1.5 text-red-500 hover:bg-red-500/10"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <p class="mt-3 line-clamp-2 min-h-[2.5rem] text-sm text-ink-500 dark:text-ink-400">{{ $category->description ?: '—' }}</p>
                    <div class="mt-3 border-t border-black/5 pt-3 dark:border-white/10">
                        <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="flex items-center justify-between text-sm">
                            <span class="text-ink-500 dark:text-ink-400">{{ __('categories.products_count') }}</span>
                            <span class="badge-info">{{ $category->products_count }} {{ __('categories.products') }}</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-5">{{ $categories->links() }}</div>
    @endif
@endsection
