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
                            @php
                                $value = is_array($category) ? ($category['id'] ?? $category['name']) : $category;
                                $label = is_array($category) ? ($category['label'] ?? $category['name']) : $category;
                            @endphp
                            <option value="{{ $value }}" @selected(old('category', $product['category_id'] ?? $product['category'] ?? '') == $value || old('category', $product['category'] ?? '') === (is_array($category) ? $category['name'] : $category))>{{ $label }}</option>
                        @endforeach
                    </x-admin.select>
                </x-admin.field>
                <x-admin.field :label="__('admin.products.brand')" name="brand">
                    <x-admin.select name="brand">
                        <option value="">{{ __('admin.products.filter_brand') }}</option>
                        @foreach ($brands as $brand)
                            @php
                                $value = is_array($brand) ? ($brand['id'] ?? $brand['name']) : $brand;
                                $label = is_array($brand) ? $brand['name'] : $brand;
                            @endphp
                            <option value="{{ $value }}" @selected(old('brand', $product['brand_id'] ?? $product['brand'] ?? '') == $value || old('brand', $product['brand'] ?? '') === $label)>{{ $label }}</option>
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

        <section class="admin-card rounded-2xl border p-4" data-vat-calculator>
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.pricing') }}</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <x-admin.field :label="__('admin.products.purchase_price')" name="purchase_price">
                    <x-admin.input name="purchase_price" type="number" step="0.01" min="0" value="{{ old('purchase_price', $product['purchase_price'] ?? '') }}" />
                </x-admin.field>
                <x-admin.field :label="__('admin.products.sale_price')" name="price" required>
                    <x-admin.input name="price" type="number" step="0.01" min="0" value="{{ old('price', $product['price'] ?? '') }}" required data-vat-gross />
                </x-admin.field>
                <x-admin.field :label="__('admin.products.vat')" name="vat">
                    <x-admin.select name="vat" data-vat-rate>
                        @foreach ($vatRates ?? ['0', '1', '8', '10', '18', '20'] as $rate)
                            <option value="{{ $rate }}" @selected((string) old('vat', $product['vat'] ?? 20) === (string) $rate)>{{ $rate }}%</option>
                        @endforeach
                    </x-admin.select>
                </x-admin.field>
            </div>
            <dl class="mt-4 grid gap-3 text-[13px] text-muted-foreground md:grid-cols-3">
                <div class="rounded-lg border border-border px-3 py-2">
                    <dt>{{ __('admin.products.price_ex_vat') }}</dt>
                    <dd class="mt-1 text-foreground" data-vat-net>{{ number_format((float) ($product['price_net'] ?? 0), 2, ',', '.') }} TL</dd>
                </div>
                <div class="rounded-lg border border-border px-3 py-2">
                    <dt>{{ __('admin.products.vat_amount') }}</dt>
                    <dd class="mt-1 text-foreground" data-vat-amount>{{ number_format((float) ($product['price_vat'] ?? 0), 2, ',', '.') }} TL</dd>
                </div>
                <div class="rounded-lg border border-border px-3 py-2">
                    <dt>{{ __('admin.products.price_inc_vat') }}</dt>
                    <dd class="mt-1 text-foreground" data-vat-inclusive>{{ number_format((float) ($product['price'] ?? 0), 2, ',', '.') }} TL</dd>
                </div>
            </dl>
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
                            <th class="py-2 pr-3 font-medium">{{ __('admin.products.price') }}</th>
                            <th class="py-2 font-medium">{{ __('admin.products.status') }}</th>
                        </tr>
                    </thead>
                    <tbody data-variant-list>
                        @forelse ($product['variants'] ?? [['color' => '', 'size' => '', 'sku' => '', 'barcode' => '', 'stock' => '', 'price' => '', 'is_active' => true]] as $variant)
                            <tr class="border-b border-border last:border-b-0">
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.color') }}"><input name="variants[color][]" value="{{ $variant['color'] }}" placeholder="{{ __('admin.products.color') }}" class="h-8 w-28 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.size') }}"><input name="variants[size][]" value="{{ $variant['size'] }}" placeholder="S / 42" class="h-8 w-16 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.sku') }}"><input name="variants[sku][]" value="{{ $variant['sku'] }}" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.barcode') }}"><input name="variants[barcode][]" value="{{ $variant['barcode'] }}" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.stock') }}"><input name="variants[stock][]" type="number" min="0" value="{{ $variant['stock'] }}" class="h-8 w-20 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3" data-label="{{ __('admin.products.price') }}"><input name="variants[price][]" type="number" step="0.01" min="0" value="{{ $variant['price'] }}" class="h-8 w-24 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2" data-label="{{ __('admin.products.status') }}">
                                    <select name="variants[is_active][]" class="h-8 rounded-md border border-input bg-background px-2 text-[13px]">
                                        <option value="1" @selected(($variant['is_active'] ?? true))>{{ __('admin.products.status_active') }}</option>
                                        <option value="0" @selected(! ($variant['is_active'] ?? true))>{{ __('admin.products.status_inactive') }}</option>
                                    </select>
                                </td>
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
                    <td class="py-2 pr-3" data-label="{{ __('admin.products.stock') }}"><input name="variants[stock][]" type="number" min="0" class="h-8 w-20 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3" data-label="{{ __('admin.products.price') }}"><input name="variants[price][]" type="number" step="0.01" min="0" class="h-8 w-24 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2" data-label="{{ __('admin.products.status') }}">
                        <select name="variants[is_active][]" class="h-8 rounded-md border border-input bg-background px-2 text-[13px]">
                            <option value="1">{{ __('admin.products.status_active') }}</option>
                            <option value="0">{{ __('admin.products.status_inactive') }}</option>
                        </select>
                    </td>
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
                <x-admin.field :label="__('admin.products.initial_stock')" name="initial_stock" :help="__('admin.products.initial_stock_help')">
                    <x-admin.input name="initial_stock" type="number" min="0" value="{{ old('initial_stock', $product['stock'] ?? '') }}" />
                </x-admin.field>
            </div>
        </section>

        <div class="flex items-center gap-2">
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            <x-admin.button variant="ghost" :href="route('admin.products.index')">{{ __('admin.common.cancel') }}</x-admin.button>
        </div>
    </form>
@endsection
