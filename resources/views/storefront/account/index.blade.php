@extends('layouts.storefront')

@section('title', 'My account')

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-16 md:grid-cols-[14rem_minmax(0,1fr)] md:px-8">
        @include('storefront.account.nav')
        <div>
            <p class="text-sm">Welcome{{ isset($customer['first_name']) ? ', '.$customer['first_name'] : '' }}.</p>
            <h2 class="mt-10 text-[11px] tracking-nav uppercase">Orders</h2>
            <ul class="mt-4 divide-y divide-border border-y border-border">
                @foreach ($orders as $order)
                    <li class="grid grid-cols-2 gap-2 py-4 text-sm md:grid-cols-4">
                        <span>{{ $order['id'] }}</span>
                        <span class="text-muted-foreground">{{ $order['date'] }}</span>
                        <span>{{ Number::currency($order['total'], in: 'EUR') }}</span>
                        <span class="text-muted-foreground">{{ $order['status'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
