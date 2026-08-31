@extends('layouts.storefront')

@section('title', __('storefront.account.addresses'))

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-16 md:grid-cols-[14rem_minmax(0,1fr)] md:px-8">
        @include('storefront.account.nav')
        <div>
            <h2 class="text-[11px] tracking-nav uppercase">{{ __('storefront.account.addresses') }}</h2>
            <p class="mt-6 max-w-md text-sm text-muted-foreground">{{ __('storefront.account.no_addresses') }}</p>
        </div>
    </div>
@endsection
