@extends('layouts.storefront')

@section('title', 'Profile')

@section('content')
    <div class="mx-auto grid max-w-[1100px] gap-12 px-4 py-16 md:grid-cols-[14rem_minmax(0,1fr)] md:px-8">
        @include('storefront.account.nav')
        <div class="max-w-md">
            <h2 class="text-[11px] tracking-nav uppercase">Profile</h2>
            <dl class="mt-8 flex flex-col gap-6 text-sm">
                <div>
                    <dt class="text-[11px] tracking-label uppercase text-muted-foreground">Name</dt>
                    <dd class="mt-1">{{ $customer['first_name'] }} {{ $customer['last_name'] }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] tracking-label uppercase text-muted-foreground">Email</dt>
                    <dd class="mt-1">{{ $customer['email'] }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
