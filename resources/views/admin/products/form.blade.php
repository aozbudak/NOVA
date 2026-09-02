@extends('layouts.admin')

@section('title', $product ? __('admin.products.edit') : __('admin.products.add'))

@section('content')
    <x-admin.page-header :title="$product ? __('admin.products.edit') : __('admin.products.add')" />

    <form method="POST" action="{{ $product ? route('admin.products.update', $product['slug']) : route('admin.products.store') }}" class="flex max-w-4xl flex-col gap-6">
        @csrf
        @if ($product)
            @method('PUT')
        @endif

        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.basic') }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground md:col-span-2">
                    {{ __('admin.products.name') }}
                    <input name="name" value="{{ $product['name'] ?? '' }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground md:col-span-2">
                    {{ __('admin.products.description') }}
                    <textarea name="description" rows="3" class="rounded-md border border-input bg-background px-3 py-2 text-[13px] text-foreground">{{ $product['description'] ?? '' }}</textarea>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.category') }}
                    <select name="category" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(($product['category'] ?? '') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.brand') }}
                    <select name="brand" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                        @foreach ($brands as $brand)
                            <option value="{{ $brand }}" @selected(($product['brand'] ?? '') === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.status') }}
                    <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                        <option value="active" @selected(($product['status'] ?? 'active') === 'active')>{{ __('admin.products.status_active') }}</option>
                        <option value="inactive" @selected(($product['status'] ?? '') === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
                    </select>
                </label>
            </div>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.pricing') }}</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.purchase_price') }}
                    <input name="purchase_price" type="number" value="{{ $product['purchase_price'] ?? '' }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.sale_price') }}
                    <input name="price" type="number" value="{{ $product['price'] ?? '' }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.vat') }}
                    <input name="vat" type="number" value="{{ $product['vat'] ?? 20 }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
            </div>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.variants') }}</h2>
                <button type="button" data-variant-add class="inline-flex items-center gap-1 text-[12px] text-muted-foreground hover:text-foreground">
                    <x-icon name="plus" size="size-3.5" />
                    {{ __('admin.products.add_variant') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
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
                                <td class="py-2 pr-3"><input name="variants[color][]" value="{{ $variant['color'] }}" class="h-8 w-28 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3"><input name="variants[size][]" value="{{ $variant['size'] }}" class="h-8 w-16 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3"><input name="variants[sku][]" value="{{ $variant['sku'] }}" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3"><input name="variants[barcode][]" value="{{ $variant['barcode'] }}" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2 pr-3"><input name="variants[stock][]" type="number" value="{{ $variant['stock'] }}" class="h-8 w-20 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                                <td class="py-2"><input name="variants[price][]" type="number" value="{{ $variant['price'] }}" class="h-8 w-24 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
            <template data-variant-template>
                <tr class="border-b border-border last:border-b-0">
                    <td class="py-2 pr-3"><input name="variants[color][]" class="h-8 w-28 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3"><input name="variants[size][]" class="h-8 w-16 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3"><input name="variants[sku][]" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3"><input name="variants[barcode][]" class="h-8 w-36 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2 pr-3"><input name="variants[stock][]" type="number" class="h-8 w-20 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                    <td class="py-2"><input name="variants[price][]" type="number" class="h-8 w-24 rounded-md border border-input bg-background px-2 text-[13px]"></td>
                </tr>
            </template>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.images') }}</h2>
            <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                {{ __('admin.products.product_images') }}
                <input type="file" name="images[]" multiple accept="image/jpeg,image/webp" class="text-[13px] text-foreground">
                <span>{{ __('admin.products.upload_hint') }}</span>
            </label>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.products.inventory') }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.initial_stock') }}
                    <input name="initial_stock" type="number" value="{{ $product['stock'] ?? '' }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.products.minimum_stock') }}
                    <input name="min_stock" type="number" value="{{ $product['min_stock'] ?? '' }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
            </div>
        </section>

        <div class="flex items-center gap-2">
            <button type="submit" data-busy-label="{{ __('admin.common.saving') }}" class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-[12px] font-medium text-primary-foreground">{{ __('admin.common.save') }}</button>
            <a href="{{ route('admin.products.index') }}" class="inline-flex h-9 items-center rounded-md px-4 text-[12px] text-muted-foreground hover:text-foreground">{{ __('admin.common.cancel') }}</a>
        </div>
    </form>
@endsection
