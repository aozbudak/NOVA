@extends('layouts.admin')

@section('title', __('admin.categories.title'))

@section('content')
    <x-admin.page-header :title="__('admin.categories.title')" />

    <x-admin.table :paginator="$categories">
        <x-slot:head>
            <x-admin.th sort="name">{{ __('admin.categories.name') }}</x-admin.th>
            <x-admin.th sort="products" align="end">{{ __('admin.categories.products') }}</x-admin.th>
            <x-admin.th sort="stock" align="end">{{ __('admin.categories.stock') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.categories.status') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($categories as $category)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.categories.name')">{{ $category['name'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.products')" align="end">{{ $category['products'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.stock')" align="end">{{ $category['stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.status')" tone="muted">{{ __('admin.products.status_'.$category['status']) }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
