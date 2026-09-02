@extends('layouts.admin')

@section('title', __('admin.cash.open'))

@section('content')
    <x-admin.page-header :title="__('admin.cash.open')" />

    <form method="POST" action="{{ route('admin.cash.open.store') }}" class="flex max-w-md flex-col gap-4 rounded-md border border-border bg-card p-4">
        @csrf
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.cash.opening') }}
            <input name="opening" type="number" value="{{ $register['opening'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.cash.date') }}
            <input name="date" type="date" value="{{ $register['date'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.cash.user') }}
            <input name="user" value="{{ $register['user'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <button type="submit" class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-[13px] font-medium text-primary-foreground">{{ __('admin.cash.submit_open') }}</button>
    </form>
@endsection
