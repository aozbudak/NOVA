@extends('layouts.storefront')

@section('title', __('storefront.account.title'))

@section('content')
    <x-account.shell>
        <x-account.card class="overflow-hidden px-5 py-5 md:px-6 md:py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-medium tracking-nav uppercase text-muted-foreground">{{ __('storefront.account.title') }}</p>
                    <h1 class="mt-1 font-serif text-3xl tracking-tight">
                        {{ isset($customer['first_name']) ? __('storefront.account.welcome_name', ['name' => $customer['first_name']]) : __('storefront.account.welcome') }}
                    </h1>
                    <p class="mt-1 max-w-xl text-sm text-muted-foreground">{{ __('storefront.account.overview_intro') }}</p>
                </div>
            </div>
            <div class="mt-5 grid gap-4 border-t border-border pt-4 sm:grid-cols-3">
                <x-account.stat :label="__('storefront.account.total_orders')" :value="count($orders)" />
                <x-account.stat :label="__('storefront.account.last_order')" :value="$orders[0]['date'] ?? '—'" />
                <x-account.stat
                    :label="__('storefront.account.total_spent')"
                    :value="Number::currency(collect($orders)->sum('total'), in: $orders[0]['currency'] ?? 'EUR')"
                />
            </div>
        </x-account.card>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.7fr)_20rem]">
            <x-account.orders-card :orders="$orders" :title="__('storefront.account.recent_orders')">
                <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 text-[12px] text-muted-foreground transition-colors hover:text-foreground">
                    {{ __('storefront.home.view_all') }}
                    <x-icon name="arrow-right" size="size-3.5" />
                </a>
            </x-account.orders-card>

            <div class="flex flex-col gap-3">
                <a href="{{ route('account.profile') }}" class="account-card group flex items-center gap-3 rounded-2xl border p-4 transition-colors hover:bg-muted">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-muted">
                        <x-icon name="user" size="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium">{{ __('storefront.account.profile') }}</span>
                        <span class="mt-0.5 block text-[12px] text-muted-foreground">{{ __('storefront.account.manage_profile') }}</span>
                    </span>
                    <x-icon name="chevron-right" size="size-4" class="text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                </a>
                <a href="{{ route('account.addresses') }}" class="account-card group flex items-center gap-3 rounded-2xl border p-4 transition-colors hover:bg-muted">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-muted">
                        <x-icon name="map-pin" size="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium">{{ __('storefront.account.addresses') }}</span>
                        <span class="mt-0.5 block text-[12px] text-muted-foreground">{{ __('storefront.account.manage_addresses') }}</span>
                    </span>
                    <x-icon name="chevron-right" size="size-4" class="text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                </a>
                <a href="{{ route('account.settings') }}" class="account-card group flex items-center gap-3 rounded-2xl border p-4 transition-colors hover:bg-muted">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-muted">
                        <x-icon name="settings" size="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium">{{ __('storefront.account.settings') }}</span>
                        <span class="mt-0.5 block text-[12px] text-muted-foreground">{{ __('storefront.account.appearance') }}</span>
                    </span>
                    <x-icon name="chevron-right" size="size-4" class="text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                </a>
            </div>
        </div>
    </x-account.shell>
@endsection
