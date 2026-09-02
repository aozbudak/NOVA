@php
    $filters = $report['filters'] ?? [];
    $chips = \App\Support\AdminList::chips([
        'category' => $filters['category'] ?? '',
        'brand' => $filters['brand'] ?? '',
        'stock' => $filters['stock'] ?? '',
    ], [
        'category' => ['label' => __('admin.products.filter_category')],
        'brand' => ['label' => __('admin.products.filter_brand')],
        'stock' => [
            'label' => __('admin.products.filter_stock'),
            'value' => ($filters['stock'] ?? '') === '' ? '' : __('admin.stock.'.$filters['stock']),
        ],
    ]);
@endphp

<x-admin.filters :action="route('admin.reports.show', $report['key'])" :chips="$chips" :columns="3">
    <x-admin.select name="category">
        <option value="">{{ __('admin.products.filter_category') }}</option>
        @foreach ($report['categories'] as $category)
            <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="brand">
        <option value="">{{ __('admin.products.filter_brand') }}</option>
        @foreach ($report['brands'] as $brand)
            <option value="{{ $brand }}" @selected(($filters['brand'] ?? '') === $brand)>{{ $brand }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="stock">
        <option value="">{{ __('admin.products.filter_stock') }}</option>
        <option value="in_stock" @selected(($filters['stock'] ?? '') === 'in_stock')>{{ __('admin.stock.in_stock') }}</option>
        <option value="low_stock" @selected(($filters['stock'] ?? '') === 'low_stock')>{{ __('admin.stock.low_stock') }}</option>
        <option value="out_of_stock" @selected(($filters['stock'] ?? '') === 'out_of_stock')>{{ __('admin.stock.out_of_stock') }}</option>
    </x-admin.select>
</x-admin.filters>

@include('admin.reports.partials.metrics', ['report' => $report])

<div class="mt-4">
    <x-admin.table>
        <x-slot:head>
            @foreach ($report['headers'] as $header)
                <x-admin.th>{{ $header }}</x-admin.th>
            @endforeach
        </x-slot:head>
        <x-slot:body>
            @foreach ($report['status_rows'] as $row)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="$report['headers'][0] ?? ''">{{ $row['product'] }}</x-admin.td>
                    <x-admin.td :label="$report['headers'][1] ?? ''" tone="muted">{{ $row['sku'] }}</x-admin.td>
                    <x-admin.td :label="$report['headers'][2] ?? ''" tone="muted">{{ $row['variant'] }}</x-admin.td>
                    <x-admin.td :label="$report['headers'][3] ?? ''">{{ $row['stock'] }}</x-admin.td>
                    <x-admin.td :label="$report['headers'][4] ?? ''" tone="muted">{{ $row['min_stock'] }}</x-admin.td>
                    <x-admin.td :label="$report['headers'][5] ?? ''"><x-admin.badge :status="$row['status']" /></x-admin.td>
                    <x-admin.td :label="$report['headers'][6] ?? ''">{{ \App\Support\AdminStore::money($row['stock_value']) }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
</div>
