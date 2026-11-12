@extends('layouts.app')

@section('title', $supplier->exists ? __('suppliers.edit') : __('suppliers.new'))

@section('content')
    <x-page-header
        :title="$supplier->exists ? __('suppliers.edit') : __('suppliers.new')"
        :subtitle="$supplier->exists ? $supplier->name : __('app.required_fields')"
        icon="truck" />

    <form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="max-w-3xl">
        @csrf
        @if ($supplier->exists) @method('PUT') @endif

        <div class="card space-y-5 p-6">
            <div>
                <label for="name" class="label">{{ __('suppliers.name') }} *</label>
                <input id="name" name="name" value="{{ old('name', $supplier->name) }}" class="input" required autofocus>
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="contact_name" class="label">{{ __('suppliers.contact_name') }}</label>
                    <input id="contact_name" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" class="input">
                    @error('contact_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">{{ __('suppliers.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $supplier->email) }}" class="input">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="label">{{ __('suppliers.phone') }}</label>
                    <input id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}" class="input">
                    @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="website" class="label">{{ __('suppliers.website') }}</label>
                    <input id="website" name="website" value="{{ old('website', $supplier->website) }}" class="input">
                    @error('website') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="label">{{ __('suppliers.address') }}</label>
                    <input id="address" name="address" value="{{ old('address', $supplier->address) }}" class="input">
                    @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="city" class="label">{{ __('suppliers.city') }}</label>
                    <input id="city" name="city" value="{{ old('city', $supplier->city) }}" class="input">
                    @error('city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="country" class="label">{{ __('suppliers.country') }}</label>
                    <input id="country" name="country" value="{{ old('country', $supplier->country) }}" class="input">
                    @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="notes" class="label">{{ __('suppliers.notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $supplier->notes) }}</textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="border-t border-black/5 pt-5 dark:border-white/10">
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $supplier->is_active))>
                    <span class="text-sm font-medium text-ink-700 dark:text-ink-100">{{ __('app.active') }}</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2">
            <a href="{{ route('suppliers.index') }}" class="btn-secondary">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}</button>
        </div>
    </form>
@endsection
