@props([
    'cartCount' => 0,
    'customer' => null,
    'navItems' => [],
])

@php
    $overHero = request()->routeIs('home');
    $department = request()->route('department');
@endphp

<div
    data-chrome
    data-over-hero="{{ $overHero ? 'true' : 'false' }}"
    class="fixed inset-x-0 top-0 z-40 transition-[background-color,border-color,color,backdrop-filter] duration-200 {{ $overHero ? 'border-b border-transparent text-overlay' : 'glass border-b text-foreground' }}"
>
    <x-announcement-bar />

    <header data-header>
        <div class="mx-auto flex h-14 max-w-[1600px] items-center justify-between gap-4 px-4 md:h-16 md:px-8">
            <div class="flex flex-1 items-center md:hidden">
                <button type="button" class="p-1 text-current" data-open="menu" aria-label="{{ __('storefront.header.open_menu') }}">
                    <x-icon name="menu" />
                </button>
            </div>

            <x-logo class="shrink-0 md:flex-none" />

            <nav class="hidden min-w-0 flex-1 items-center justify-center gap-4 lg:gap-6 md:flex" aria-label="{{ __('storefront.nav.primary') }}">
                @foreach ($navItems as $item)
                    @php $active = $department === $item['department']; @endphp
                    <a
                        href="{{ route('shop.show', $item['department']) }}"
                        data-mega-trigger="{{ $item['department'] }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="mega-{{ $item['department'] }}"
                        @if ($active) aria-current="page" @endif
                        class="relative flex h-14 shrink-0 items-center text-[10px] font-medium tracking-nav uppercase text-current transition-opacity after:absolute after:inset-x-0 after:bottom-3 after:h-px after:origin-left after:bg-current after:transition-transform after:duration-200 hover:opacity-70 md:h-16 lg:text-[11px] {{ $active ? 'after:scale-x-100' : 'after:scale-x-0 hover:after:scale-x-100' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex flex-1 items-center justify-end gap-2 md:flex-none md:gap-3">
                <x-theme-toggle class="hidden md:inline-flex" />
                <x-locale-switcher class="hidden md:flex" />
                <button type="button" class="p-1 text-current" data-open="search" aria-label="{{ __('storefront.header.search') }}">
                    <x-icon name="search" />
                </button>
                <a href="{{ $customer ? route('account.show') : route('login') }}" class="hidden p-1 text-current md:inline-flex" aria-label="{{ __('storefront.header.account') }}">
                    <x-icon name="user" />
                </a>
                <a href="{{ route('wishlist.index') }}" class="hidden p-1 text-current md:inline-flex" aria-label="{{ __('storefront.header.wishlist') }}">
                    <x-icon name="heart" />
                </a>
                <button type="button" class="relative p-1 text-current" data-open="cart" aria-label="{{ __('storefront.header.bag') }}">
                    <x-icon name="bag" />
                    <span data-cart-badge class="absolute -top-0.5 -right-1 min-w-4 text-center text-[10px] tracking-wide {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                </button>
            </div>
        </div>
    </header>

    <x-mega-menu :items="$navItems" />
</div>
