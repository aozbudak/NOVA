@extends('layouts.admin')

@section('title', __('admin.variants.title'))

@section('content')
    <x-admin.page-header :title="__('admin.variants.title')" />

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.variants.product') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.color') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.size') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.sku') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.barcode') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.stock') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.price') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.products.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($variants as $variant)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $variant['product'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $variant['color'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $variant['size'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $variant['sku'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $variant['barcode'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $variant['stock'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($variant['price']) }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge :status="$variant['stock_status']" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
