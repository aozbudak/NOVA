@extends('layouts.storefront')

@section('title', __('storefront.account.orders'))

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-16 md:grid-cols-[14rem_minmax(0,1fr)] md:px-8">
        @include('storefront.account.nav')
        <div>
            <h2 class="text-[11px] tracking-nav uppercase">{{ __('storefront.account.orders') }}</h2>
            <ul class="mt-6 divide-y divide-border border-y border-border">
                @foreach ($orders as $order)
                    <li class="grid grid-cols-2 gap-2 py-4 text-sm md:grid-cols-4">
                        <span>{{ __('storefront.account.order', ['id' => $order['id']]) }}</span>
                        <span class="text-muted-foreground">{{ $order['date'] }}</span>
                        <span>{{ Number::currency($order['total'], in: 'EUR') }}</span>
                        <span class="text-muted-foreground">{{ $order['status'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
