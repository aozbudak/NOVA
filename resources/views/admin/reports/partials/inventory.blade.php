<form method="GET" action="{{ route('admin.reports.show', $report['key']) }}" class="mb-4 grid gap-2 md:grid-cols-3">
    <select name="category" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        <option value="">{{ __('admin.products.filter_category') }}</option>
        @foreach ($report['categories'] as $category)
            <option value="{{ $category }}" @selected(($report['filters']['category'] ?? '') === $category)>{{ $category }}</option>
        @endforeach
    </select>
    <select name="brand" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        <option value="">{{ __('admin.products.filter_brand') }}</option>
        @foreach ($report['brands'] as $brand)
            <option value="{{ $brand }}" @selected(($report['filters']['brand'] ?? '') === $brand)>{{ $brand }}</option>
        @endforeach
    </select>
    <select name="stock" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" onchange="this.form.submit()">
        <option value="">{{ __('admin.products.filter_stock') }}</option>
        <option value="in_stock" @selected(($report['filters']['stock'] ?? '') === 'in_stock')>{{ __('admin.stock.in_stock') }}</option>
        <option value="low_stock" @selected(($report['filters']['stock'] ?? '') === 'low_stock')>{{ __('admin.stock.low_stock') }}</option>
        <option value="out_of_stock" @selected(($report['filters']['stock'] ?? '') === 'out_of_stock')>{{ __('admin.stock.out_of_stock') }}</option>
    </select>
</form>

@include('admin.reports.partials.metrics', ['report' => $report])

<div class="mt-4 overflow-x-auto rounded-md border border-border bg-card">
    <table class="w-full text-left text-[13px]">
        <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
            <tr>
                @foreach ($report['headers'] as $header)
                    <th class="px-3 py-2 font-medium">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($report['status_rows'] as $row)
                <tr class="border-b border-border last:border-b-0">
                    <td class="px-3 py-2.5 text-foreground">{{ $row['product'] }}</td>
                    <td class="px-3 py-2.5 text-muted-foreground">{{ $row['sku'] }}</td>
                    <td class="px-3 py-2.5 text-muted-foreground">{{ $row['variant'] }}</td>
                    <td class="px-3 py-2.5 text-foreground">{{ $row['stock'] }}</td>
                    <td class="px-3 py-2.5 text-muted-foreground">{{ $row['min_stock'] }}</td>
                    <td class="px-3 py-2.5"><x-admin.badge :status="$row['status']" /></td>
                    <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($row['stock_value']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
