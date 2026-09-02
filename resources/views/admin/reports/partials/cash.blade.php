<x-admin.filters :action="route('admin.reports.show', $report['key'])" :columns="2">
    <x-admin.field :label="__('admin.dashboard.range.from')">
        <x-admin.input type="date" name="from" value="{{ $report['from'] }}" />
    </x-admin.field>
    <x-admin.field :label="__('admin.dashboard.range.to')">
        <x-admin.input type="date" name="to" value="{{ $report['to'] }}" />
    </x-admin.field>
    <input type="hidden" name="date" value="{{ $report['date'] }}">
</x-admin.filters>

@include('admin.reports.partials.metrics', ['report' => $report])

@if (! empty($report['trend']))
    <section class="mt-6 admin-card rounded-2xl border p-4">
        <h2 class="text-sm font-medium text-foreground">{{ __('admin.reports.charts.movements') }}</h2>
        <x-admin.sparkline class="mt-3" :series="$report['trend']" />
    </section>
@endif

<div class="mt-4">
    @include('admin.reports.partials.table', ['report' => $report])
</div>
