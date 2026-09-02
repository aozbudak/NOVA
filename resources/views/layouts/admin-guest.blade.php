<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-panel>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', __('admin.auth.title')) — {{ __('admin.brand') }}</title>
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
        <script>
            (() => {
                const theme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = theme === 'dark' || (theme !== 'light' && prefersDark);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.classList.toggle('light', theme === 'light');
            })();
        </script>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="login-atmosphere relative min-h-screen overflow-hidden font-sans text-foreground antialiased">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -top-24 -left-16 size-[28rem] rounded-full bg-sidebar-glow/25 blur-3xl"></div>
            <div class="absolute right-[-8%] bottom-[-10%] size-[32rem] rounded-full bg-primary/20 blur-3xl"></div>
            <div class="login-grid absolute inset-0 opacity-40"></div>
        </div>
        <main class="relative mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-16">
            @yield('content')
        </main>
    </body>
</html>
