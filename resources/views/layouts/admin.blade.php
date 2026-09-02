<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-panel>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', __('admin.dashboard.title')) — {{ __('admin.brand') }}</title>
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
        <script>
            (() => {
                const theme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = theme === 'dark' || (theme !== 'light' && prefersDark);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.classList.toggle('light', theme === 'light');

                if (localStorage.getItem('nova.admin.sidebar') === 'collapsed' && window.matchMedia('(min-width: 1024px)').matches) {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            })();
        </script>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
        <script>
            window.NOVA = {
                csrf: @json(csrf_token()),
                locale: @json(app()->getLocale()),
                api: {
                    search: @json(route('api.admin.search')),
                    sales: @json(route('api.sales.store')),
                    products: @json(route('api.products.index')),
                    inventoryAdjust: @json(route('api.inventory.adjust')),
                    cashClose: @json(route('api.cash.close')),
                    returns: @json(route('api.returns.store')),
                    posItems: @json(route('api.pos.items')),
                },
            };
        </script>
    </head>
    <body class="account-atmosphere min-h-screen bg-background font-sans text-foreground antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-foreground">Skip to content</a>

        <x-admin.sidebar :sections="$navSections" :home-route="$homeRoute" :staff="$staff" />

        <div class="min-h-screen lg:pl-[var(--sidebar-width)]">
            <x-admin.header :breadcrumbs="$breadcrumbs" :notifications="$notifications" />

            <main id="main" @class([
                'min-h-[calc(100vh-var(--header-height))]',
                'px-4 py-5 md:px-8 md:py-6' => ! request()->routeIs('admin.pos.index'),
                'h-[calc(100vh-var(--header-height))] overflow-hidden p-0' => request()->routeIs('admin.pos.index'),
            ])>
                @yield('content')
            </main>
        </div>

        <x-admin.search :sections="$navSections" />
        <x-admin.confirm />
        <x-admin.toast />
    </body>
</html>
