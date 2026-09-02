@include('admin.reports.partials.metrics', ['report' => $report])

<section class="mt-6 admin-card rounded-2xl border p-4">
    <h2 class="text-sm font-medium text-foreground">{{ __('admin.reports.charts.top_suppliers') }}</h2>
    <ul class="mt-3 flex flex-col gap-3">
        @foreach ($report['top_suppliers'] as $item)
            <li>
                <div class="flex items-baseline justify-between gap-2 text-[13px]">
                    <span class="text-foreground">{{ $item['name'] }}</span>
                    <span class="text-muted-foreground">{{ \App\Support\AdminStore::money($item['amount']) }}</span>
                </div>
                <div class="mt-1 h-1 bg-muted">
                    <div class="h-1 bg-primary" style="width: {{ $item['share'] }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
</section>

<div class="mt-4">
    @include('admin.reports.partials.table', ['report' => $report])
</div>
