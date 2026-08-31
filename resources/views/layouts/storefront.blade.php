<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'NOVA') — NOVA</title>
        <meta name="description" content="@yield('description', 'NOVA is a contemporary fashion house. Discover the Autumn Winter 2026 collection.')">
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
    <body class="min-h-screen bg-background text-foreground" data-cart-count="{{ $cartCount }}" data-wishlist='@json($wishlistIds)'>
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-foreground">Skip to content</a>

        @unless($reducedChrome ?? false)
            <x-announcement-bar />
            <x-header :cart-count="$cartCount" :customer="$customer" />
            <x-mobile-menu :customer="$customer" />
            <x-search-overlay :index="$searchIndex" />
            <x-cart-drawer />
        @else
            <header class="border-b border-border">
                <div class="mx-auto flex h-14 max-w-[1600px] items-center justify-center px-4 md:px-8">
                    <x-logo />
                </div>
            </header>
        @endunless

        <main id="main">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        @unless($reducedChrome ?? false)
            <x-footer />
        @endunless

        <x-toast />
        <x-modal />

        <script>
            window.NOVA = {
                routes: {
                    cart: @json(route('cart.store')),
                    cartPanel: @json(route('cart.panel')),
                    cartUpdate: @json(url('/cart')),
                    wishlist: @json(route('wishlist.store')),
                    search: @json(route('search')),
                    product: @json(url('/product')),
                },
                csrf: @json(csrf_token()),
                catalog: @json($searchIndex),
            };
        </script>
    </body>
</html>
