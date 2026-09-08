<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_percent_discount_reduces_a_thousand_to_eight_hundred(): void
    {
        [$product, $variant] = $this->pricedProduct(1000);
        $discount = Discount::factory()->percent(20)->create();
        $discount->products()->attach($product->id);

        $quote = app(DiscountService::class)->quote($product->fresh(['category', 'brandRecord']), $variant);

        $this->assertSame('1000.00', $quote->unitOriginal);
        $this->assertSame('800.00', $quote->unitFinal);
        $this->assertSame('200.00', $quote->unitAmount);
        $this->assertSame(20, $quote->percent);
        $this->assertSame('product', $quote->scope);
    }

    public function test_inactive_discount_keeps_the_original_price(): void
    {
        [$product, $variant] = $this->pricedProduct(1000);
        $discount = Discount::factory()->percent(20)->inactive()->create();
        $discount->products()->attach($product->id);

        $quote = app(DiscountService::class)->quote($product->fresh(['category', 'brandRecord']), $variant);

        $this->assertFalse($quote->hasDiscount());
        $this->assertSame('1000.00', $quote->unitFinal);
    }

    public function test_upcoming_discount_keeps_the_original_price(): void
    {
        $this->freezeTime();
        [$product, $variant] = $this->pricedProduct(1000);
        $discount = Discount::factory()->percent(20)->upcoming()->create();
        $discount->products()->attach($product->id);

        $quote = app(DiscountService::class)->quote($product->fresh(['category', 'brandRecord']), $variant);

        $this->assertFalse($quote->hasDiscount());
        $this->assertSame('1000.00', $quote->unitFinal);
    }

    public function test_expired_discount_keeps_the_original_price(): void
    {
        $this->freezeTime();
        [$product, $variant] = $this->pricedProduct(1000);
        $discount = Discount::factory()->percent(20)->expired()->create();
        $discount->products()->attach($product->id);

        $quote = app(DiscountService::class)->quote($product->fresh(['category', 'brandRecord']), $variant);

        $this->assertFalse($quote->hasDiscount());
        $this->assertSame('1000.00', $quote->unitFinal);
    }

    public function test_product_discount_wins_over_category_and_brand_discounts(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'base_price' => 1000,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 1000,
        ]);

        $productDiscount = Discount::factory()->percent(10)->create(['name' => 'Product 10']);
        $categoryDiscount = Discount::factory()->percent(20)->create(['name' => 'Category 20']);
        $brandDiscount = Discount::factory()->percent(15)->create(['name' => 'Brand 15']);

        $productDiscount->products()->attach($product->id);
        $categoryDiscount->categories()->attach($category->id);
        $brandDiscount->brands()->attach($brand->id);

        $quote = app(DiscountService::class)->quote($product->fresh(['category', 'brandRecord']), $variant);

        $this->assertSame('900.00', $quote->unitFinal);
        $this->assertSame('product', $quote->scope);
        $this->assertSame('Product 10', $quote->name);
    }

    public function test_category_discount_applies_to_nested_products_without_per_product_rows(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        $product = Product::factory()->create([
            'category_id' => $child->id,
            'base_price' => 1000,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 1000,
        ]);
        $discount = Discount::factory()->percent(30)->create();
        $discount->categories()->attach($parent->id);

        $quote = app(DiscountService::class)->quote($product->fresh(['category', 'brandRecord']), $variant);

        $this->assertSame('700.00', $quote->unitFinal);
        $this->assertSame('category', $quote->scope);
    }

    /**
     * @return array{0: Product, 1: ProductVariant}
     */
    private function pricedProduct(float $price): array
    {
        $product = Product::factory()->create(['base_price' => $price]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => $price,
        ]);

        return [$product, $variant];
    }
}
