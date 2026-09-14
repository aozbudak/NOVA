@extends('layouts.admin')

@section('title', __('admin.categories.title'))

@section('content')
    <x-admin.page-header :title="__('admin.categories.title')">
        <x-slot:actions>
            <x-admin.button type="button" icon="plus" data-open-layer="add-category">{{ __('admin.categories.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card class="mb-6 overflow-hidden">
        <div class="border-b border-border px-5 py-4">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.categories.header') }}</h2>
            <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.categories.header_help') }}</p>
        </div>
        <div class="flex flex-col gap-4 p-5">
            @if ($headerCategories->isEmpty())
                <p class="text-[13px] text-muted-foreground">{{ __('admin.categories.header_empty') }}</p>
            @else
                <ul class="flex flex-col gap-2">
                    @foreach ($headerCategories as $item)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-border px-3 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-[13px] text-foreground">{{ $item['name'] }}</p>
                                @if (filled($item['parent'] ?? null))
                                    <p class="truncate text-[12px] text-muted-foreground">{{ $item['parent'] }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.categories.header.destroy', $item['id']) }}">
                                @csrf
                                @method('DELETE')
                                <x-admin.button type="submit" variant="ghost">{{ __('admin.categories.header_remove') }}</x-admin.button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($availableHeaderCategories->isNotEmpty())
                <form method="POST" action="{{ route('admin.categories.header.store') }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <x-admin.field class="min-w-56 flex-1" :label="__('admin.categories.header_select')" name="category_id" required>
                        <x-admin.select name="category_id" required>
                            <option value="">{{ __('admin.categories.header_select') }}</option>
                            @foreach ($availableHeaderCategories as $option)
                                <option value="{{ $option['id'] }}" @selected(old('category_id') === $option['id'])>
                                    {{ filled($option['parent'] ?? null) ? $option['parent'].' / '.$option['name'] : $option['name'] }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.field>
                    <x-admin.button type="submit" icon="plus">{{ __('admin.categories.header_add') }}</x-admin.button>
                </form>
            @endif
        </div>
    </x-admin.card>

    <x-admin.card class="mb-6 overflow-hidden">
        <div class="border-b border-border px-5 py-4">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.categories.covers') }}</h2>
            <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.categories.covers_help') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.categories.covers.update') }}" enctype="multipart/form-data" class="flex flex-col gap-5 p-5">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($covers as $cover)
                    <x-admin.field :label="$cover['label']" :name="'covers.'.$cover['slot']" :help="__('admin.categories.cover_hint')">
                        @if (filled($cover['image']))
                            <img src="{{ $cover['image'] }}" alt="" width="160" height="200" class="mb-3 h-[200px] w-full object-cover">
                        @endif
                        <x-admin.input type="file" :name="'covers['.$cover['slot'].']'" accept="image/jpeg,image/png,image/webp" />
                    </x-admin.field>
                @endforeach
            </div>
            @error('covers')
                <p class="text-[12px] text-destructive">{{ $message }}</p>
            @enderror
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.categories.cover_save') }}</x-admin.button>
        </form>
    </x-admin.card>

    <x-admin.table :paginator="$categories">
        <x-slot:head>
            <x-admin.th sort="name">{{ __('admin.categories.name') }}</x-admin.th>
            <x-admin.th>{{ __('admin.categories.parent') }}</x-admin.th>
            <x-admin.th sort="products" align="end">{{ __('admin.categories.products') }}</x-admin.th>
            <x-admin.th sort="stock" align="end">{{ __('admin.categories.stock') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.categories.status') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($categories as $category)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.categories.name')">{{ $category['name'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.parent')" tone="muted">{{ $category['parent'] ?? '—' }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.products')" align="end">{{ $category['products'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.stock')" align="end">{{ $category['stock'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.categories.status')" tone="muted">{{ __('admin.products.status_'.$category['status']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.common.actions')" align="end">
                        <x-admin.row-actions>
                            <x-admin.icon-button icon="edit" :label="__('admin.categories.edit')" type="button" data-open-layer="edit-category-{{ $category['id'] }}" />
                            <form method="POST" action="{{ route('admin.categories.destroy', $category['id']) }}" onsubmit="return confirm(@json(__('admin.categories.delete_confirm')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex size-8 items-center justify-center rounded-xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground" aria-label="{{ __('admin.categories.delete') }}">
                                    <x-icon name="delete" size="size-3.5" />
                                </button>
                            </form>
                        </x-admin.row-actions>
                    </x-admin.td>
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
            <x-admin.field :label="__('admin.categories.parents')" name="parent_ids" :help="__('admin.categories.parents_help')">
                <div class="grid gap-2 sm:grid-cols-2">
                    @php
                        $selectedParents = collect(old('parent_ids', old('parent_id') ? [old('parent_id')] : []));
                    @endphp
                    @foreach ($parents as $parent)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-md border border-border px-3 py-2 text-[13px] text-foreground hover:bg-accent has-[:checked]:border-foreground/15 has-[:checked]:bg-accent">
                            <input type="checkbox" name="parent_ids[]" value="{{ $parent['id'] }}" @checked($selectedParents->contains($parent['id'])) class="size-3.5 rounded-sm border-input">
                            {{ $parent['name'] }}
                        </label>
                    @endforeach
                </div>
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

    @foreach ($categories as $category)
        <x-admin.drawer :name="'edit-category-'.$category['id']" :title="__('admin.categories.edit')">
            <form method="POST" action="{{ route('admin.categories.update', $category['id']) }}" class="flex flex-col gap-4">
                @csrf
                @method('PUT')
                <x-admin.field :label="__('admin.categories.name')" name="name" required>
                    <x-admin.input name="name" value="{{ $category['name'] }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.categories.parent')" name="parent_id">
                    <x-admin.select name="parent_id">
                        <option value="">{{ __('admin.categories.root') }}</option>
                        @foreach ($parents as $parent)
                            @if ($parent['id'] !== $category['id'])
                                <option value="{{ $parent['id'] }}" @selected(($category['parent_id'] ?? null) === $parent['id'])>{{ $parent['name'] }}</option>
                            @endif
                        @endforeach
                    </x-admin.select>
                </x-admin.field>
                <x-admin.field :label="__('admin.categories.status')" name="status" required>
                    <x-admin.select name="status">
                        <option value="active" @selected($category['status'] === 'active')>{{ __('admin.products.status_active') }}</option>
                        <option value="inactive" @selected($category['status'] === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
                    </x-admin.select>
                </x-admin.field>
                <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            </form>
        </x-admin.drawer>
    @endforeach
@endsection
