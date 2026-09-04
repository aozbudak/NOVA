@extends('layouts.admin')

@section('title', __('admin.products.title'))

@section('content')
    <x-admin.page-header :title="__('admin.products.title')">
        <x-slot:actions>
            <x-admin.button :href="route('admin.products.create')" icon="plus">{{ __('admin.products.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.products.index')" :chips="$chips" :columns="5">
        <x-admin.input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.products.search') }}" />
        <x-admin.select name="category">
            <option value="">{{ __('admin.products.filter_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.select name="brand">
            <option value="">{{ __('admin.products.filter_brand') }}</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand }}" @selected($filters['brand'] === $brand)>{{ $brand }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.select name="status">
            <option value="">{{ __('admin.products.filter_status') }}</option>
            <option value="active" @selected($filters['status'] === 'active')>{{ __('admin.products.status_active') }}</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
        </x-admin.select>
        <x-admin.select name="stock">
            <option value="">{{ __('admin.products.filter_stock') }}</option>
            <option value="in_stock" @selected($filters['stock'] === 'in_stock')>{{ __('admin.stock.in_stock') }}</option>
            <option value="low_stock" @selected($filters['stock'] === 'low_stock')>{{ __('admin.stock.low_stock') }}</option>
            <option value="out_of_stock" @selected($filters['stock'] === 'out_of_stock')>{{ __('admin.stock.out_of_stock') }}</option>
        </x-admin.select>
    </x-admin.filters>

    @if ($products->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.products.title')">
                {{ __('admin.empty.products.body') }}
                <x-slot:action>
                    <x-admin.button :href="route('admin.products.create')">{{ __('admin.products.add') }}</x-admin.button>
                </x-slot:action>
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$products">
            <x-slot:head>
                <x-admin.th>{{ __('admin.products.image') }}</x-admin.th>
                <x-admin.th sort="name">{{ __('admin.products.product') }}</x-admin.th>
                <x-admin.th sort="sku">{{ __('admin.products.sku') }}</x-admin.th>
                <x-admin.th>{{ __('admin.products.barcode') }}</x-admin.th>
                <x-admin.th sort="category">{{ __('admin.products.category') }}</x-admin.th>
                <x-admin.th sort="price" align="end">{{ __('admin.products.price') }}</x-admin.th>
                <x-admin.th sort="stock" align="end">{{ __('admin.products.stock') }}</x-admin.th>
                <x-admin.th sort="status">{{ __('admin.products.status') }}</x-admin.th>
                <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($products as $product)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.products.image')">
                            @if (($product['image'] ?? '') !== '')
                                <img src="{{ $product['image'] }}" alt="" width="36" height="44" class="h-11 w-9 object-cover">
                            @else
                                <span class="inline-block h-11 w-9 bg-muted"></span>
                            @endif
                        </x-admin.td>
                        <x-admin.td :label="__('admin.products.product')">{{ $product['name'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.sku')" tone="muted">{{ $product['sku'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.barcode')" tone="muted">{{ $product['barcode'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.category')" tone="muted">{{ $product['category'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.price')" align="end">{{ \App\Support\AdminStore::money($product['price']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.products.stock')" align="end">
                            <span>{{ $product['stock'] }}</span>
                            <x-admin.badge class="ml-2" :status="$product['stock_status']" />
                        </x-admin.td>
                        <x-admin.td :label="__('admin.products.status')" tone="muted">{{ __('admin.products.status_'.$product['status']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.common.actions')" align="end">
                            <x-admin.row-actions>
                                <x-admin.icon-button icon="edit" :label="__('admin.common.edit')" :href="route('admin.products.edit', $product['slug'])" />
                                <x-admin.icon-button
                                    icon="delete"
                                    :label="__('admin.common.deactivate')"
                                    data-confirm
                                    data-confirm-title="{{ __('admin.confirm.deactivate_product') }}"
                                    data-confirm-body="{{ __('admin.confirm.deactivate_product_body') }}"
                                    data-confirm-action="{{ route('admin.products.deactivate', $product['slug']) }}"
                                />
                            </x-admin.row-actions>
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    @endif
@endsection
