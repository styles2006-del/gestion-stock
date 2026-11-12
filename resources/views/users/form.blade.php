@extends('layouts.app')

@section('title', $user->exists ? __('users.edit') : __('users.new'))

@section('content')
    <x-page-header :title="$user->exists ? __('users.edit') : __('users.new')" :subtitle="$user->exists ? $user->name : __('app.required_fields')" icon="users" />

    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="max-w-2xl">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="card space-y-4 p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">{{ __('users.name') }} *</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required autofocus>
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">{{ __('users.email') }} *</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required>
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="label">{{ __('users.password') }} {{ $user->exists ? '' : '*' }}</label>
                    <input id="password" type="password" name="password" class="input" autocomplete="new-password" @if (! $user->exists) required @endif>
                    @if ($user->exists)
                        <p class="mt-1 text-xs text-ink-400">{{ __('users.password_hint') }}</p>
                    @endif
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label">{{ __('users.password_confirm') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password">
                </div>
                <div>
                    <label for="role" class="label">{{ __('users.role') }} *</label>
                    <select id="role" name="role" class="input" required>
                        @foreach (\App\Models\User::ROLES as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ __('roles.' . $role) }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="label">{{ __('users.phone') }}</label>
                    <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="input">
                    @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="locale" class="label">{{ __('users.locale') }} *</label>
                    <select id="locale" name="locale" class="input" required>
                        <option value="fr" @selected(old('locale', $user->locale) === 'fr')>{{ __('app.french') }}</option>
                        <option value="en" @selected(old('locale', $user->locale) === 'en')>{{ __('app.english') }}</option>
                    </select>
                    @error('locale') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-3 border-t border-black/5 pt-4 dark:border-white/10">
                <input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $user->is_active ?? true))>
                <span class="text-sm text-ink-700 dark:text-ink-100">{{ __('users.is_active') }}</span>
            </label>
        </div>

        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-accent-500/20 bg-accent-500/10 px-4 py-3 text-sm text-ink-600 dark:text-ink-300">
            <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-accent-500" />
            <span>{{ __('users.roles_hint') }}</span>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2">
            <a href="{{ route('users.index') }}" class="btn-secondary">{{ __('app.cancel') }}</a>
            <button type="submit" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('app.save') }}</button>
        </div>
    </form>
@endsection
