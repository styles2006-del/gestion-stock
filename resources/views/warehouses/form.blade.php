@extends('layouts.app')

@section('title', $warehouse->exists ? __('warehouses.edit') : __('warehouses.new'))

@section('content')
    <x-page-header :title="$warehouse->exists ? __('warehouses.edit') : __('warehouses.new')" :subtitle="$warehouse->exists ? $warehouse->name : __('app.required_fields')" icon="warehouse" />

    <form method="POST" action="{{ $warehouse->exists ? route('warehouses.update', $warehouse) : route('warehouses.store') }}" class="max-w-2xl">
        @csrf
        @if ($warehouse->exists) @method('PUT') @endif

        <div class="card space-y-4 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">{{ __('warehouses.name') }} *</label>
                    <input id="name" name="name" value="{{ old('name', $warehouse->name) }}" class="input" required autofocus>
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="code" class="label">{{ __('warehouses.code') }}</label>
                    <input id="code" name="code" value="{{ old('code', $warehouse->code) }}" class="input font-mono uppercase">
                    @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="location" class="label">{{ __('warehouses.location') }}</label>
                <input id="location" name="location" value="{{ old('location', $warehouse->location) }}" class="input">
            </div>
            <div>
                <label for="description" class="label">{{ __('warehouses.description') }}</label>
                <textarea id="description" name="description" rows="3" class="input">{{ old('description', $warehouse->description) }}</textarea>
            </div>
            <div class="flex flex-col gap-3 border-t border-black/5 pt-4 dark:border-white/10">
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="checkbox" name="is_default" value="1" class="checkbox" @checked(old('is_default', $warehouse->is_default))>
                    <span class="text-sm text-ink-700 dark:text-ink-100">{{ __('warehouses.is_default') }}
                        <span class="block text-xs text-ink-400">{{ __('warehouses.is_default_hint') }}</span>
                    </span>
                </label>
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $warehouse->is_active ?? true))>
                    <span class="text-sm text-ink-700 dark:text-ink-100">{{ __('app.active') }}</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2">
            <a href="{{ route('warehouses.index') }}" class="btn-secondary">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}</button>
        </div>
    </form>
@endsection
