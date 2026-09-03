@props([
    'customer' => null,
    'navItems' => [],
])

<div data-drawer="menu" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-label="{{ __('storefront.header.menu') }}">
    <div data-drawer-backdrop class="absolute inset-0 bg-foreground/25 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>
    <div data-drawer-panel class="glass-strong absolute inset-y-0 left-0 flex w-[min(100%,22rem)] -translate-x-full flex-col border-r transition-transform duration-200">
        <div class="flex h-14 items-center justify-between px-4">
            <x-logo compact />
            <button type="button" data-close="menu" aria-label="{{ __('storefront.header.close_menu') }}" class="p-1">
                <x-icon name="x" />
            </button>
        </div>
        <nav class="flex flex-1 flex-col overflow-y-auto px-4 py-4" aria-label="{{ __('storefront.nav.mobile') }}">
            @foreach ($navItems as $item)
                <details class="border-b border-glass-border">
                    <summary class="flex cursor-pointer list-none items-center justify-between py-3 text-sm tracking-nav uppercase">
                        {{ $item['label'] }}
                        <x-icon name="chevron-down" size="size-4" />
                    </summary>
                    <div class="flex flex-col gap-2 pb-4 pl-1">
                        <a href="{{ $item['href'] ?? route('shop.show', $item['department']) }}" class="py-1 text-sm text-muted-foreground">{{ __('storefront.nav.shop_department', ['department' => $item['label']]) }}</a>
                        @foreach ($item['columns'] as $column)
                            <p class="mt-2 text-[10px] tracking-nav uppercase text-muted-foreground">{{ $column['title'] }}</p>
                            @foreach ($column['links'] as $link)
                                <a href="{{ $link['href'] }}" class="py-1 text-sm text-muted-foreground">{{ $link['label'] }}</a>
                            @endforeach
                        @endforeach
                    </div>
                </details>
            @endforeach
            <div class="mt-6 flex flex-col gap-3 text-sm text-muted-foreground">
                <a href="{{ $customer ? route('account.show') : route('login') }}">{{ __('storefront.header.account') }}</a>
                <a href="{{ route('wishlist.index') }}">{{ __('storefront.header.wishlist') }}</a>
                <a href="{{ route('pages.show', 'contact') }}">{{ __('storefront.header.help') }}</a>
            </div>
            <div class="mt-6 flex items-center justify-between gap-4 border-t border-glass-border py-4">
                <x-locale-switcher />
                <x-theme-toggle />
            </div>
        </nav>
    </div>
</div>
