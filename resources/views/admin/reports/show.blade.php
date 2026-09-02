@extends('layouts.admin')

@section('title', $report['title'])

@section('content')
    <x-admin.page-header :title="$report['title']">
        <x-slot:actions>
            <details class="relative">
                <summary class="inline-flex h-8 cursor-pointer list-none items-center gap-1.5 rounded-md border border-border bg-card px-3 text-[12px] text-foreground hover:bg-accent">
                    <x-icon name="download" size="size-3.5" />
                    {{ __('admin.reports.export') }}
                </summary>
                <div class="absolute right-0 z-10 mt-1 min-w-32 rounded-md border border-border bg-card py-1 text-[12px] shadow-sm">
                    @foreach (['csv' => __('admin.reports.export_csv'), 'excel' => __('admin.reports.export_excel'), 'pdf' => __('admin.reports.export_pdf')] as $format => $label)
                        <a href="{{ route('admin.reports.export', ['report' => $report['key'], 'format' => $format] + request()->except('format')) }}" class="block px-3 py-1.5 text-foreground hover:bg-accent">{{ $label }}</a>
                    @endforeach
                </div>
            </details>
        </x-slot:actions>
    </x-admin.page-header>

    @include('admin.reports.partials.'.$report['key'], ['report' => $report])
@endsection
