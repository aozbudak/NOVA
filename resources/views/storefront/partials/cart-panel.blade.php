@php
    $items = $items ?? app(\App\Support\Cart::class)->items();
    $totals = $totals ?? app(\App\Support\Cart::class)->totals();
    $subtotal = $subtotal ?? $totals['total'];
@endphp

@if ($items->isEmpty())
    <div class="flex flex-col items-center gap-4 px-6 py-8 text-center">
        <p class="text-[11px] tracking-nav uppercase">{{ __('storefront.cart.empty') }}</p>
        <x-button href="{{ route('shop.show', 'new-in') }}" data-close="cart">{{ __('storefront.cart.continue') }}</x-button>
    </div>
@else
    <ul class="flex-1 overflow-y-auto px-5 py-3">
        @foreach ($items as $item)
            <li class="flex gap-4 border-b border-border py-4 first:pt-0">
                <a href="{{ route('product.show', $item['product']['slug']) }}" class="block w-20 shrink-0">
                    @if (($item['product']['images'][0] ?? null))
                        <img
                            src="{{ $item['product']['images'][0] }}"
                            alt="{{ $item['product']['name'] }}"
                            width="160"
                            height="200"
                            class="aspect-[4/5] w-full object-cover"
                            loading="lazy"
                        >
                    @else
                        <div class="aspect-[4/5] w-full bg-muted" aria-hidden="true"></div>
                    @endif
                </a>
                <div class="flex min-w-0 flex-1 flex-col gap-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm">{{ $item['product']['name'] }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ __('storefront.cart.size', ['size' => $item['size']]) }}</p>
                            @if ($item['line_discount'] > 0)
                                <p class="mt-1 text-xs text-muted-foreground">
                                    <span class="line-through">{{ Number::currency($item['unit_original'] * $item['quantity'], in: 'EUR') }}</span>
                                    @if ($item['discount_percent'])
                                        · {{ __('storefront.product.off', ['percent' => $item['discount_percent']]) }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        <p class="text-sm">{{ Number::currency($item['line_total'], in: 'EUR') }}</p>
                    </div>
                    <div class="mt-auto flex items-center justify-between">
                        <div class="inline-flex items-center border border-border">
                            <button type="button" class="p-2" data-cart-qty="{{ $item['key'] }}" data-qty="{{ $item['quantity'] - 1 }}" aria-label="{{ __('storefront.cart.decrease') }}">
                                <x-icon name="minus" size="size-3.5" />
                            </button>
                            <span class="min-w-6 text-center text-xs">{{ $item['quantity'] }}</span>
                            <button type="button" class="p-2" data-cart-qty="{{ $item['key'] }}" data-qty="{{ $item['quantity'] + 1 }}" aria-label="{{ __('storefront.cart.increase') }}">
                                <x-icon name="plus" size="size-3.5" />
                            </button>
                        </div>
                        <button type="button" class="text-[10px] tracking-label uppercase text-muted-foreground" data-cart-remove="{{ $item['key'] }}">{{ __('storefront.cart.remove') }}</button>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    <div class="border-t border-border px-5 py-4">
        <div class="flex flex-col gap-1.5 text-sm">
            <div class="flex items-center justify-between">
                <span class="tracking-label uppercase text-muted-foreground">{{ __('storefront.cart.subtotal') }}</span>
                <span>{{ Number::currency($totals['subtotal'], in: 'EUR') }}</span>
            </div>
            @if ($totals['discount'] > 0)
                <div class="flex items-center justify-between">
                    <span class="tracking-label uppercase text-muted-foreground">{{ __('storefront.cart.discount') }}</span>
                    <span>−{{ Number::currency($totals['discount'], in: 'EUR') }}</span>
                </div>
            @endif
            <div class="flex items-center justify-between">
                <span class="tracking-label uppercase text-muted-foreground">{{ __('storefront.cart.tax') }}</span>
                <span>{{ Number::currency($totals['tax'], in: 'EUR') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="tracking-label uppercase text-muted-foreground">{{ __('storefront.cart.total') }}</span>
                <span data-cart-subtotal>{{ Number::currency($totals['total'], in: 'EUR') }}</span>
            </div>
        </div>
        <x-button href="{{ route('checkout.show') }}" class="mt-4 w-full">{{ __('storefront.cart.checkout') }}</x-button>
    </div>
@endif
