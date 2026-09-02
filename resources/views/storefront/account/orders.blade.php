@extends('layouts.storefront')

@section('title', __('storefront.account.orders'))

@section('content')
    <x-account.shell>
        <x-account.orders-card :orders="$orders" :title="__('storefront.account.orders')" />
    </x-account.shell>
@endsection
