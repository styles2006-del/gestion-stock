@extends('layouts.app')

@section('title', __('profile.title'))

@section('content')
    <x-page-header :title="__('profile.title')" :subtitle="__('profile.subtitle')" icon="user" />

    <div class="max-w-3xl space-y-5">
        {{-- Informations personnelles --}}
        <div class="card p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                    <x-icon name="user" class="h-4 w-4" />
                </div>
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('profile.information') }}</h2>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="label">{{ __('users.name') }} *</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="label">{{ __('users.email') }} *</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required>
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="label">{{ __('users.phone') }}</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="input">
                        @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="locale" class="label">{{ __('users.locale') }}</label>
                        <select id="locale" name="locale" class="input">
                            <option value="fr" @selected(old('locale', $user->locale) === 'fr')>{{ __('app.french') }}</option>
                            <option value="en" @selected(old('locale', $user->locale) === 'en')>{{ __('app.english') }}</option>
                        </select>
                        @error('locale') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}</button>
                </div>
            </form>
        </div>

        {{-- Sécurité --}}
        <div class="card p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                    <x-icon name="shield" class="h-4 w-4" />
                </div>
                <h2 class="text-base font-semibold text-ink-800 dark:text-white">{{ __('profile.security') }}</h2>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="label">{{ __('profile.current_password') }}</label>
                    <input id="current_password" type="password" name="current_password" class="input" autocomplete="current-password" required>
                    @error('current_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="label">{{ __('profile.new_password') }}</label>
                        <input id="password" type="password" name="password" class="input" autocomplete="new-password" required>
                        @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">{{ __('profile.confirm_password') }}</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="btn-primary"><x-icon name="shield" class="h-4 w-4" /> {{ __('profile.update_password') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
