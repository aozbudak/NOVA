<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_discount_appears_in_the_storefront_header(): void
    {
        $discount = Discount::factory()->percent(20)->create(['name' => 'Autumn 20']);
        $discount->products()->attach($this->cataloguedProduct()->id);

        $this->get('/')
            ->assertOk()
            ->assertSee('Discounts')
            ->assertSee('Autumn 20');
    }

    public function test_discount_page_lists_only_that_discounts_products(): void
    {
        $onSale = $this->cataloguedProduct('Nike Air Max', 2001);
        $regular = $this->cataloguedProduct('Adidas Superstar', 2002);
        $discount = Discount::factory()->percent(20)->create(['name' => 'Nike 20']);
        $discount->products()->attach($onSale->id);

        $this->get(route('discounts.show', $discount))
            ->assertOk()
            ->assertSee('Nike 20')
            ->assertSee('Nike Air Max')
            ->assertSee('1 products');
    }

    public function test_inactive_discount_is_hidden_from_the_storefront(): void
    {
        $discount = Discount::factory()->percent(10)->inactive()->create(['name' => 'Hidden Deal']);

        $this->get('/')->assertDontSee('Hidden Deal');
        $this->get(route('discounts.show', $discount))->assertNotFound();
    }

    private function cataloguedProduct(string $name = 'T-Shirt', int $catalogCode = 101): Product
    {
        $product = Product::factory()->create([
            'name' => $name,
            'base_price' => 1000,
            'catalog_code' => $catalogCode,
            'is_active' => true,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'SKU-'.$catalogCode,
            'size' => 'M',
            'color' => 'Black',
            'price' => 1000,
            'is_active' => true,
        ]);
        Stock::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_quantity' => 0,
        ]);

        return $product;
    }
}
