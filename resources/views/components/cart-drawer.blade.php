<div data-drawer="cart" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="cart-title">
    <div data-drawer-backdrop class="absolute inset-0 bg-foreground/40 opacity-0 transition-opacity duration-200"></div>
    <div data-drawer-panel class="absolute inset-y-0 right-0 flex w-[min(100%,26rem)] translate-x-full flex-col bg-background transition-transform duration-200">
        <div class="flex h-14 items-center justify-between border-b border-border px-5">
            <h2 id="cart-title" class="text-[11px] font-medium tracking-nav uppercase">Your bag</h2>
            <button type="button" data-close="cart" aria-label="Close bag" class="p-1">
                <x-icon name="x" />
            </button>
        </div>
        <div data-cart-panel class="flex min-h-0 flex-1 flex-col">
            @include('storefront.partials.cart-panel')
        </div>
    </div>
</div>
