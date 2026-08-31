@extends('layouts.storefront', ['reducedChrome' => true])

@section('title', __('storefront.confirmation.title'))

@section('content')
    <div class="mx-auto max-w-lg px-4 py-24 text-center">
        <p class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.confirmation.thank_you') }}</p>
        <h1 class="mt-4 font-serif text-4xl">{{ __('storefront.confirmation.confirmed') }}</h1>
        <p class="mt-6 text-sm text-muted-foreground">{{ __('storefront.confirmation.sent', ['id' => $order['id'], 'email' => $order['email']]) }}</p>
        <p class="mt-2 text-sm">{{ Number::currency($order['total'], in: 'EUR') }}</p>
        <x-button href="{{ route('home') }}" class="mt-10">{{ __('storefront.confirmation.continue') }}</x-button>
    </div>
@endsection
