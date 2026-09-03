@extends('layouts.admin')

@section('title', __('admin.brands.title'))

@section('content')
    <x-admin.page-header :title="__('admin.brands.title')">
        <x-slot:actions>
            <x-admin.button type="button" icon="plus" data-open-layer="add-brand">{{ __('admin.brands.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :paginator="$brands">
        <x-slot:head>
            <x-admin.th sort="name">{{ __('admin.brands.name') }}</x-admin.th>
            <x-admin.th sort="products" align="end">{{ __('admin.brands.products') }}</x-admin.th>
            <x-admin.th sort="stock" align="end">{{ __('admin.brands.stock') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.brands.status') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($brands as $brand)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.brands.name')">{{ $brand['name'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.brands.products')" align="end">{{ $brand['products'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.brands.stock')" align="end">{{ $brand['stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.brands.status')" tone="muted">{{ __('admin.products.status_'.$brand['status']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.common.actions')" align="end">
                        <x-admin.row-actions>
                            <x-admin.icon-button icon="edit" :label="__('admin.brands.edit')" type="button" data-open-layer="edit-brand-{{ $brand['id'] }}" />
                            <form method="POST" action="{{ route('admin.brands.toggle', $brand['id']) }}">
                                @csrf
                                <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl text-[11px] text-muted-foreground hover:bg-muted hover:text-foreground">{{ $brand['status'] === 'active' ? __('admin.products.status_inactive') : __('admin.products.status_active') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.brands.destroy', $brand['id']) }}" onsubmit="return confirm(@json(__('admin.brands.delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground" aria-label="{{ __('admin.brands.delete') }}">
                                    <x-icon name="delete" size="size-3.5" />
                                </button>
                            </form>
                        </x-admin.row-actions>
                    </x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>

    <x-admin.drawer name="add-brand" :title="__('admin.brands.add')">
        <form method="POST" action="{{ route('admin.brands.store') }}" class="flex flex-col gap-4">
            @csrf
            <x-admin.field :label="__('admin.brands.name')" name="name" required>
                <x-admin.input name="name" value="{{ old('name') }}" required />
            </x-admin.field>
            <x-admin.field :label="__('admin.brands.slug')" name="slug">
                <x-admin.input name="slug" value="{{ old('slug') }}" />
            </x-admin.field>
            <x-admin.field :label="__('admin.brands.description')" name="description">
                <x-admin.textarea name="description" rows="3">{{ old('description') }}</x-admin.textarea>
            </x-admin.field>
            <x-admin.field :label="__('admin.brands.logo')" name="logo">
                <x-admin.input name="logo" value="{{ old('logo') }}" />
            </x-admin.field>
            <x-admin.field :label="__('admin.brands.status')" name="status" required>
                <x-admin.select name="status">
                    <option value="active" @selected(old('status', 'active') === 'active')>{{ __('admin.products.status_active') }}</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
                </x-admin.select>
            </x-admin.field>
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
        </form>
    </x-admin.drawer>

    @foreach ($brands as $brand)
        <x-admin.drawer :name="'edit-brand-'.$brand['id']" :title="__('admin.brands.edit')">
            <form method="POST" action="{{ route('admin.brands.update', $brand['id']) }}" class="flex flex-col gap-4">
                @csrf
                @method('PUT')
                <x-admin.field :label="__('admin.brands.name')" name="name" required>
                    <x-admin.input name="name" value="{{ $brand['name'] }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.brands.slug')" name="slug">
                    <x-admin.input name="slug" value="{{ $brand['slug'] ?? '' }}" />
                </x-admin.field>
                <x-admin.field :label="__('admin.brands.description')" name="description">
                    <x-admin.textarea name="description" rows="3">{{ $brand['description'] ?? '' }}</x-admin.textarea>
                </x-admin.field>
                <x-admin.field :label="__('admin.brands.logo')" name="logo">
                    <x-admin.input name="logo" value="{{ $brand['logo'] ?? '' }}" />
                </x-admin.field>
                <x-admin.field :label="__('admin.brands.status')" name="status" required>
                    <x-admin.select name="status">
                        <option value="active" @selected($brand['status'] === 'active')>{{ __('admin.products.status_active') }}</option>
                        <option value="inactive" @selected($brand['status'] === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
                    </x-admin.select>
                </x-admin.field>
                <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            </form>
        </x-admin.drawer>
    @endforeach
@endsection
