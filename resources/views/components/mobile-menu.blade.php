<div data-drawer="menu" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-label="Menu">
    <div data-drawer-backdrop class="absolute inset-0 bg-foreground/40 opacity-0 transition-opacity duration-200"></div>
    <div data-drawer-panel class="absolute inset-y-0 left-0 flex w-[min(100%,22rem)] -translate-x-full flex-col bg-background transition-transform duration-200">
        <div class="flex h-14 items-center justify-between px-4">
            <x-logo />
            <button type="button" data-close="menu" aria-label="Close menu" class="p-1">
                <x-icon name="x" />
            </button>
        </div>
        <nav class="flex flex-1 flex-col gap-1 px-4 py-6" aria-label="Mobile">
            @foreach (['women' => 'Women', 'men' => 'Men', 'new-in' => 'New In', 'collections' => 'Collections', 'sale' => 'Sale'] as $slug => $label)
                <a href="{{ route('shop.show', $slug) }}" class="py-3 text-sm tracking-nav uppercase">{{ $label }}</a>
            @endforeach
            <div class="mt-8 flex flex-col gap-3 border-t border-border pt-6 text-sm text-muted-foreground">
                <a href="{{ ($customer ?? null) ? route('account.show') : route('login') }}">Account</a>
                <a href="{{ route('wishlist.index') }}">Wishlist</a>
                <a href="{{ route('pages.show', 'contact') }}">Help</a>
            </div>
            <div class="mt-auto pb-8">
                <x-theme-toggle />
            </div>
        </nav>
    </div>
</div>
