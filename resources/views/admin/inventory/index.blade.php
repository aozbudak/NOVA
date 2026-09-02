@extends('layouts.admin')

@section('title', __('admin.inventory.title'))

@section('content')
    <x-admin.page-header :title="__('admin.inventory.title')">
        <x-slot:actions>
            <x-admin.button variant="secondary" type="button" data-open-layer="stock-adjust">{{ __('admin.inventory.adjust') }}</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.inventory.movements')">{{ __('admin.inventory.movements') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.inventory.index')" :chips="$chips" :columns="3">
        <x-admin.input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.inventory.search') }}" />
        <x-admin.select name="category">
            <option value="">{{ __('admin.inventory.filter_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.select name="stock">
            <option value="">{{ __('admin.inventory.filter_stock') }}</option>
            <option value="in_stock" @selected(request('stock') === 'in_stock')>{{ __('admin.stock.in_stock') }}</option>
            <option value="low_stock" @selected(request('stock') === 'low_stock')>{{ __('admin.stock.low_stock') }}</option>
            <option value="out_of_stock" @selected(request('stock') === 'out_of_stock')>{{ __('admin.stock.out_of_stock') }}</option>
        </x-admin.select>
    </x-admin.filters>

    <x-admin.table :paginator="$rows">
        <x-slot:head>
            <x-admin.th sort="product">{{ __('admin.inventory.product') }}</x-admin.th>
            <x-admin.th>{{ __('admin.inventory.variant') }}</x-admin.th>
            <x-admin.th sort="sku">{{ __('admin.inventory.sku') }}</x-admin.th>
            <x-admin.th sort="stock" align="end">{{ __('admin.inventory.current') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.inventory.min') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.inventory.status') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($rows as $row)
                <tr @class(['border-b border-border last:border-b-0', 'bg-muted/40' => $row['status'] !== 'in_stock'])>
                    <x-admin.td :label="__('admin.inventory.product')">{{ $row['product'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.variant')" tone="muted">{{ $row['variant'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.sku')" tone="muted">{{ $row['sku'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.current')" align="end">{{ $row['stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.min')" align="end" tone="muted">{{ $row['min_stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.status')"><x-admin.badge :status="$row['status']" /></x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>

    <x-admin.drawer name="stock-adjust" :title="__('admin.inventory.adjust')">
        <form method="POST" action="{{ route('admin.inventory.adjust') }}" class="flex flex-col gap-4">
            @csrf
            <x-admin.field :label="__('admin.inventory.sku')" name="sku" required>
                <x-admin.input name="sku" required />
            </x-admin.field>
            <x-admin.field :label="__('admin.inventory.quantity')" name="quantity" required>
                <x-admin.input name="quantity" type="number" required />
            </x-admin.field>
            <x-admin.field :label="__('admin.inventory.reason')" name="reason">
                <x-admin.input name="reason" />
            </x-admin.field>
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.processing') }}">{{ __('admin.common.save') }}</x-admin.button>
        </form>
    </x-admin.drawer>
@endsection
