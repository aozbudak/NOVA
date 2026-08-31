@extends('layouts.storefront')

@section('title', 'Addresses')

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-16 md:grid-cols-[14rem_minmax(0,1fr)] md:px-8">
        @include('storefront.account.nav')
        <div>
            <h2 class="text-[11px] tracking-nav uppercase">Addresses</h2>
            <p class="mt-6 max-w-md text-sm text-muted-foreground">No saved addresses yet. Addresses added at checkout will appear here.</p>
        </div>
    </div>
@endsection
