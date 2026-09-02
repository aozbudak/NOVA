@extends('layouts.admin')

@section('title', __('admin.audit.detail'))

@section('content')
    <x-admin.page-header :title="$log['action']" :description="$log['reference']" />

    <dl class="mb-6 grid gap-3 admin-card rounded-2xl border p-4 text-[13px] sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <dt class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.datetime') }}</dt>
            <dd class="mt-1 text-foreground">{{ $log['datetime'] }}</dd>
        </div>
        <div>
            <dt class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.user') }}</dt>
            <dd class="mt-1 text-foreground">{{ $log['user'] }}</dd>
        </div>
        <div>
            <dt class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.module') }}</dt>
            <dd class="mt-1 text-foreground">{{ $log['module'] }}</dd>
        </div>
        <div>
            <dt class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.status') }}</dt>
            <dd class="mt-1"><x-admin.badge group="status" :status="$log['status']" /></dd>
        </div>
        <div>
            <dt class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.ip') }}</dt>
            <dd class="mt-1 font-mono text-foreground">{{ $log['ip'] }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.endpoint') }}</dt>
            <dd class="mt-1 font-mono text-foreground">{{ $log['endpoint'] }}</dd>
        </div>
    </dl>

    <div class="grid gap-4 md:grid-cols-2">
        <section class="admin-card rounded-2xl border p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.old') }}</h2>
            <pre class="mt-2 text-[13px] text-foreground">{{ $log['old'] ? json_encode($log['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
        </section>
        <section class="admin-card rounded-2xl border p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.new') }}</h2>
            <pre class="mt-2 text-[13px] text-foreground">{{ $log['new'] ? json_encode($log['new'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
        </section>
    </div>
@endsection
