@props(['image'])

<section class="relative min-h-svh overflow-hidden bg-muted">
    <img
        src="{{ $image }}"
        alt="{{ __('storefront.hero.alt') }}"
        width="2400"
        height="1600"
        class="absolute inset-0 h-full w-full object-cover"
        fetchpriority="high"
    >
    <div class="absolute inset-0 bg-foreground/25"></div>
    <div class="relative mx-auto flex min-h-svh max-w-[1600px] flex-col justify-end px-4 pb-12 md:px-8 md:pb-16">
        <p class="text-[11px] font-medium tracking-nav text-overlay uppercase">{{ __('storefront.hero.kicker') }}</p>
        <h1 class="mt-3 max-w-xl font-serif text-5xl tracking-tight text-overlay md:text-7xl">{{ __('storefront.hero.title') }}</h1>
        <div class="mt-8 flex flex-wrap gap-3">
            <x-button href="{{ route('shop.show', 'women') }}" variant="light">{{ __('storefront.hero.shop_women') }}</x-button>
            <x-button href="{{ route('shop.show', 'men') }}" variant="light">{{ __('storefront.hero.shop_men') }}</x-button>
        </div>
    </div>
</section>
