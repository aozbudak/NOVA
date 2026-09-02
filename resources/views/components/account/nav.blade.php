@php
    $customer = session('storefront.customer') ?? [];
    $first = (string) ($customer['first_name'] ?? '');
    $last = (string) ($customer['last_name'] ?? '');
    $fullName = trim($first.' '.$last);
    $email = (string) ($customer['email'] ?? '');
    $initials = mb_strtoupper(mb_substr($first, 0, 1).mb_substr($last, 0, 1));

    $links = [
        ['label' => __('storefront.account.overview'), 'route' => 'account.show', 'icon' => 'dashboard'],
        ['label' => __('storefront.account.orders'), 'route' => 'account.orders', 'icon' => 'bag'],
        ['label' => __('storefront.header.wishlist'), 'route' => 'wishlist.index', 'icon' => 'heart'],
        ['label' => __('storefront.account.profile'), 'route' => 'account.profile', 'icon' => 'user'],
        ['label' => __('storefront.account.addresses'), 'route' => 'account.addresses', 'icon' => 'map-pin'],
        ['label' => __('storefront.account.settings'), 'route' => 'account.settings', 'icon' => 'settings'],
    ];
@endphp

<aside class="account-card rounded-2xl border md:sticky md:top-[calc(var(--chrome-height)+1rem)]">
    <div class="flex items-center gap-3 border-b border-border px-4 py-3">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-foreground text-[11px] font-medium tracking-wide text-background uppercase">
            {{ $initials !== '' ? $initials : 'N' }}
        </span>
        <span class="min-w-0">
            <span class="block truncate text-sm font-medium leading-tight">{{ $fullName !== '' ? $fullName : __('storefront.account.title') }}</span>
            @if ($email !== '')
                <span class="mt-0.5 block truncate text-[12px] text-muted-foreground">{{ $email }}</span>
            @endif
        </span>
    </div>

    <nav class="grid grid-cols-3 gap-1 p-2 md:flex md:flex-col" aria-label="{{ __('storefront.account.title') }}">
        @foreach ($links as $link)
            @php $active = request()->routeIs($link['route']); @endphp
            <a
                href="{{ route($link['route']) }}"
                @if ($active) aria-current="page" @endif
                class="account-link flex flex-col items-center gap-1.5 rounded-xl px-2 py-2 text-center text-[12px] transition-colors md:flex-row md:gap-3 md:px-3 md:text-left {{ $active ? 'text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
            >
                <span class="flex size-8 items-center justify-center rounded-lg {{ $active ? 'bg-foreground text-background' : 'bg-muted' }}">
                    <x-icon :name="$link['icon']" size="size-4" />
                </span>
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>

    <form method="post" action="{{ route('account.logout') }}" class="border-t border-border p-2">
        @csrf
        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-[12px] text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
            <span class="flex size-8 items-center justify-center rounded-lg bg-muted">
                <x-icon name="logout" size="size-4" />
            </span>
            {{ __('storefront.account.logout') }}
        </button>
    </form>
</aside>
