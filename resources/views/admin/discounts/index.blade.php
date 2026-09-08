@extends('layouts.admin')

@section('title', __('admin.discounts.title'))

@section('content')
    <x-admin.page-header :title="__('admin.discounts.title')">
        <x-slot:actions>
            <x-admin.button type="button" icon="plus" data-open-layer="add-discount">{{ __('admin.discounts.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($discounts->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.discounts.title')" icon="discounts">
                {{ __('admin.empty.discounts.body') }}
                <x-slot:action>
                    <x-admin.button type="button" data-open-layer="add-discount">{{ __('admin.discounts.add') }}</x-admin.button>
                </x-slot:action>
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$discounts">
            <x-slot:head>
                <x-admin.th sort="name">{{ __('admin.discounts.name') }}</x-admin.th>
                <x-admin.th sort="type">{{ __('admin.discounts.type') }}</x-admin.th>
                <x-admin.th sort="value" align="end">{{ __('admin.discounts.value') }}</x-admin.th>
                <x-admin.th>{{ __('admin.discounts.targets') }}</x-admin.th>
                <x-admin.th sort="starts_at">{{ __('admin.discounts.starts_at') }}</x-admin.th>
                <x-admin.th sort="ends_at">{{ __('admin.discounts.ends_at') }}</x-admin.th>
                <x-admin.th sort="status">{{ __('admin.discounts.status') }}</x-admin.th>
                <x-admin.th>{{ __('admin.discounts.creator') }}</x-admin.th>
                <x-admin.th sort="created_at">{{ __('admin.discounts.created_at') }}</x-admin.th>
                <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($discounts as $discount)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.discounts.name')">{{ $discount['name'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.discounts.type')">{{ $discount['type_label'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.discounts.value')" align="end">
                            {{ $discount['type'] === 'percent' ? '%'.$discount['value'] : \App\Support\AdminStore::money($discount['value']) }}
                        </x-admin.td>
                        <x-admin.td :label="__('admin.discounts.targets')" tone="muted">{{ $discount['targets_label'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.discounts.starts_at')" tone="muted">{{ $discount['starts_label'] ?? '—' }}</x-admin.td>
                        <x-admin.td :label="__('admin.discounts.ends_at')" tone="muted">{{ $discount['ends_label'] ?? '—' }}</x-admin.td>
                        <x-admin.td :label="__('admin.discounts.status')" tone="muted">{{ __('admin.products.status_'.$discount['status']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.discounts.creator')" tone="muted">{{ $discount['creator'] ?? '—' }}</x-admin.td>
                        <x-admin.td :label="__('admin.discounts.created_at')" tone="muted">{{ $discount['created_at'] ?? '—' }}</x-admin.td>
                        <x-admin.td :label="__('admin.common.actions')" align="end">
                            <x-admin.row-actions>
                                <x-admin.icon-button icon="edit" :label="__('admin.discounts.edit')" type="button" data-open-layer="edit-discount-{{ $discount['id'] }}" />
                                <form method="POST" action="{{ route('admin.discounts.toggle', $discount['id']) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl text-[11px] text-muted-foreground hover:bg-muted hover:text-foreground">{{ $discount['status'] === 'active' ? __('admin.products.status_inactive') : __('admin.products.status_active') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.discounts.destroy', $discount['id']) }}" onsubmit="return confirm(@json(__('admin.discounts.delete_confirm')))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground" aria-label="{{ __('admin.discounts.delete') }}">
                                        <x-icon name="delete" size="size-3.5" />
                                    </button>
                                </form>
                            </x-admin.row-actions>
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    @endif

    <x-admin.drawer name="add-discount" :title="__('admin.discounts.add')">
        <form method="POST" action="{{ route('admin.discounts.store') }}" class="flex flex-col gap-4">
            @csrf
            @include('admin.discounts.form', ['discount' => null])
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
        </form>
    </x-admin.drawer>

    @foreach ($discounts as $discount)
        <x-admin.drawer :name="'edit-discount-'.$discount['id']" :title="__('admin.discounts.edit')">
            <form method="POST" action="{{ route('admin.discounts.update', $discount['id']) }}" class="flex flex-col gap-4">
                @csrf
                @method('PUT')
                @include('admin.discounts.form', ['discount' => $discount])
                <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            </form>
        </x-admin.drawer>
    @endforeach

    @if ($errors->any())
        <div hidden data-open-layer-on-load="add-discount"></div>
    @endif
@endsection
