@extends('layouts.admin')

@section('title', $report['title'])

@section('content')
    <x-admin.page-header :title="$report['title']">
        <x-slot:actions>
            <details class="relative">
                <summary class="inline-flex h-8 cursor-pointer list-none items-center gap-1.5 rounded-lg border border-border bg-card px-3 text-[12px] text-foreground hover:bg-accent">
                    <x-icon name="download" size="size-3.5" />
                    {{ __('admin.reports.export') }}
                </summary>
                <div class="admin-popover absolute right-0 z-10 mt-2 min-w-40 overflow-hidden rounded-xl border py-1.5 text-[12px]">
                    @foreach (['csv' => __('admin.reports.export_csv'), 'excel' => __('admin.reports.export_excel'), 'pdf' => __('admin.reports.export_pdf')] as $format => $label)
                        <a href="{{ route('admin.reports.export', ['report' => $report['key'], 'format' => $format] + request()->except('format')) }}" class="mx-1 block rounded-lg px-2.5 py-2 text-foreground hover:bg-accent">{{ $label }}</a>
                    @endforeach
                </div>
            </details>
        </x-slot:actions>
    </x-admin.page-header>

    @include('admin.reports.partials.'.$report['key'], ['report' => $report])
@endsection
