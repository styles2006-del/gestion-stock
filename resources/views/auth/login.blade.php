<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.login_title') }} · {{ \App\Models\Setting::getValue('org_name', config('app.name')) }}</title>
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
<body class="relative flex min-h-screen items-center justify-center overflow-hidden px-4">
    {{-- Arrière-plan décoratif --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-32 -left-24 h-96 w-96 rounded-full bg-accent-400/25 blur-3xl"></div>
        <div class="absolute -right-24 -bottom-32 h-96 w-96 rounded-full bg-purple-400/20 blur-3xl"></div>
        <div class="absolute top-1/3 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-emerald-300/15 blur-3xl"></div>
    </div>

    <div class="w-full max-w-[420px]">
        <div class="mb-8 flex flex-col items-center text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-accent-400 to-accent-600 text-white shadow-xl shadow-accent-500/30">
                <x-icon name="box" class="h-7 w-7" />
            </div>
            <h1 class="mt-5 text-[26px] font-semibold tracking-tight text-ink-800 dark:text-white">
                {{ \App\Models\Setting::getValue('org_name', __('app.app_name')) }}
            </h1>
            <p class="mt-1.5 text-sm text-ink-500 dark:text-ink-400">{{ __('auth.login_subtitle') }}</p>
        </div>

        <div class="card animate-[var(--animate-fade-up)] p-7">
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="label">{{ __('auth.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="input" required autofocus autocomplete="username"
                           placeholder="vous@exemple.com">
                </div>

                <div>
                    <label for="password" class="label">{{ __('auth.password') }}</label>
                    <input id="password" type="password" name="password"
                           class="input" required autocomplete="current-password"
                           placeholder="••••••••">
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                    <input type="checkbox" name="remember" class="checkbox">
                    {{ __('auth.remember') }}
                </label>

                <button type="submit" class="btn-primary w-full !py-3 text-[15px]">
                    {{ __('auth.sign_in') }}
                </button>
            </form>

            <div class="mt-6 border-t border-black/5 pt-5 dark:border-white/10">
                <p class="mb-3 text-center text-xs font-medium tracking-wide text-ink-400 uppercase">
                    {{ __('auth.demo_credentials') }}
                </p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                        ['📦', 'admin@stock.test', 'Admin'],
                        ['🗂️', 'manager@stock.test', 'Manager'],
                        ['👁️', 'user@stock.test', 'User'],
                    ] as [$emoji, $mail, $label])
                        <button type="button"
                                onclick="document.getElementById('email').value='{{ $mail }}';document.getElementById('password').value='password';"
                                class="flex flex-col items-center gap-1 rounded-xl border border-black/5 bg-black/[0.02] px-2 py-2.5 text-center transition hover:bg-black/[0.05] dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/[0.08]">
                            <span class="text-lg">{{ $emoji }}</span>
                            <span class="text-[11px] font-medium text-ink-600 dark:text-ink-300">{{ $label }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-center gap-2 text-xs text-ink-400">
            @foreach (['fr' => 'Français', 'en' => 'English'] as $code => $label)
                <a href="{{ route('locale.switch', $code) }}"
                   class="{{ app()->getLocale() === $code ? 'font-semibold text-ink-700 dark:text-ink-200' : 'hover:text-ink-600' }}">
                    {{ $label }}
                </a>
                @if (! $loop->last) <span>·</span> @endif
            @endforeach
        </div>
    </div>
</body>
</html>
