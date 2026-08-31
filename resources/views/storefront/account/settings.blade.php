@extends('layouts.storefront')

@section('title', 'Settings')

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-16 md:grid-cols-[14rem_minmax(0,1fr)] md:px-8">
        @include('storefront.account.nav')
        <div>
            <h2 class="text-[11px] tracking-nav uppercase">Settings</h2>
            <p class="mt-6 max-w-md text-sm text-muted-foreground">Manage email preferences and appearance. Use the header control to switch between light and dark mode.</p>
        </div>
    </div>
@endsection
