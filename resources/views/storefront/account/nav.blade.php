@php
    $links = [
        ['label' => 'Orders', 'route' => 'account.orders'],
        ['label' => 'Wishlist', 'route' => 'wishlist.index'],
        ['label' => 'Profile', 'route' => 'account.profile'],
        ['label' => 'Addresses', 'route' => 'account.addresses'],
        ['label' => 'Settings', 'route' => 'account.settings'],
    ];
@endphp

<aside class="flex flex-col gap-3">
    <h1 class="mb-4 font-serif text-3xl">My account</h1>
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}" class="text-[11px] tracking-nav uppercase {{ request()->routeIs($link['route']) ? 'text-foreground' : 'text-muted-foreground' }}">
            {{ $link['label'] }}
        </a>
    @endforeach
    <form method="post" action="{{ route('account.logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-[11px] tracking-nav uppercase text-muted-foreground">Log out</button>
    </form>
</aside>
