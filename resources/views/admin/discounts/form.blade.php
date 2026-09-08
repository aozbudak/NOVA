@php
    $selectedProducts = old('product_ids', $discount['product_ids'] ?? []);
    $selectedVariants = old('variant_ids', $discount['variant_ids'] ?? []);
    $selectedCategories = old('category_ids', $discount['category_ids'] ?? []);
    $selectedBrands = old('brand_ids', $discount['brand_ids'] ?? []);
@endphp

<x-admin.field :label="__('admin.discounts.name')" name="name" required>
    <x-admin.input name="name" value="{{ old('name', $discount['name'] ?? '') }}" required />
</x-admin.field>
<x-admin.field :label="__('admin.discounts.type')" name="type" required>
    <x-admin.select name="type">
        @foreach ($types as $type)
            <option value="{{ $type->value }}" @selected(old('type', $discount['type'] ?? 'percent') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </x-admin.select>
</x-admin.field>
<x-admin.field :label="__('admin.discounts.value')" name="value" required :help="__('admin.discounts.value_help')">
    <x-admin.input name="value" type="number" min="0" step="0.01" value="{{ old('value', $discount['value'] ?? '') }}" required />
</x-admin.field>
<div class="grid gap-4 md:grid-cols-2">
    <x-admin.field :label="__('admin.discounts.starts_at')" name="starts_at">
        <x-admin.input name="starts_at" type="datetime-local" value="{{ old('starts_at', $discount['starts_at'] ?? '') }}" />
    </x-admin.field>
    <x-admin.field :label="__('admin.discounts.ends_at')" name="ends_at">
        <x-admin.input name="ends_at" type="datetime-local" value="{{ old('ends_at', $discount['ends_at'] ?? '') }}" />
    </x-admin.field>
</div>
<x-admin.field :label="__('admin.discounts.status')" name="status" required>
    <x-admin.select name="status">
        <option value="active" @selected(old('status', $discount['status'] ?? 'active') === 'active')>{{ __('admin.products.status_active') }}</option>
        <option value="inactive" @selected(old('status', $discount['status'] ?? '') === 'inactive')>{{ __('admin.products.status_inactive') }}</option>
    </x-admin.select>
</x-admin.field>
<x-admin.field :label="__('admin.discounts.products')" name="product_ids" :help="__('admin.discounts.targets_help')">
    <x-admin.picker
        name="product_ids"
        :placeholder="__('admin.discounts.select_product')"
        :options="$products->map(fn ($product) => ['id' => $product->id, 'label' => $product->name])->all()"
        :selected="$selectedProducts"
    />
</x-admin.field>
<x-admin.field :label="__('admin.discounts.variants')" name="variant_ids">
    <x-admin.picker
        name="variant_ids"
        :placeholder="__('admin.discounts.select_variant')"
        :options="$variants->map(fn ($variant) => ['id' => $variant->id, 'label' => trim($variant->sku.' '.$variant->size.' '.$variant->color)])->all()"
        :selected="$selectedVariants"
    />
</x-admin.field>
<x-admin.field :label="__('admin.discounts.categories')" name="category_ids">
    <x-admin.picker
        name="category_ids"
        :placeholder="__('admin.discounts.select_category')"
        :options="$categories->map(fn ($category) => ['id' => $category->id, 'label' => $category->name])->all()"
        :selected="$selectedCategories"
    />
</x-admin.field>
<x-admin.field :label="__('admin.discounts.brands')" name="brand_ids">
    <x-admin.picker
        name="brand_ids"
        :placeholder="__('admin.discounts.select_brand')"
        :options="$brands->map(fn ($brand) => ['id' => $brand->id, 'label' => $brand->name])->all()"
        :selected="$selectedBrands"
    />
</x-admin.field>
