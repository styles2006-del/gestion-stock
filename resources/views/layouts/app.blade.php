<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('nav.dashboard')) · {{ \App\Models\Setting::getValue('org_name', config('app.name')) }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📦</text></svg>">

    <script>
        (function () {
            const t = localStorage.getItem('stock-theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen">
        {{-- ============ SIDEBAR ============ --}}
        <div id="app-overlay" class="fixed inset-0 z-40 hidden bg-black/30 backdrop-blur-sm lg:hidden" data-sidebar-close></div>

        <aside id="app-sidebar" class="glass fixed inset-y-0 left-0 z-50 flex w-[264px] -translate-x-full flex-col border-r border-black/5 transition-transform duration-300 lg:translate-x-0 dark:border-white/10">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 py-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-accent-400 to-accent-600 text-white shadow-lg shadow-accent-500/25">
                    <x-icon name="box" class="h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold tracking-tight text-ink-800 dark:text-white">
                        {{ \App\Models\Setting::getValue('org_name', config('app.name')) }}
                    </p>
                    <p class="text-[11px] text-ink-400">{{ __('app.app_name') }}</p>
                </div>
                <button type="button" class="btn-ghost -mr-1 !p-1.5 lg:hidden" data-sidebar-close>
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="no-scrollbar flex-1 overflow-y-auto px-3 pb-4">
                @php
                    $link = fn (string $route, string $icon, string $label) => [
                        'url' => route($route),
                        'icon' => $icon,
                        'label' => $label,
                        'active' => request()->routeIs(str_replace('.index', '.*', $route)) || request()->routeIs($route),
                    ];

                    $sections = [
                        __('nav.inventory') => array_filter([
                            auth()->user()->canManage() ? $link('dashboard', 'dashboard', __('nav.dashboard')) : $link('dashboard', 'dashboard', __('nav.dashboard')),
                            $link('products.index', 'box', __('nav.products')),
                        ]),
                    ];
                @endphp

                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                    <x-icon name="dashboard" class="h-[18px] w-[18px]" />
                    {{ __('nav.dashboard') }}
                </a>

                <p class="nav-section">{{ __('nav.inventory') }}</p>
                <div class="space-y-0.5">
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'nav-link-active' : '' }}">
                        <x-icon name="box" class="h-[18px] w-[18px]" /> {{ __('nav.products') }}
                    </a>
                    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'nav-link-active' : '' }}">
                        <x-icon name="tag" class="h-[18px] w-[18px]" /> {{ __('nav.categories') }}
                    </a>
                    <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'nav-link-active' : '' }}">
                        <x-icon name="warehouse" class="h-[18px] w-[18px]" /> {{ __('nav.warehouses') }}
                    </a>
                    <a href="{{ route('movements.index') }}" class="nav-link {{ request()->routeIs('movements.*') ? 'nav-link-active' : '' }}">
                        <x-icon name="arrows" class="h-[18px] w-[18px]" /> {{ __('nav.movements') }}
                    </a>
                </div>

                <p class="nav-section">{{ __('nav.management') }}</p>
                <div class="space-y-0.5">
                    <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.*') ? 'nav-link-active' : '' }}">
                        <x-icon name="document" class="h-[18px] w-[18px]" /> {{ __('nav.documents') }}
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'nav-link-active' : '' }}">
                        <x-icon name="truck" class="h-[18px] w-[18px]" /> {{ __('nav.suppliers') }}
                    </a>
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'nav-link-active' : '' }}">
                        <x-icon name="chart" class="h-[18px] w-[18px]" /> {{ __('nav.reports') }}
                    </a>
                </div>

                @if (auth()->user()->isAdmin())
                    <p class="nav-section">{{ __('nav.administration') }}</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'nav-link-active' : '' }}">
                            <x-icon name="users" class="h-[18px] w-[18px]" /> {{ __('nav.users') }}
                        </a>
                        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'nav-link-active' : '' }}">
                            <x-icon name="settings" class="h-[18px] w-[18px]" /> {{ __('nav.settings') }}
                        </a>
                    </div>
                @endif
            </nav>

            {{-- Carte utilisateur --}}
            @php $me = auth()->user(); @endphp
            <div class="border-t border-black/5 p-3 dark:border-white/10">
                <div class="flex items-center gap-3 rounded-xl px-2 py-1.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-ink-700 to-ink-900 text-xs font-semibold text-white dark:from-ink-200 dark:to-white dark:text-ink-900">
                        {{ strtoupper(mb_substr($me->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-ink-800 dark:text-white">{{ $me->name }}</p>
                        <p class="truncate text-[11px] text-ink-400">{{ $me->roleLabel() }}</p>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1">
                    <a href="{{ route('profile.edit') }}" class="nav-link flex-1 !py-2">
                        <x-icon name="user" class="h-4 w-4" /> {{ __('nav.profile') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link !py-2 !px-2.5" title="{{ __('nav.logout') }}">
                            <x-icon name="logout" class="h-4 w-4" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ============ CONTENU ============ --}}
        <div class="flex min-w-0 flex-1 flex-col lg:pl-[264px]">
            {{-- Barre supérieure --}}
            <header class="glass sticky top-0 z-30 border-b border-black/5 dark:border-white/10">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6">
                    <button type="button" class="btn-ghost !p-2 lg:hidden" data-sidebar-toggle>
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>

                    <div class="relative hidden max-w-md flex-1 sm:block">
                        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-ink-400" />
                        <form method="GET" action="{{ route('products.index') }}">
                            <input type="search" name="q" value="{{ request('q') }}"
                                   placeholder="{{ __('app.search_placeholder') }}"
                                   class="input !rounded-full !py-2 pl-9">
                        </form>
                    </div>

                    <div class="ml-auto flex items-center gap-1.5">
                        {{-- Sélecteur de langue --}}
                        <div class="hidden items-center rounded-full bg-black/[0.04] p-0.5 sm:flex dark:bg-white/[0.06]">
                            @foreach (['fr' => 'FR', 'en' => 'EN'] as $code => $short)
                                <a href="{{ route('locale.switch', $code) }}"
                                   class="rounded-full px-2.5 py-1 text-xs font-semibold transition
                                          {{ app()->getLocale() === $code ? 'bg-white text-ink-900 shadow-sm dark:bg-white/15 dark:text-white' : 'text-ink-500 hover:text-ink-800 dark:text-ink-400 dark:hover:text-white' }}">
                                    {{ $short }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Thème --}}
                        <button type="button" class="btn-ghost !p-2" data-theme-toggle title="{{ __('app.language') }}">
                            <x-icon name="sun" class="h-5 w-5 dark:hidden" />
                            <x-icon name="moon" class="hidden h-5 w-5 dark:block" />
                        </button>

                        @if (auth()->user()->canManage())
                            <a href="{{ route('movements.create') }}" class="btn-primary hidden sm:inline-flex">
                                <x-icon name="plus" class="h-4 w-4" />
                                <span>{{ __('movements.new') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 sm:py-8">
                {{-- Messages flash --}}
                @if (session('success'))
                    <div data-auto-dismiss class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div data-auto-dismiss class="mb-5 flex items-start gap-3 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                        <div class="flex items-center gap-2 font-medium">
                            <x-icon name="alert" class="h-4 w-4" />
                            <span>{{ trans_choice('app.results_count', $errors->count(), ['count' => $errors->count()]) }}</span>
                        </div>
                        <ul class="mt-2 list-inside list-disc space-y-0.5 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="px-6 pb-6 text-center text-xs text-ink-400">
                © {{ date('Y') }} {{ \App\Models\Setting::getValue('org_name', config('app.name')) }} — {{ __('app.app_name') }}
            </footer>
        </div>
    </div>

    @include('components.chatbot')

    @stack('scripts')
</body>
</html>
