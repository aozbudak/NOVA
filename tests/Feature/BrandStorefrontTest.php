<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_brand_that_appears_in_the_header_and_api(): void
    {
        $this->post(route('admin.brands.store'), [
            'name' => 'ZARA',
            'description' => 'Contemporary fashion',
            'status' => 'active',
        ])->assertRedirect(route('admin.brands.index'));

        $brand = Brand::query()->where('slug', 'zara')->first();
        $this->assertNotNull($brand);
        $this->assertTrue($brand->is_active);

        $this->get('/')
            ->assertOk()
            ->assertSee('ZARA');

        $this->get(route('api.brands.index'))
            ->assertOk()
            ->assertJsonFragment(['slug' => 'zara', 'name' => 'ZARA']);
    }

    public function test_brand_page_lists_only_that_brands_products(): void
    {
        $nike = Brand::factory()->create(['name' => 'Nike', 'slug' => 'nike', 'is_active' => true]);
        $adidas = Brand::factory()->create(['name' => 'Adidas', 'slug' => 'adidas', 'is_active' => true]);
        $category = Category::factory()->create(['name' => 'Ayakkabı', 'slug' => 'shoes']);

        $airMax = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $nike->id,
            'brand' => 'Nike',
            'name' => 'Nike Air Max',
            'slug' => 'nike-air-max',
            'catalog_code' => 2001,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $adidas->id,
            'brand' => 'Adidas',
            'name' => 'Adidas Superstar',
            'slug' => 'adidas-superstar',
            'catalog_code' => 2002,
            'is_active' => true,
        ]);

        $this->attachVariant($airMax);

        $this->get(route('brands.show', 'nike'))
            ->assertOk()
            ->assertSee('Nike Air Max')
            ->assertSee('1 products');

        $this->get(route('api.catalog.index', ['brand' => 'nike']))
            ->assertOk()
            ->assertJsonFragment(['slug' => 'nike-air-max'])
            ->assertJsonMissing(['slug' => 'adidas-superstar']);
    }

    public function test_inactive_brand_is_hidden_from_the_storefront_menu(): void
    {
        $brand = Brand::factory()->create(['name' => 'Puma', 'slug' => 'puma', 'is_active' => true]);

        $this->get('/')->assertSee('Puma');

        $this->post(route('admin.brands.toggle', $brand->id))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertFalse($brand->fresh()->is_active);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('>Puma<', false);

        $this->get(route('brands.show', 'puma'))->assertNotFound();
    }

    public function test_deleting_a_brand_keeps_the_product_without_a_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'Mango', 'slug' => 'mango']);
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'brand' => 'Mango',
        ]);

        $this->delete(route('admin.brands.destroy', $brand->id))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertNull($product->fresh()->brand_id);
        $this->assertNull(Brand::query()->find($brand->id));
    }

    public function test_product_form_lists_database_brands(): void
    {
        Brand::factory()->create(['name' => 'Levi\'s', 'slug' => 'levis', 'is_active' => true]);

        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Levi\'s');
    }

    private function attachVariant(Product $product): void
    {
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'NIKE-BLK-42',
            'color' => 'Siyah',
            'size' => '42',
            'is_active' => true,
        ]);

        Stock::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 8,
            'reserved_quantity' => 0,
            'minimum_quantity' => 0,
        ]);
    }
}
