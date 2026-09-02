@extends('layouts.admin')

@section('title', __('admin.audit.title'))

@section('content')
    <x-admin.page-header :title="__('admin.audit.title')" />

    <x-admin.filters :action="route('admin.audit.index')" :chips="$chips" :columns="4">
        <x-admin.input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.audit.search') }}" />
        <x-admin.select name="module">
            <option value="">{{ __('admin.audit.module') }}</option>
            @foreach (['Products', 'Sales', 'Returns', 'Cash', 'Inventory'] as $module)
                <option value="{{ $module }}" @selected($filters['module'] === $module)>{{ $module }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.select name="user">
            <option value="">{{ __('admin.audit.user') }}</option>
            @foreach (['Admin', 'Ayşe Yılmaz', 'Mert Kaya', 'Deniz Aksoy'] as $user)
                <option value="{{ $user }}" @selected($filters['user'] === $user)>{{ $user }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.select name="status">
            <option value="">{{ __('admin.audit.status') }}</option>
            <option value="success" @selected($filters['status'] === 'success')>{{ __('admin.status.success') }}</option>
            <option value="failure" @selected($filters['status'] === 'failure')>{{ __('admin.status.failure') }}</option>
        </x-admin.select>
    </x-admin.filters>

    @if ($logs->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.audit.title')">
                {{ __('admin.empty.audit.body') }}
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$logs">
            <x-slot:head>
                <x-admin.th sort="datetime">{{ __('admin.audit.datetime') }}</x-admin.th>
                <x-admin.th sort="user">{{ __('admin.audit.user') }}</x-admin.th>
                <x-admin.th>{{ __('admin.audit.action') }}</x-admin.th>
                <x-admin.th sort="module">{{ __('admin.audit.module') }}</x-admin.th>
                <x-admin.th>{{ __('admin.audit.reference') }}</x-admin.th>
                <x-admin.th>{{ __('admin.audit.ip') }}</x-admin.th>
                <x-admin.th>{{ __('admin.audit.endpoint') }}</x-admin.th>
                <x-admin.th sort="status">{{ __('admin.audit.status') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($logs as $log)
                    <tr data-audit-row class="cursor-pointer border-b border-border last:border-b-0 hover:bg-accent/40">
                        <x-admin.td :label="__('admin.audit.datetime')" tone="muted" class="whitespace-nowrap">{{ $log['datetime'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.audit.user')">{{ $log['user'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.audit.action')">{{ $log['action'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.audit.module')" tone="muted">{{ $log['module'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.audit.reference')">{{ $log['reference'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.audit.ip')" tone="muted" class="font-mono text-[11px]">{{ $log['ip'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.audit.endpoint')" tone="muted" class="font-mono text-[11px]">{{ $log['endpoint'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.audit.status')"><x-admin.badge group="status" :status="$log['status']" /></x-admin.td>
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
            </x-slot:body>
        </x-admin.table>
    @endif
@endsection
