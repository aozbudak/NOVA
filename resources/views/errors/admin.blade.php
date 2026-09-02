@php
    $title = match ((int) $code) {
        401 => __('admin.errors.401_title'),
        403 => __('admin.errors.403_title'),
        default => __('admin.errors.404_title'),
    };
    $body = match ((int) $code) {
        401 => __('admin.errors.401_body'),
        403 => null,
        default => null,
    };
    $action = match ((int) $code) {
        401 => ['url' => route('login'), 'label' => __('admin.errors.login')],
        default => ['url' => url('/admin'), 'label' => __('admin.errors.back')],
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-panel>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} — {{ __('admin.brand') }}</title>
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
        <script>
            (() => {
                const theme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = theme === 'dark' || (theme !== 'light' && prefersDark);
                document.documentElement.classList.toggle('dark', isDark);
            })();
        </script>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background font-sans text-foreground antialiased">
        <div class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-4 text-center">
            <p class="font-serif text-lg tracking-tight">{{ __('admin.brand') }}</p>
            <h1 class="mt-10 text-sm font-medium tracking-[0.18em] text-foreground">{{ $title }}</h1>
            @if ($body)
                <p class="mt-3 text-[13px] tracking-[0.12em] text-muted-foreground">{{ $body }}</p>
            @endif
            <a href="{{ $action['url'] }}" class="mt-8 inline-flex h-9 items-center rounded-md bg-primary px-4 text-[12px] font-medium text-primary-foreground">
                {{ $action['label'] }}
            </a>
        </div>
    </body>
</html>
