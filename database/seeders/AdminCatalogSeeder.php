<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Support\AdminStore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()->whereNull('catalog_code')->delete();

        $store = new AdminStore;

        foreach ($store->sourceCatalog() as $item) {
            $category = Category::query()->firstOrCreate(
                ['slug' => 'admin-'.Str::slug($item['category'])],
                [
                    'name' => $item['category'],
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );

            $slug = $item['slug'];

            if (Product::query()->where('slug', $slug)->exists()) {
                $slug = Str::slug($item['name'].'-'.$item['sku']);
            }

            $brand = Brand::query()->firstOrCreate(
                ['slug' => Str::slug($item['brand']) ?: 'brand'],
                [
                    'name' => $item['brand'],
                    'is_active' => true,
                ],
            );

            $product = Product::query()->create([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $item['name'],
                'slug' => $slug,
                'description' => $item['description'],
                'brand' => $brand->name,
                'base_price' => $item['price'],
                'sale_price' => null,
                'vat_rate' => $item['vat'] ?? 20,
                'currency' => 'TRY',
                'is_new' => false,
                'is_featured' => false,
                'is_active' => $item['status'] === 'active',
                'catalog_code' => null,
                'attributes' => [
                    'channel' => 'admin',
                    'legacy_id' => $item['id'],
                    'sku' => $item['sku'],
                    'barcode' => $item['barcode'],
                    'purchase_price' => $item['purchase_price'],
                    'vat' => $item['vat'],
                    'min_stock' => $item['min_stock'],
                ],
            ]);

            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_url' => $item['image'],
                'alt_text' => $item['name'],
                'sort_order' => 0,
                'is_primary' => true,
            ]);

            foreach ($item['variants'] as $row) {
                $barcode = (string) $row['barcode'];

                while (ProductVariant::query()->where('barcode', $barcode)->exists()) {
                    $barcode .= '0';
                }

                $variant = ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'sku' => $row['sku'],
                    'barcode' => $barcode,
                    'color' => $row['color'],
                    'size' => $row['size'],
                    'price' => $row['price'],
                    'is_active' => true,
                ]);

                Stock::query()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $row['stock'],
                    'reserved_quantity' => 0,
                    'minimum_quantity' => $item['min_stock'],
                ]);
            }
        }
    }
}
