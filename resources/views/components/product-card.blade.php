@props([
    'product',
    'wishlistIds' => [],
])

@php
    $inWishlist = in_array($product['id'], $wishlistIds, true);
    $quickSize = collect($product['sizes'])->firstWhere('in_stock', true)['code'] ?? 'M';
    $primaryImage = $product['images'][0] ?? null;
    $hoverImage = $product['images'][1] ?? null;
@endphp

<article {{ $attributes->merge(['class' => 'group']) }}>
    <div class="relative">
        <a href="{{ route('product.show', $product['slug']) }}" class="block overflow-hidden bg-muted">
            @if ($primaryImage)
                <img
                    src="{{ $primaryImage }}"
                    alt="{{ $product['name'] }}"
                    width="700"
                    height="875"
                    class="aspect-[4/5] w-full object-cover transition-opacity duration-300 group-hover:opacity-0"
                    loading="lazy"
                    sizes="(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 50vw"
                >
                @if ($hoverImage)
                    <img
                        src="{{ $hoverImage }}"
                        alt=""
                        width="700"
                        height="875"
                        class="absolute inset-0 aspect-[4/5] w-full object-cover opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        loading="lazy"
                    >
                @endif
            @else
                <div class="aspect-[4/5] w-full bg-muted" aria-hidden="true"></div>
            @endif
        </a>
        <x-wishlist-button :product-id="$product['id']" :active="$inWishlist" class="absolute top-3 right-3" />
        @if ($product['oldPrice'] && ($product['discountPercent'] ?? null))
            <span class="absolute top-3 left-3 bg-foreground px-2 py-1 text-[10px] tracking-nav uppercase text-background">{{ __('storefront.product.off', ['percent' => $product['discountPercent']]) }}</span>
        @elseif ($product['oldPrice'])
            <span class="absolute top-3 left-3 bg-foreground px-2 py-1 text-[10px] tracking-nav uppercase text-background">{{ __('storefront.product.sale') }}</span>
        @endif
        <button
            type="button"
            class="absolute inset-x-3 bottom-3 hidden bg-background/95 py-2.5 text-[10px] tracking-nav uppercase opacity-0 transition-opacity duration-150 group-hover:opacity-100 md:block"
            data-quick-add
            data-product-id="{{ $product['id'] }}"
            data-size="{{ $quickSize }}"
        >
            {{ __('storefront.product.quick_add') }}
        </button>
    </div>
    <div class="mt-3 flex flex-col gap-1">
        <a href="{{ route('product.show', $product['slug']) }}" class="text-sm">{{ $product['name'] }}</a>
        <p class="text-sm">
            @if ($product['oldPrice'])
                <span class="text-muted-foreground line-through">{{ Number::currency($product['oldPrice'], in: 'EUR') }}</span>
                <span class="ml-1">{{ Number::currency($product['price'], in: 'EUR') }}</span>
            @else
                {{ Number::currency($product['price'], in: 'EUR') }}
            @endif
        </p>
        <p class="text-xs text-muted-foreground">{{ $product['colors'][0]['name'] }}</p>
    </div>
</article>
