@props(['orders', 'title' => null])

<x-account.card {{ $attributes }}>
    @if (filled($title) || $slot->isNotEmpty())
        <div class="flex items-center justify-between gap-4 px-5 py-4">
            @if (filled($title))
                <h2 class="font-serif text-xl">{{ $title }}</h2>
            @endif
            {{ $slot }}
        </div>
    @endif

    @if (count($orders) === 0)
        <x-account.empty
            :title="__('storefront.account.no_orders')"
            icon="bag"
            @class(['py-10', 'border-t border-border' => filled($title) || $slot->isNotEmpty()])
        >
            {{ __('storefront.account.no_orders_body') }}
            <x-slot:action>
                <x-button href="{{ route('shop.show', 'new-in') }}">{{ __('storefront.wishlist.explore') }}</x-button>
            </x-slot:action>
        </x-account.empty>
    @else
        <div @class([
            'hidden grid-cols-[auto_minmax(0,1.2fr)_0.9fr_0.7fr_0.9fr_0.8fr_auto] gap-4 px-5 py-2.5 text-[11px] font-medium tracking-label text-muted-foreground uppercase md:grid',
            'border-t border-border' => filled($title) || $slot->isNotEmpty(),
        ])>
            <span class="size-10"></span>
            <span>{{ __('storefront.account.order_id') }}</span>
            <span>{{ __('storefront.account.order_date') }}</span>
            <span>{{ __('storefront.account.order_items') }}</span>
            <span>{{ __('storefront.account.order_total') }}</span>
            <span>{{ __('storefront.account.payment_method') }}</span>
            <span class="text-right">{{ __('storefront.account.order_status') }}</span>
        </div>
        <div class="divide-y divide-border border-t border-border">
            @foreach ($orders as $order)
                <x-account.order :order="$order" />
            @endforeach
        </div>
    @endif
</x-account.card>
