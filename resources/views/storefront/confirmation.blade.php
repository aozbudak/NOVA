@extends('layouts.storefront', ['reducedChrome' => true])

@section('title', 'Order confirmed')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-24 text-center">
        <p class="text-[11px] tracking-nav uppercase text-muted-foreground">Thank you</p>
        <h1 class="mt-4 font-serif text-4xl">Your order is confirmed</h1>
        <p class="mt-6 text-sm text-muted-foreground">Order {{ $order['id'] }} has been sent to {{ $order['email'] }}.</p>
        <p class="mt-2 text-sm">{{ Number::currency($order['total'], in: 'EUR') }}</p>
        <x-button href="{{ route('home') }}" class="mt-10">Continue shopping</x-button>
    </div>
@endsection
