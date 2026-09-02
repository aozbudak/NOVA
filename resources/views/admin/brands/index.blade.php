@extends('layouts.admin')

@section('title', __('admin.brands.title'))

@section('content')
    <x-admin.page-header :title="__('admin.brands.title')" />

    <x-admin.table :paginator="$brands">
        <x-slot:head>
            <x-admin.th sort="name">{{ __('admin.brands.name') }}</x-admin.th>
            <x-admin.th sort="products" align="end">{{ __('admin.brands.products') }}</x-admin.th>
            <x-admin.th sort="stock" align="end">{{ __('admin.brands.stock') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.brands.status') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($brands as $brand)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.brands.name')">{{ $brand['name'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.brands.products')" align="end">{{ $brand['products'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.brands.stock')" align="end">{{ $brand['stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.brands.status')" tone="muted">{{ __('admin.products.status_'.$brand['status']) }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
