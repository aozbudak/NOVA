@props([
    'cartCount' => 0,
    'customer' => null,
])

<header
    data-header
    class="sticky top-0 z-40 bg-background/0 transition-[background-color,box-shadow] duration-200"
>
    <div class="mx-auto flex h-14 max-w-[1600px] items-center justify-between gap-6 px-4 md:h-16 md:px-8">
        <div class="flex flex-1 items-center md:hidden">
            <button type="button" class="p-1 text-foreground" data-open="menu" aria-label="Open menu">
                <x-icon name="menu" />
            </button>
        </div>

        <nav class="hidden flex-1 items-center gap-8 md:flex" aria-label="Primary">
            <a href="{{ route('shop.show', 'women') }}" class="text-[11px] font-medium tracking-nav uppercase text-foreground transition-opacity hover:opacity-60">Women</a>
            <a href="{{ route('shop.show', 'men') }}" class="text-[11px] font-medium tracking-nav uppercase text-foreground transition-opacity hover:opacity-60">Men</a>
            <a href="{{ route('shop.show', 'new-in') }}" class="text-[11px] font-medium tracking-nav uppercase text-foreground transition-opacity hover:opacity-60">New In</a>
            <a href="{{ route('shop.show', 'collections') }}" class="text-[11px] font-medium tracking-nav uppercase text-foreground transition-opacity hover:opacity-60">Collections</a>
            <a href="{{ route('shop.show', 'sale') }}" class="text-[11px] font-medium tracking-nav uppercase text-foreground transition-opacity hover:opacity-60">Sale</a>
        </nav>

        <x-logo class="shrink-0" />

        <div class="flex flex-1 items-center justify-end gap-3 md:gap-4">
            <x-theme-toggle class="hidden md:inline-flex" />
            <button type="button" class="p-1 text-foreground" data-open="search" aria-label="Search">
                <x-icon name="search" />
            </button>
            <a href="{{ $customer ? route('account.show') : route('login') }}" class="hidden p-1 text-foreground md:inline-flex" aria-label="Account">
                <x-icon name="user" />
            </a>
            <a href="{{ route('wishlist.index') }}" class="hidden p-1 text-foreground md:inline-flex" aria-label="Wishlist">
                <x-icon name="heart" />
            </a>
            <button type="button" class="relative p-1 text-foreground" data-open="cart" aria-label="Shopping bag">
                <x-icon name="bag" />
                <span data-cart-badge class="absolute -top-0.5 -right-1 min-w-4 text-center text-[10px] tracking-wide {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
            </button>
        </div>
    </div>
</header>
