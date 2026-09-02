@extends('layouts.admin')

@section('title', __('admin.reports.title'))

@section('content')
    <x-admin.page-header :title="__('admin.reports.title')" />

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($categories as $category)
            <a href="{{ route('admin.reports.show', $category['key']) }}" class="rounded-md border border-border bg-card p-4 hover:bg-accent">
                <p class="text-[13px] text-foreground">{{ $category['label'] }}</p>
            </a>
        @endforeach
    </div>
@endsection
