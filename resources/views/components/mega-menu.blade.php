@props(['items' => []])

<div data-mega-root class="hidden border-t border-glass-border glass-strong text-foreground" role="presentation">
    @foreach ($items as $item)
        <div
            id="mega-{{ $item['department'] }}"
            data-mega="{{ $item['department'] }}"
            class="hidden"
            role="region"
            aria-label="{{ __('storefront.nav.shop_department', ['department' => $item['label']]) }}"
        >
            <div class="mx-auto grid max-w-[1600px] gap-10 px-8 py-10 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($item['columns'] as $column)
                        <div>
                            <p class="text-[11px] font-medium tracking-nav uppercase">{{ $column['title'] }}</p>
                            <ul class="mt-4 flex flex-col gap-2.5">
                                @foreach ($column['links'] as $link)
                                    <li>
                                        <a href="{{ $link['href'] }}" class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                            {{ $link['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                    <div>
                        <p class="text-[11px] font-medium tracking-nav uppercase">{{ __('storefront.nav.explore') }}</p>
                        <a href="{{ route('shop.show', $item['department']) }}" class="mt-4 inline-flex items-center gap-2 text-sm">
                            {{ __('storefront.nav.shop_department', ['department' => $item['label']]) }}
                            <x-icon name="arrow-right" size="size-4" />
                        </a>
                    </div>
                </div>
                <a href="{{ $item['featured']['href'] }}" class="group relative hidden min-h-48 overflow-hidden bg-muted lg:block">
                    <img
                        src="{{ $item['featured']['image'] }}"
                        alt="{{ $item['featured']['title'] }}"
                        width="480"
                        height="640"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-foreground/20"></div>
                    <div class="absolute inset-x-0 bottom-0 p-5 text-overlay">
                        <p class="text-[11px] tracking-nav uppercase">{{ $item['featured']['title'] }}</p>
                        <p class="mt-2 inline-flex items-center gap-2 text-[11px] tracking-nav uppercase">
                            {{ __('storefront.nav.shop_collection') }}
                            <x-icon name="arrow-right" size="size-4" />
                        </p>
                    </div>
                </a>
            </div>
        </div>
    @endforeach
</div>
