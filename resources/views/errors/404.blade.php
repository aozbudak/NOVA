<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('storefront.errors.not_found_title') }}</title>
        <script>
            (() => {
                const theme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', theme === 'dark' || (theme !== 'light' && prefersDark));
            })();
        </script>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background text-foreground">
        <div class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-4 text-center">
            <x-logo />
            <h1 class="mt-12 font-serif text-4xl">{{ __('storefront.errors.not_found_heading') }}</h1>
            <p class="mt-4 text-sm text-muted-foreground">{{ __('storefront.errors.not_found_body') }}</p>
            <a href="{{ url('/') }}" class="mt-10 inline-flex border border-primary bg-primary px-6 py-3 text-[11px] tracking-[0.18em] text-primary-foreground uppercase">{{ __('storefront.errors.back') }}</a>
        </div>
    </body>
</html>
