@extends('layouts.admin')

@section('title', __('admin.categories.title'))

@section('content')
    <x-admin.page-header :title="__('admin.categories.title')">
        <x-slot:actions>
            <x-admin.button type="button" icon="plus" data-open-layer="add-category">{{ __('admin.categories.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :paginator="$categories">
        <x-slot:head>
            <x-admin.th sort="name">{{ __('admin.categories.name') }}</x-admin.th>
            <x-admin.th sort="products" align="end">{{ __('admin.categories.products') }}</x-admin.th>
            <x-admin.th sort="stock" align="end">{{ __('admin.categories.stock') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.categories.status') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($categories as $category)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.categories.name')">{{ $category['name'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.products')" align="end">{{ $category['products'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.stock')" align="end">{{ $category['stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.status')" tone="muted">{{ __('admin.products.status_'.$category['status']) }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>

    <x-admin.drawer name="add-category" :title="__('admin.categories.add')">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="flex flex-col gap-4">
            @csrf
            <x-admin.field :label="__('admin.categories.name')" name="name" required>
                <x-admin.input name="name" value="{{ old('name') }}" required />
            </x-admin.field>
            <x-admin.field :label="__('admin.categories.status')" name="status" required>
                <x-admin.select name="status">
                    <option value="active" @selected(old('status', 'active') === 'active')>{{ __('admin.products.status_active') }}</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
                </x-admin.select>
            </x-admin.field>
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
        </form>
    </x-admin.drawer>
@endsection
