@props([
    'productId',
    'active' => false,
])

<button
    type="button"
    data-wishlist-toggle
    data-product-id="{{ $productId }}"
    aria-pressed="{{ $active ? 'true' : 'false' }}"
    aria-label="{{ $active ? __('storefront.wishlist.remove') : __('storefront.wishlist.add') }}"
    {{ $attributes->merge(['class' => 'p-1 text-foreground transition-transform duration-150']) }}
>
    <x-icon name="heart" :filled="$active" />
</button>
