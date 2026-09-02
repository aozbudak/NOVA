<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Support\Catalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->whereNotNull('catalog_code')->exists()) {
            return;
        }

        foreach ((new Catalog)->all() as $item) {
            $department = Category::query()->firstOrCreate(
                ['slug' => $item['category']],
                [
                    'name' => Str::headline($item['category']),
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );

            $category = Category::query()->firstOrCreate(
                ['slug' => $item['category'].'-'.$item['type']],
                [
                    'parent_id' => $department->id,
                    'name' => Str::headline($item['type']),
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );

            $product = Product::query()->create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => $item['description'],
                'brand' => 'NOVA',
                'base_price' => $item['oldPrice'] ?? $item['price'],
                'sale_price' => $item['oldPrice'] !== null ? $item['price'] : null,
                'currency' => $item['currency'],
                'is_new' => $item['isNew'],
                'is_featured' => $item['featured'],
                'is_active' => true,
                'catalog_code' => $item['id'],
                'attributes' => [
                    'channel' => 'storefront',
                    'department' => $item['category'],
                    'type' => $item['type'],
                    'collection' => $item['collection'],
                    'colors' => $item['colors'],
                    'sizes' => $item['sizes'],
                    'stock' => $item['stock'],
                    'material' => $item['material'],
                ],
            ]);

            foreach ($item['images'] as $index => $url) {
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'image_url' => $url,
                    'alt_text' => $item['name'],
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }

            foreach ($item['sizes'] as $size) {
                $variant = ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'sku' => Str::upper($item['slug']).'-'.$size['code'],
                    'barcode' => null,
                    'color' => $item['colors'][0]['name'] ?? null,
                    'size' => $size['code'],
                    'price' => $item['price'],
                    'is_active' => true,
                ]);

                Stock::query()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $size['in_stock'] ? max(1, (int) $item['stock']) : 0,
                    'reserved_quantity' => 0,
                    'minimum_quantity' => 2,
                ]);
            }
        }
    }
}
