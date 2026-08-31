@extends('layouts.storefront', ['reducedChrome' => true])

@section('title', 'Checkout')

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-12 lg:grid-cols-[minmax(0,1fr)_20rem] lg:px-8">
        <form method="post" action="{{ route('checkout.store') }}" class="flex flex-col gap-12">
            @csrf
            <section class="flex flex-col gap-6">
                <h2 class="text-[11px] tracking-nav uppercase">Contact</h2>
                <x-input name="email" label="Email" type="email" :required="true" autocomplete="email" :value="$customer['email'] ?? ''" />
            </section>
            <section class="flex flex-col gap-6">
                <h2 class="text-[11px] tracking-nav uppercase">Shipping</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <x-input name="first_name" label="First name" :required="true" autocomplete="given-name" :value="$customer['first_name'] ?? ''" />
                    <x-input name="last_name" label="Last name" :required="true" autocomplete="family-name" :value="$customer['last_name'] ?? ''" />
                </div>
                <x-input name="address" label="Address" :required="true" autocomplete="street-address" />
                <div class="grid gap-6 md:grid-cols-3">
                    <x-input name="city" label="City" :required="true" autocomplete="address-level2" />
                    <x-input name="postal_code" label="Postal code" :required="true" autocomplete="postal-code" />
                    <x-input name="country" label="Country" :required="true" autocomplete="country-name" value="Germany" />
                </div>
            </section>
            <section>
                <h2 class="text-[11px] tracking-nav uppercase">Delivery</h2>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <label class="flex items-center justify-between border border-border px-4 py-3">
                        <span><input type="radio" name="delivery" value="standard" class="mr-3" checked> Standard — 3–5 days</span>
                        <span>Complimentary</span>
                    </label>
                    <label class="flex items-center justify-between border border-border px-4 py-3">
                        <span><input type="radio" name="delivery" value="express" class="mr-3"> Express — 1–2 days</span>
                        <span>€15.00</span>
                    </label>
                </div>
            </section>
            <section>
                <h2 class="text-[11px] tracking-nav uppercase">Payment</h2>
                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <label class="flex items-center border border-border px-4 py-3">
                        <input type="radio" name="payment" value="card" class="mr-3" checked> Card
                    </label>
                    <label class="flex items-center border border-border px-4 py-3">
                        <input type="radio" name="payment" value="paypal" class="mr-3"> PayPal
                    </label>
                </div>
            </section>
            <x-button type="submit" class="w-full md:w-auto">Place order</x-button>
        </form>
        <aside class="h-fit border border-border p-6">
            <h2 class="text-[11px] tracking-nav uppercase">Order summary</h2>
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
                <span class="tracking-label uppercase text-muted-foreground">Subtotal</span>
                <span>{{ Number::currency($subtotal, in: 'EUR') }}</span>
            </div>
        </aside>
    </div>
@endsection
