@extends('layouts.admin')

@section('title', $report['title'])

@section('content')
    <x-admin.page-header :title="$report['title']" />

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.reports.metric') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.reports.value') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['label'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
