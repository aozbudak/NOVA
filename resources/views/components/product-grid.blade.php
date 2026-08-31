@props([
    'products',
    'wishlistIds' => [],
])

<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-x-3 gap-y-10 md:grid-cols-3 md:gap-x-5 lg:grid-cols-4']) }}>
    @foreach ($products as $product)
        <x-product-card :product="$product" :wishlist-ids="$wishlistIds" />
    @endforeach
</div>
