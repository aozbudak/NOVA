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
    <body class="min-h-screen bg-background font-sans text-foreground antialiased">
        <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-16">
            @yield('content')
        </main>
    </body>
</html>
