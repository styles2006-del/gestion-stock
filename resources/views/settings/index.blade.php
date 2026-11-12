@extends('layouts.app')

@section('title', __('settings.title'))

@section('content')
    <x-page-header :title="__('settings.title')" :subtitle="__('settings.subtitle')" icon="settings" />

    <form method="POST" action="{{ route('settings.update') }}" class="max-w-3xl space-y-5">
        @csrf
        @method('PUT')

        {{-- Organisation --}}
        <div class="card p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                    <x-icon name="building" class="h-4 w-4" />
                </div>
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('settings.organization') }}</h2>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="org_name" class="label">{{ __('settings.org_name') }} *</label>
                    <input id="org_name" name="org_name" value="{{ old('org_name', $settings['org_name']) }}" class="input" required>
                    @error('org_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="org_address" class="label">{{ __('settings.org_address') }}</label>
                    <input id="org_address" name="org_address" value="{{ old('org_address', $settings['org_address']) }}" class="input">
                    @error('org_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="org_phone" class="label">{{ __('settings.org_phone') }}</label>
                        <input id="org_phone" name="org_phone" value="{{ old('org_phone', $settings['org_phone']) }}" class="input">
                        @error('org_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="org_email" class="label">{{ __('settings.org_email') }}</label>
                        <input id="org_email" type="email" name="org_email" value="{{ old('org_email', $settings['org_email']) }}" class="input">
                        @error('org_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="org_logo" class="label">{{ __('settings.org_logo') }}</label>
                    <input id="org_logo" name="org_logo" value="{{ old('org_logo', $settings['org_logo']) }}" class="input">
                    <p class="mt-1 text-xs text-ink-400">{{ __('settings.org_logo_hint') }}</p>
                    @error('org_logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Finance --}}
        <div class="card p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                    <x-icon name="coins" class="h-4 w-4" />
                </div>
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('settings.finance') }}</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="currency" class="label">{{ __('settings.currency') }}</label>
                    <select id="currency" name="currency" class="input">
                        @foreach (['EUR', 'USD', 'GBP', 'MAD', 'CHF', 'CAD', 'XOF'] as $currency)
                            <option value="{{ $currency }}" @selected(old('currency', $settings['currency']) === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                    @error('currency') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tax_rate" class="label">{{ __('settings.tax_rate') }}</label>
                    <input id="tax_rate" type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate']) }}" class="input">
                    @error('tax_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="card p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                    <x-icon name="alert" class="h-4 w-4" />
                </div>
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('settings.notifications') }}</h2>
            </div>

            <label class="flex cursor-pointer items-start gap-3">
                <input type="checkbox" name="low_stock_alert" value="1" class="checkbox mt-0.5" @checked(old('low_stock_alert', $settings['low_stock_alert']))>
                <span class="text-sm text-ink-700 dark:text-ink-100">
                    {{ __('settings.low_stock_alert') }}
                    <span class="block text-xs text-ink-400">{{ __('settings.low_stock_alert_hint') }}</span>
                </span>
            </label>
        </div>

        {{-- Apparence --}}
        <div class="card p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                    <x-icon name="sparkles" class="h-4 w-4" />
                </div>
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('settings.appearance') }}</h2>
            </div>
            <p class="text-sm text-ink-500 dark:text-ink-400">{{ __('settings.appearance_hint') }}</p>
        </div>

        <div class="flex items-center justify-end gap-2">
            <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}</button>
        </div>
    </form>
@endsection
