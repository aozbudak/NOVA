@extends('layouts.admin')

@section('title', __('admin.cash.open'))

@section('content')
    <x-admin.page-header :title="__('admin.cash.open')" />

    <form method="POST" action="{{ route('admin.cash.open.store') }}" class="flex max-w-md flex-col gap-4 admin-card rounded-2xl border p-4">
        @csrf
        <x-admin.field :label="__('admin.cash.opening')" name="opening" required>
            <x-admin.input name="opening" type="number" value="{{ $register['opening'] }}" required />
        </x-admin.field>
        <x-admin.field :label="__('admin.cash.date')" name="date" required>
            <x-admin.input name="date" type="date" value="{{ $register['date'] }}" required />
        </x-admin.field>
        <x-admin.field :label="__('admin.cash.user')" name="user">
            <x-admin.input name="user" value="{{ $register['user'] }}" />
        </x-admin.field>
        <x-admin.button type="submit">{{ __('admin.cash.submit_open') }}</x-admin.button>
    </form>
@endsection
