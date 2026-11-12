@extends('layouts.app')

@section('title', $category->exists ? __('categories.edit') : __('categories.new'))

@section('content')
    <x-page-header :title="$category->exists ? __('categories.edit') : __('categories.new')" :subtitle="$category->exists ? $category->name : __('app.required_fields')" icon="tag" />

    <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="max-w-2xl">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="card space-y-4 p-6">
            <div>
                <label for="name" class="label">{{ __('categories.name') }} *</label>
                <input id="name" name="name" value="{{ old('name', $category->name) }}" class="input" required autofocus>
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="code" class="label">{{ __('categories.code') }}</label>
                <input id="code" name="code" value="{{ old('code', $category->code) }}" class="input font-mono uppercase">
                @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description" class="label">{{ __('categories.description') }}</label>
                <textarea id="description" name="description" rows="3" class="input">{{ old('description', $category->description) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2">
            <a href="{{ route('categories.index') }}" class="btn-secondary">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}</button>
        </div>
    </form>
@endsection
