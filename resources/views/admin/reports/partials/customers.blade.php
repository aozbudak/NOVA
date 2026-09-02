@include('admin.reports.partials.metrics', ['report' => $report])

<div class="mt-4">
    <h2 class="mb-3 text-sm font-medium text-foreground">{{ __('admin.reports.charts.top_customers') }}</h2>
    @include('admin.reports.partials.table', ['report' => $report])
</div>
