<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'NOVA') — NOVA</title>
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
        <meta name="description" content="@yield('description', __('storefront.layout.description'))">
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
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-foreground">{{ __('storefront.layout.skip') }}</a>

        @unless($reducedChrome ?? false)
            <x-header :cart-count="$cartCount" :customer="$customer" :nav-items="$navItems" />
            <x-mobile-menu :customer="$customer" :nav-items="$navItems" />
            <x-search-overlay :index="$searchIndex" />
            <x-cart-drawer />
        @else
            <header class="fixed inset-x-0 top-0 z-40 glass border-b">
                <div class="mx-auto flex h-14 max-w-[1600px] items-center justify-between px-4 md:px-8">
                    <x-logo compact />
                    <x-locale-switcher />
                </div>
            </header>
        @endunless

        <main id="main" class="{{ request()->routeIs('home') ? '' : (($reducedChrome ?? false) ? 'pt-14' : 'pt-chrome') }}">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        @unless(($reducedChrome ?? false) || request()->routeIs('account.*'))
            <x-footer />
        @endunless

        <x-toast />
        <x-modal />

        <script>
            window.NOVA = {
                routes: {
                    cart: @json(route('api.cart.store')),
                    cartPanel: @json(route('cart.panel')),
                    cartUpdate: @json(route('api.cart.update', ['key' => '__KEY__'])),
                    cartDestroy: @json(route('api.cart.destroy', ['key' => '__KEY__'])),
                    wishlist: @json(route('api.wishlist.store')),
                    search: @json(route('api.search')),
                    product: @json(route('product.show', ['slug' => '__SLUG__'])),
                    catalog: @json(route('api.catalog.index')),
                    checkout: @json(route('api.checkout.store')),
                    checkoutConfirmation: @json(route('checkout.confirmation')),
                },
                csrf: @json(csrf_token()),
                catalog: @json($searchIndex),
                locale: @json(app()->getLocale()),
                i18n: {
                    addedToBag: @json(__('storefront.cart.added')),
                    error: @json(__('storefront.common.error')),
                    noResults: @json(__('storefront.search.no_results')),
                    addToWishlist: @json(__('storefront.wishlist.add')),
                    removeFromWishlist: @json(__('storefront.wishlist.remove')),
                    productImage: @json(__('storefront.product.image')),
                },
            };
        </script>
    </body>
</html>
