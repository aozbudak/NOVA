@extends('layouts.storefront')

@section('title', __('storefront.account.addresses'))

@section('content')
    <x-account.shell>
        <x-account.card>
            <div class="flex items-center justify-between gap-4 border-b border-border px-5 py-4">
                <h1 class="font-serif text-2xl tracking-tight">{{ __('storefront.account.addresses') }}</h1>
            </div>

            @if ($addresses->isEmpty())
                <x-account.empty :title="__('storefront.account.no_addresses_title')" icon="map-pin">
                    {{ __('storefront.account.no_addresses') }}
                </x-account.empty>
            @else
                <div class="divide-y divide-border">
                    @foreach ($addresses as $address)
                        <article class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-sm font-medium">{{ $address->title }}</h2>
                                    @if ($address->is_default)
                                        <span class="rounded-full bg-muted px-2 py-0.5 text-[11px] text-muted-foreground">{{ __('storefront.account.default_address') }}</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm">{{ trim($address->first_name.' '.$address->last_name) }}</p>
                                <p class="mt-0.5 text-[13px] text-muted-foreground">{{ $address->address_line }}</p>
                                <p class="text-[13px] text-muted-foreground">{{ $address->district }}, {{ $address->city }}@if (filled($address->postal_code)) {{ $address->postal_code }} @endif</p>
                                @if (filled($address->phone))
                                    <p class="text-[13px] text-muted-foreground">{{ $address->phone }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="rounded-xl border border-border px-3 py-2 text-[11px] tracking-nav uppercase transition-colors hover:bg-muted">
                                            {{ __('storefront.account.set_default') }}
                                        </button>
                                    </form>
                                @endunless
                                <a href="{{ route('account.addresses.edit', $address) }}" class="inline-flex size-9 items-center justify-center rounded-xl border border-border hover:bg-muted" aria-label="{{ __('storefront.account.edit_address') }}">
                                    <x-icon name="edit" size="size-4" />
                                </a>
                                <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex size-9 items-center justify-center rounded-xl border border-border hover:bg-muted" aria-label="{{ __('storefront.account.delete_address') }}">
                                        <x-icon name="delete" size="size-4" />
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-account.card>

        @php
            $formAddress = $editing ?? null;
        @endphp

        <form
            method="POST"
            action="{{ $formAddress ? route('account.addresses.update', $formAddress) : route('account.addresses.store') }}"
            class="account-card overflow-hidden rounded-2xl border"
        >
            @csrf
            @if ($formAddress)
                @method('PUT')
            @endif
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-sm font-medium">{{ $formAddress ? __('storefront.account.edit_address') : __('storefront.account.add_address') }}</h2>
            </div>
            <div class="grid gap-6 p-5 sm:grid-cols-2">
                <x-input name="title" :label="__('storefront.account.address_title')" :value="old('title', $formAddress?->title ?? '')" :required="true" />
                <x-input name="phone" :label="__('storefront.account.phone')" type="tel" :value="old('phone', $formAddress?->phone ?? $customer['phone'] ?? '')" autocomplete="tel" />
                <x-input name="first_name" :label="__('storefront.auth.first_name')" :value="old('first_name', $formAddress?->first_name ?? $customer['first_name'] ?? '')" :required="true" autocomplete="given-name" />
                <x-input name="last_name" :label="__('storefront.auth.last_name')" :value="old('last_name', $formAddress?->last_name ?? $customer['last_name'] ?? '')" :required="true" autocomplete="family-name" />
                <div class="sm:col-span-2">
                    <x-input name="address_line" :label="__('storefront.checkout.address')" :value="old('address_line', $formAddress?->address_line ?? '')" :required="true" autocomplete="street-address" />
                </div>
                <x-input name="district" :label="__('storefront.account.district')" :value="old('district', $formAddress?->district ?? '')" :required="true" />
                <x-input name="city" :label="__('storefront.checkout.city')" :value="old('city', $formAddress?->city ?? '')" :required="true" autocomplete="address-level2" />
                <x-input name="postal_code" :label="__('storefront.checkout.postal_code')" :value="old('postal_code', $formAddress?->postal_code ?? '')" autocomplete="postal-code" />
                <label class="flex items-center gap-2 text-sm sm:col-span-2">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $formAddress?->is_default ?? $addresses->isEmpty()))>
                    {{ __('storefront.account.default_address') }}
                </label>
            </div>
            <div class="flex flex-wrap items-center gap-3 border-t border-border px-5 py-3">
                <x-button type="submit">{{ __('storefront.account.save') }}</x-button>
                @if ($formAddress)
                    <a href="{{ route('account.addresses') }}" class="text-[11px] tracking-nav uppercase text-muted-foreground hover:text-foreground">{{ __('storefront.account.cancel') }}</a>
                @endif
            </div>
        </form>
    </x-account.shell>
@endsection
