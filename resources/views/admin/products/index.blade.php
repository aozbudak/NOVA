@extends('layouts.admin')

@section('title', __('admin.products.title'))

@section('content')
    <x-admin.page-header :title="__('admin.products.title')">
        <x-slot:actions>
            <a href="{{ route('admin.products.create') }}" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                <x-icon name="plus" size="size-3.5" />
                {{ __('admin.products.add') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4 grid gap-2 md:grid-cols-5">
        <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.products.search') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <select name="category" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.products.filter_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <select name="brand" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.products.filter_brand') }}</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand }}" @selected($filters['brand'] === $brand)>{{ $brand }}</option>
            @endforeach
        </select>
        <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.products.filter_status') }}</option>
            <option value="active" @selected($filters['status'] === 'active')>{{ __('admin.products.status_active') }}</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
        </select>
        <select name="stock" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" onchange="this.form.submit()">
            <option value="">{{ __('admin.products.filter_stock') }}</option>
            <option value="in_stock" @selected($filters['stock'] === 'in_stock')>{{ __('admin.stock.in_stock') }}</option>
            <option value="low_stock" @selected($filters['stock'] === 'low_stock')>{{ __('admin.stock.low_stock') }}</option>
            <option value="out_of_stock" @selected($filters['stock'] === 'out_of_stock')>{{ __('admin.stock.out_of_stock') }}</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.image') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.product') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.sku') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.barcode') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.category') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.price') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.stock') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.status') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2">
                            <img src="{{ $product['image'] }}" alt="" width="36" height="44" class="h-11 w-9 object-cover">
                        </td>
                        <td class="px-3 py-2 text-foreground">{{ $product['name'] }}</td>
                        <td class="px-3 py-2 text-muted-foreground">{{ $product['sku'] }}</td>
                        <td class="px-3 py-2 text-muted-foreground">{{ $product['barcode'] }}</td>
                        <td class="px-3 py-2 text-muted-foreground">{{ $product['category'] }}</td>
                        <td class="px-3 py-2 text-foreground">{{ \App\Support\AdminStore::money($product['price']) }}</td>
                        <td class="px-3 py-2">
                            <span class="text-foreground">{{ $product['stock'] }}</span>
                            <x-admin.badge class="ml-2" :status="$product['stock_status']" />
                        </td>
                        <td class="px-3 py-2 text-muted-foreground">{{ __('admin.products.status_'.$product['status']) }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.products.edit', $product['slug']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.edit') }}">
                                    <x-icon name="edit" size="size-3.5" />
                                </a>
                                <a href="{{ route('admin.products.edit', $product['slug']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.view') }}">
                                    <x-icon name="eye" size="size-3.5" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
