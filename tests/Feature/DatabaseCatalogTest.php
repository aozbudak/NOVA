<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\AdminStore;
use App\Support\Catalog;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_storefront_catalog_is_read_from_the_database(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->assertSame(20, Product::query()->whereNotNull('catalog_code')->count());

        $product = (new Catalog)->find(1);

        $this->assertNotNull($product);
        $this->assertSame('Structured Wool Coat', $product['name']);
        $this->assertTrue(Product::query()->where('slug', 'structured-wool-coat')->exists());
    }

    public function test_seeded_admin_catalog_is_read_from_the_database(): void
    {
        $this->seed(AdminCatalogSeeder::class);

        $this->assertTrue(Product::query()->where('slug', 'basic-shirt')->exists());
        $this->assertNotNull((new AdminStore)->product('basic-shirt'));
        $this->assertSame('NOVA01', (new AdminStore)->product('basic-shirt')['sku']);
    }

    public function test_empty_database_does_not_show_fixture_products(): void
    {
        $this->assertCount(0, (new AdminStore)->products());
        $this->assertCount(0, (new Catalog)->all());
        $this->assertNull((new AdminStore)->product('basic-shirt'));
        $this->assertNull((new Catalog)->find(1));
    }
}
