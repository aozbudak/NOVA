<div data-drawer="filters" class="fixed inset-0 z-50 hidden lg:hidden" role="dialog" aria-modal="true" aria-label="{{ __('storefront.listing.filters') }}">
    <div data-drawer-backdrop class="absolute inset-0 bg-foreground/25 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>
    <div data-drawer-panel class="glass-strong absolute inset-y-0 left-0 flex w-[min(100%,22rem)] -translate-x-full flex-col overflow-y-auto border-r px-5 py-6 transition-transform duration-200">
        <div class="mb-6 flex items-center justify-between">
            <p class="text-[11px] tracking-nav uppercase">{{ __('storefront.listing.filter') }}</p>
            <button type="button" data-close="filters" aria-label="{{ __('storefront.listing.close_filters') }}" class="p-1">
                <x-icon name="x" />
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
