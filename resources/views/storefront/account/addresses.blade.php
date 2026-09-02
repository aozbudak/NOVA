@extends('layouts.storefront')

@section('title', __('storefront.account.addresses'))

@section('content')
    <x-account.shell>
        <x-account.card>
            <div class="border-b border-border px-5 py-4">
                <h1 class="font-serif text-2xl tracking-tight">{{ __('storefront.account.addresses') }}</h1>
            </div>
            <x-account.empty :title="__('storefront.account.no_addresses_title')" icon="map-pin">
                {{ __('storefront.account.no_addresses') }}
                <x-slot:action>
                    <x-button href="{{ route('shop.show', 'new-in') }}">{{ __('storefront.wishlist.explore') }}</x-button>
                </x-slot:action>
            </x-account.empty>
        </x-account.card>
    </x-account.shell>
@endsection
