@extends('layouts.app')

@section('title', __('nav.users'))

@section('content')
    <x-page-header :title="__('users.title')" :subtitle="__('app.results_count', ['count' => $users->total()])" icon="users">
        <a href="{{ route('users.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" /> {{ __('users.new') }}
        </a>
    </x-page-header>

    {{-- Filtres --}}
    <form method="GET" class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[220px] flex-1">
            <label class="label">{{ __('app.search') }}</label>
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-ink-400" />
                <input type="search" name="q" value="{{ request('q') }}" class="input pl-9" placeholder="{{ __('users.name') }} / {{ __('users.email') }}">
            </div>
        </div>
        <div class="w-full sm:w-48">
            <label class="label">{{ __('users.role') }}</label>
            <select name="role" class="input">
                <option value="">{{ __('app.all') }}</option>
                @foreach (\App\Models\User::ROLES as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ __('roles.' . $role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn-secondary"><x-icon name="filter" class="h-4 w-4" /> {{ __('app.filters') }}</button>
            <a href="{{ route('users.index') }}" class="btn-ghost"><x-icon name="refresh" class="h-4 w-4" /></a>
        </div>
    </form>

    @if ($users->isEmpty())
        <x-empty-state icon="users" :title="__('users.empty')" />
    @else
        <div class="card overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('users.name') }}</th>
                            <th class="hidden md:table-cell">{{ __('users.email') }}</th>
                            <th>{{ __('users.role') }}</th>
                            <th class="hidden sm:table-cell">{{ __('users.locale') }}</th>
                            <th>{{ __('app.status') }}</th>
                            <th class="text-right">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-ink-700 to-ink-900 text-xs font-semibold text-white dark:from-ink-200 dark:to-white dark:text-ink-900">
                                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-ink-800 dark:text-ink-100">{{ $user->name }}</p>
                                        </div>
                                        @if ($user->id === auth()->id())
                                            <span class="badge-info">{{ __('users.you') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-ink-500">{{ $user->email }}</td>
                                <td>
                                    <span class="badge-info">{{ $user->roleLabel() }}</span>
                                </td>
                                <td class="hidden sm:table-cell text-ink-500">
                                    {{ $user->locale === 'en' ? __('app.english') : __('app.french') }}
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge-success">{{ __('app.active') }}</span>
                                    @else
                                        <span class="badge-neutral">{{ __('app.inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('users.edit', $user) }}" class="btn-ghost !p-2" title="{{ __('app.edit') }}">
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="{{ __('users.delete_confirm') }}">
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
            {{ $users->links() }}
        </div>
    @endif
@endsection
