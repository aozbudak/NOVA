<form method="GET" action="{{ route('admin.reports.show', $report['key']) }}" class="mb-6 flex flex-wrap items-end gap-2">
    <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
        {{ __('admin.dashboard.range.from') }}
        <input type="date" name="from" value="{{ $report['from'] }}" class="h-8 rounded-md border border-input bg-background px-2 text-[13px] text-foreground">
    </label>
    <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
        {{ __('admin.dashboard.range.to') }}
        <input type="date" name="to" value="{{ $report['to'] }}" class="h-8 rounded-md border border-input bg-background px-2 text-[13px] text-foreground">
    </label>
    <input type="hidden" name="date" value="{{ $report['date'] }}">
    <button type="submit" class="h-8 rounded-md border border-border bg-card px-3 text-[12px] text-foreground hover:bg-accent">{{ __('admin.dashboard.range.apply') }}</button>
</form>

@include('admin.reports.partials.metrics', ['report' => $report])

@if (! empty($report['trend']))
    <section class="mt-6 rounded-md border border-border bg-card p-4">
        <h2 class="text-sm font-medium text-foreground">{{ __('admin.reports.charts.movements') }}</h2>
        <x-admin.sparkline class="mt-3" :series="$report['trend']" />
    </section>
@endif

<div class="mt-4">
    @include('admin.reports.partials.table', ['report' => $report])
</div>
