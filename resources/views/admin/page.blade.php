@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :description="$description" />

    <section class="rounded-md border border-border bg-card px-4 py-10 text-center">
        <p class="text-sm text-muted-foreground">{{ $description }}</p>
    </section>
@endsection
