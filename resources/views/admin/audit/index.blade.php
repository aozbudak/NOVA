@extends('layouts.admin')

@section('title', __('admin.audit.title'))

@section('content')
    <x-admin.page-header :title="__('admin.audit.title')" />

    <form method="GET" action="{{ route('admin.audit.index') }}" class="mb-4 grid gap-2 md:grid-cols-4" data-table-filter>
        <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.audit.search') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <select name="module" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.audit.module') }}</option>
            @foreach (['Products', 'Sales', 'Returns', 'Cash', 'Inventory'] as $module)
                <option value="{{ $module }}" @selected($filters['module'] === $module)>{{ $module }}</option>
            @endforeach
        </select>
        <select name="user" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.audit.user') }}</option>
            @foreach (['Admin', 'Ayşe Yılmaz', 'Mert Kaya', 'Deniz Aksoy'] as $user)
                <option value="{{ $user }}" @selected($filters['user'] === $user)>{{ $user }}</option>
            @endforeach
        </select>
        <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.audit.status') }}</option>
            <option value="success" @selected($filters['status'] === 'success')>{{ __('admin.status.success') }}</option>
            <option value="failure" @selected($filters['status'] === 'failure')>{{ __('admin.status.failure') }}</option>
        </select>
    </form>

    <div data-table-shell class="relative">
        <div data-table-skeleton hidden>
            <x-admin.skeleton.table />
        </div>
        <div data-table-body>
            @if ($logs->isEmpty())
                <div class="rounded-md border border-border bg-card">
                    <x-admin.empty :title="__('admin.empty.audit.title')">
                        {{ __('admin.empty.audit.body') }}
                    </x-admin.empty>
                </div>
            @else
                <div class="overflow-x-auto rounded-md border border-border bg-card">
                    <table class="w-full text-left text-[12px] leading-tight">
                        <thead class="border-b border-border text-[10px] tracking-wide text-muted-foreground uppercase">
                            <tr>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.datetime') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.user') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.action') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.module') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.reference') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.ip') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.endpoint') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.audit.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr data-audit-row class="cursor-pointer border-b border-border last:border-b-0 hover:bg-accent/40" data-open-detail>
                                    <td class="px-3 py-2 text-muted-foreground whitespace-nowrap">{{ $log['datetime'] }}</td>
                                    <td class="px-3 py-2 text-foreground">{{ $log['user'] }}</td>
                                    <td class="px-3 py-2 text-foreground">{{ $log['action'] }}</td>
                                    <td class="px-3 py-2 text-muted-foreground">{{ $log['module'] }}</td>
                                    <td class="px-3 py-2 text-foreground">{{ $log['reference'] }}</td>
                                    <td class="px-3 py-2 font-mono text-[11px] text-muted-foreground">{{ $log['ip'] }}</td>
                                    <td class="px-3 py-2 font-mono text-[11px] text-muted-foreground">{{ $log['endpoint'] }}</td>
                                    <td class="px-3 py-2"><x-admin.badge group="status" :status="$log['status']" /></td>
                                </tr>
                                <tr data-audit-detail hidden class="border-b border-border bg-muted/30">
                                    <td colspan="8" class="px-3 py-3">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <p class="text-[10px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.old') }}</p>
                                                <pre class="mt-1 text-[12px] text-foreground">{{ $log['old'] ? json_encode($log['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.audit.new') }}</p>
                                                <pre class="mt-1 text-[12px] text-foreground">{{ $log['new'] ? json_encode($log['new'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.audit.show', $log['id']) }}" class="mt-2 inline-block text-[12px] text-muted-foreground hover:text-foreground">{{ __('admin.common.view') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
