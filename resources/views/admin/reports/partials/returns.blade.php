@include('admin.reports.partials.metrics', ['report' => $report])

<div class="mt-4">
    @include('admin.reports.partials.table', ['report' => $report])
</div>
