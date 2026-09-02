@extends('layouts.admin')

@section('title', $product ? __('admin.products.edit') : __('admin.products.add'))

@section('content')
    <x-admin.page-header :title="$product ? __('admin.products.edit') : __('admin.products.add')" />

    <form method="POST" action="{{ $product ? route('admin.products.update', $product['slug']) : route('admin.products.store') }}" class="flex max-w-4xl flex-col gap-6">
        @csrf
        @if ($product)
            @method('PUT')
        @endif

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.basic') }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <x-admin.field class="md:col-span-2" :label="__('admin.products.name')" name="name" required>
                    <x-admin.input name="name" value="{{ $product['name'] ?? '' }}" required />
                </x-admin.field>
                <x-admin.field class="md:col-span-2" :label="__('admin.products.description')" name="description">
                    <x-admin.textarea name="description" rows="3">{{ $product['description'] ?? '' }}</x-admin.textarea>
                </x-admin.field>
                <x-admin.field :label="__('admin.products.category')" name="category" required>
                    <x-admin.select name="category">
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(($product['category'] ?? '') === $category)>{{ $category }}</option>
                        @endforeach
                    </x-admin.select>
                </x-admin.field>
                <x-admin.field :label="__('admin.products.brand')" name="brand">
                    <x-admin.select name="brand">
                        @foreach ($brands as $brand)
                            <option value="{{ $brand }}" @selected(($product['brand'] ?? '') === $brand)>{{ $brand }}</option>
                        @endforeach
                    </x-admin.select>
                </x-admin.field>
                <x-admin.field :label="__('admin.products.status')" name="status">
                    <x-admin.select name="status">
                        <option value="active" @selected(($product['status'] ?? 'active') === 'active')>{{ __('admin.products.status_active') }}</option>
                        <option value="inactive" @selected(($product['status'] ?? '') === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
                    </x-admin.select>
                </x-admin.field>
            </div>
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.pricing') }}</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <x-admin.field :label="__('admin.products.purchase_price')" name="purchase_price">
                    <x-admin.input name="purchase_price" type="number" value="{{ $product['purchase_price'] ?? '' }}" />
                </x-admin.field>
                <x-admin.field :label="__('admin.products.sale_price')" name="price" required>
                    <x-admin.input name="price" type="number" value="{{ $product['price'] ?? '' }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.products.vat')" name="vat">
                    <x-admin.input name="vat" type="number" value="{{ $product['vat'] ?? 20 }}" />
                </x-admin.field>
            </div>
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.variants') }}</h2>
                <button type="button" data-variant-add class="inline-flex items-center gap-1 text-[12px] text-muted-foreground hover:text-foreground">
                    <x-icon name="plus" size="size-3.5" />
                    {{ __('admin.products.add_variant') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table data-admin-table class="w-full min-w-[40rem] text-left text-[13px]">
                    <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                        <tr>
                            <th class="py-2 pr-3 font-medium">{{ __('admin.products.color') }}</th>
                            <th class="py-2 pr-3 font-medium">{{ __('admin.products.size') }}</th>
                            <th class="py-2 pr-3 font-medium">{{ __('admin.products.sku') }}</th>
                            <th class="py-2 pr-3 font-medium">{{ __('admin.products.barcode') }}</th>
                            <th class="py-2 pr-3 font-medium">{{ __('admin.products.stock') }}</th>
                            <th class="py-2 font-medium">{{ __('admin.products.price') }}</th>
                        </tr>
                    </thead>
                    <tbody data-variant-list>
                        @forelse ($product['variants'] ?? [['color' => '', 'size' => '', 'sku' => '', 'barcode' => '', 'stock' => '', 'price' => '']] as $variant)
                            <tr class="border-b border-border last:border-b-0">
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.color') }}"><input name="variants[color][]" value="{{ $variant['color'] }}" class="h-8 w-28 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.size') }}"><input name="variants[size][]" value="{{ $variant['size'] }}" class="h-8 w-16 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.sku') }}"><input name="variants[sku][]" value="{{ $variant['sku'] }}" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.barcode') }}"><input name="variants[barcode][]" value="{{ $variant['barcode'] }}" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.stock') }}"><input name="variants[stock][]" type="number" value="{{ $variant['stock'] }}" class="h-8 w-20 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2" data-label="{{ __('admin.products.price') }}"><input name="variants[price][]" type="number" value="{{ $variant['price'] }}" class="h-8 w-24 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
            <template data-variant-template>
                <tr class="border-b border-border last:border-b-0">
                    <td class="py-2 pr-3" data-label="{{ __('admin.products.color') }}"><input name="variants[color][]" class="h-8 w-28 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3" data-label="{{ __('admin.products.size') }}"><input name="variants[size][]" class="h-8 w-16 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3" data-label="{{ __('admin.products.sku') }}"><input name="variants[sku][]" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3" data-label="{{ __('admin.products.barcode') }}"><input name="variants[barcode][]" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3" data-label="{{ __('admin.products.stock') }}"><input name="variants[stock][]" type="number" class="h-8 w-20 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2" data-label="{{ __('admin.products.price') }}"><input name="variants[price][]" type="number" class="h-8 w-24 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                </tr>
            </template>
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.images') }}</h2>
            <x-admin.field :label="__('admin.products.product_images')" name="images" :help="__('admin.products.upload_hint')">
                <x-admin.input type="file" name="images[]" multiple accept="image/jpeg,image/webp" />
            </x-admin.field>
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.inventory') }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <x-admin.field :label="__('admin.products.initial_stock')" name="initial_stock">
                    <x-admin.input name="initial_stock" type="number" value="{{ $product['stock'] ?? '' }}" />
                </x-admin.field>
                <x-admin.field :label="__('admin.products.minimum_stock')" name="min_stock">
                    <x-admin.input name="min_stock" type="number" value="{{ $product['min_stock'] ?? '' }}" />
                </x-admin.field>
            </div>
        </section>

        <div class="flex items-center gap-2">
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            <x-admin.button variant="ghost" :href="route('admin.products.index')">{{ __('admin.common.cancel') }}</x-admin.button>
        </div>
    </form>
@endsection
