@extends('layouts.admin')

@section('title', __('admin.variants.title'))

@section('content')
    <x-admin.page-header :title="__('admin.variants.title')" />

    <x-admin.table :paginator="$variants">
        <x-slot:head>
            <x-admin.th sort="product">{{ __('admin.variants.product') }}</x-admin.th>
            <x-admin.th>{{ __('admin.products.color') }}</x-admin.th>
            <x-admin.th>{{ __('admin.products.size') }}</x-admin.th>
            <x-admin.th sort="sku">{{ __('admin.products.sku') }}</x-admin.th>
            <x-admin.th>{{ __('admin.products.barcode') }}</x-admin.th>
            <x-admin.th sort="stock" align="end">{{ __('admin.products.stock') }}</x-admin.th>
            <x-admin.th sort="price" align="end">{{ __('admin.products.price') }}</x-admin.th>
            <x-admin.th>{{ __('admin.products.status') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($variants as $variant)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.variants.product')">{{ $variant['product'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.products.color')" tone="muted">{{ $variant['color'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.products.size')" tone="muted">{{ $variant['size'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.products.sku')" tone="muted">{{ $variant['sku'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.products.barcode')" tone="muted">{{ $variant['barcode'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.products.stock')" align="end">{{ $variant['stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.products.price')" align="end">{{ \App\Support\AdminStore::money($variant['price']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.products.status')"><x-admin.badge :status="$variant['stock_status']" /></x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
