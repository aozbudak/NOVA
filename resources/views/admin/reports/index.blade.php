@extends('layouts.admin')

@section('title', __('admin.reports.title'))

@section('content')
    <x-admin.page-header :title="__('admin.reports.title')" />

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($categories as $category)
            <a href="{{ route('admin.reports.show', $category['key']) }}" class="admin-card group flex items-center gap-3 rounded-2xl border p-4 transition-colors hover:bg-muted">
                <span class="flex size-10 items-center justify-center rounded-xl bg-muted">
                    <x-icon :name="$category['key']" size="size-4" />
                </span>
                <span class="min-w-0 flex-1 text-sm font-medium">{{ $category['label'] }}</span>
                <x-icon name="chevron-right" size="size-4" class="text-muted-foreground transition-transform group-hover:translate-x-0.5" />
            </a>
        @endforeach
    </div>
@endsection
