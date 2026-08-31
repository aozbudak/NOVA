@php
    $links = [
        ['label' => __('storefront.account.orders'), 'route' => 'account.orders'],
        ['label' => __('storefront.header.wishlist'), 'route' => 'wishlist.index'],
        ['label' => __('storefront.account.profile'), 'route' => 'account.profile'],
        ['label' => __('storefront.account.addresses'), 'route' => 'account.addresses'],
        ['label' => __('storefront.account.settings'), 'route' => 'account.settings'],
    ];
@endphp

<aside class="flex flex-col gap-3">
    <h1 class="mb-4 font-serif text-3xl">{{ __('storefront.account.title') }}</h1>
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" class="text-[11px] tracking-nav uppercase {{ request()->routeIs($link['route']) ? 'text-foreground' : 'text-muted-foreground' }}">
            {{ $link['label'] }}
        </a>
    @endforeach
    <form method="post" action="{{ route('account.logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.account.logout') }}</button>
    </form>
</aside>
