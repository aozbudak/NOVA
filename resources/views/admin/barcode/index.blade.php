@extends('layouts.admin')

@section('title', __('admin.barcode.title'))

@section('content')
    <x-admin.page-header :title="__('admin.barcode.title')" />

    <x-admin.filters :action="route('admin.barcode.index')" :chips="$chips" :columns="1">
        <x-admin.input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.barcode.search') }}" />
    </x-admin.filters>

    @if ($barcodes->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.barcode.title')">
                {{ __('admin.empty.barcode.body') }}
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$barcodes">
            <x-slot:head>
                <x-admin.th sort="product">{{ __('admin.barcode.product') }}</x-admin.th>
                <x-admin.th>{{ __('admin.barcode.variant') }}</x-admin.th>
                <x-admin.th sort="sku">{{ __('admin.products.sku') }}</x-admin.th>
                <x-admin.th sort="barcode">{{ __('admin.products.barcode') }}</x-admin.th>
                <x-admin.th sort="stock" align="end">{{ __('admin.products.stock') }}</x-admin.th>
                <x-admin.th>{{ __('admin.products.status') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($barcodes as $row)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.barcode.product')">
                            <a href="{{ route('admin.products.edit', $row['product_slug']) }}" class="hover:underline">{{ $row['product'] }}</a>
                        </x-admin.td>
                        <x-admin.td :label="__('admin.barcode.variant')" tone="muted">{{ $row['variant'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.sku')" tone="muted">{{ $row['sku'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.barcode')">{{ $row['barcode'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.stock')" align="end">{{ $row['stock'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.status')"><x-admin.badge :status="$row['status']" /></x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    @endif
@endsection
