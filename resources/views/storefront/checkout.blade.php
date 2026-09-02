@extends('layouts.storefront', ['reducedChrome' => true])

@section('title', __('storefront.checkout.title'))

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-12 lg:grid-cols-[minmax(0,1fr)_20rem] lg:px-8">
        <form method="post" action="{{ route('checkout.store') }}" data-checkout class="flex flex-col gap-12">
            @csrf
            <section class="flex flex-col gap-6">
                <h2 class="text-[11px] tracking-nav uppercase">{{ __('storefront.checkout.contact') }}</h2>
                <x-input name="email" :label="__('storefront.checkout.email')" type="email" :required="true" autocomplete="email" :value="$customer['email'] ?? ''" />
            </section>
            <section class="flex flex-col gap-6">
                <h2 class="text-[11px] tracking-nav uppercase">{{ __('storefront.checkout.shipping') }}</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-input name="first_name" :label="__('storefront.checkout.first_name')" :required="true" autocomplete="given-name" :value="$customer['first_name'] ?? ''" />
                    <x-input name="last_name" :label="__('storefront.checkout.last_name')" :required="true" autocomplete="family-name" :value="$customer['last_name'] ?? ''" />
                </div>
                <x-input name="address" :label="__('storefront.checkout.address')" :required="true" autocomplete="street-address" />
                <div class="grid gap-6 md:grid-cols-3">
                    <x-input name="city" :label="__('storefront.checkout.city')" :required="true" autocomplete="address-level2" />
                    <x-input name="postal_code" :label="__('storefront.checkout.postal_code')" :required="true" autocomplete="postal-code" />
                    <x-input name="country" :label="__('storefront.checkout.country')" :required="true" autocomplete="country-name" :value="__('storefront.checkout.country_default')" />
                </div>
            </section>
            <section>
                <h2 class="text-[11px] tracking-nav uppercase">{{ __('storefront.checkout.delivery') }}</h2>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <label class="flex items-center justify-between border border-border px-4 py-3">
                        <span><input type="radio" name="delivery" value="standard" class="mr-3" checked> {{ __('storefront.checkout.standard') }}</span>
                        <span>{{ __('storefront.checkout.complimentary') }}</span>
                    </label>
                    <label class="flex items-center justify-between border border-border px-4 py-3">
                        <span><input type="radio" name="delivery" value="express" class="mr-3"> {{ __('storefront.checkout.express') }}</span>
                        <span>€15.00</span>
                    </label>
                </div>
            </section>
            <section>
                <h2 class="text-[11px] tracking-nav uppercase">{{ __('storefront.checkout.payment') }}</h2>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <label class="flex items-center border border-border px-4 py-3">
                        <input type="radio" name="payment" value="card" class="mr-3" checked> {{ __('storefront.checkout.card') }}
                    </label>
                    <label class="flex items-center border border-border px-4 py-3">
                        <input type="radio" name="payment" value="paypal" class="mr-3"> {{ __('storefront.checkout.paypal') }}
                    </label>
                </div>
            </section>
            <x-button type="submit" class="w-full md:w-auto">{{ __('storefront.checkout.place_order') }}</x-button>
        </form>
        <aside class="h-fit border border-border p-6">
            <h2 class="text-[11px] tracking-nav uppercase">{{ __('storefront.checkout.summary') }}</h2>
            <ul class="mt-6 flex flex-col gap-4">
                @foreach ($items as $item)
                    <li class="flex gap-3 text-sm">
                        <img src="{{ $item['product']['images'][0] }}" alt="{{ $item['product']['name'] }}" width="64" height="80" class="h-20 w-16 object-cover" loading="lazy">
                        <div class="flex-1">
                            <p>{{ $item['product']['name'] }}</p>
                            <p class="text-xs text-muted-foreground">{{ $item['size'] }} · {{ $item['quantity'] }}</p>
                        </div>
                        <p>{{ Number::currency($item['line_total'], in: 'EUR') }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6 flex justify-between border-t border-border pt-4 text-sm">
                <span class="tracking-label uppercase text-muted-foreground">{{ __('storefront.checkout.subtotal') }}</span>
                <span>{{ Number::currency($subtotal, in: 'EUR') }}</span>
            </div>
        </aside>
    </div>
@endsection
