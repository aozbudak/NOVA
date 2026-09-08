<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SaleReturn;
use App\Models\Stock;
use App\Support\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountSaleFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_exposes_the_discounted_storefront_price(): void
    {
        [$product] = $this->cataloguedProduct(1000, 101);
        $discount = Discount::factory()->percent(20)->create();
        $discount->products()->attach($product->id);

        $mapped = app(Catalog::class)->find(101);

        $this->assertNotNull($mapped);
        $this->assertSame(800.0, $mapped['price']);
        $this->assertSame(1000.0, $mapped['oldPrice']);
        $this->assertSame(20, $mapped['discountPercent']);
    }

    public function test_pos_sale_stores_the_discounted_order_total(): void
    {
        $variant = $this->variantWithStock(5, 1000);
        $discount = Discount::factory()->percent(20)->create();
        $discount->products()->attach($variant->product_id);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'card',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.total', 800);

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame('1000.00', $order->subtotal);
        $this->assertSame('200.00', $order->discount_amount);
        $this->assertSame('800.00', $order->total_amount);
        $this->assertSame('200.00', $order->items()->value('discount_amount'));
        $this->assertSame('800.00', $order->items()->value('total_price'));
    }

    public function test_checkout_recalculates_the_discount_on_the_backend(): void
    {
        [$product, $variant] = $this->cataloguedProduct(1000, 101);
        $discount = Discount::factory()->percent(20)->create(['name' => 'Spring 20']);
        $discount->products()->attach($product->id);

        $this->post(route('cart.store'), [
            'product_id' => 101,
            'size' => $variant->size,
            'quantity' => 1,
        ])->assertRedirect();

        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect(route('checkout.confirmation'));

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame('1000.00', $order->subtotal);
        $this->assertSame('200.00', $order->discount_amount);
        $this->assertSame('800.00', $order->total_amount);
        $this->assertSame('Spring 20', $order->items()->value('discount_name'));
        $this->assertSame('800.00', $order->items()->value('total_price'));
        $this->assertTrue(Payment::query()->where('amount', 800)->exists());
    }

    public function test_return_refunds_the_discounted_sale_price(): void
    {
        $variant = $this->variantWithStock(5, 1000);
        $discount = Discount::factory()->percent(20)->create();
        $discount->products()->attach($variant->product_id);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'card',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1],
            ],
        ])->assertCreated();

        $order = Order::query()->first();
        $this->assertNotNull($order);

        $this->postJson(route('api.returns.store'), [
            'sale' => $order->order_number,
            'reason' => 'wrong_size',
        ])->assertCreated();

        $return = SaleReturn::query()->first();
        $this->assertNotNull($return);
        $this->assertSame('800.00', $return->total_amount);
        $this->assertSame('800.00', $return->items()->value('total_price'));
        $this->assertSame('800.00', $return->items()->value('unit_price'));
        $this->assertTrue(Payment::query()->where('status', 'refunded')->where('amount', 800)->exists());
    }

    /**
     * @return array{0: Product, 1: ProductVariant}
     */
    private function cataloguedProduct(float $price, int $catalogCode): array
    {
        $product = Product::factory()->create([
            'name' => 'T-Shirt',
            'base_price' => $price,
            'catalog_code' => $catalogCode,
            'is_active' => true,
            'vat_rate' => 20,
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'NOVA-TS-M',
            'size' => 'M',
            'color' => 'Black',
            'price' => $price,
            'is_active' => true,
        ]);
        Stock::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_quantity' => 0,
        ]);

        return [$product, $variant];
    }

    private function variantWithStock(int $quantity, float $price): ProductVariant
    {
        $product = Product::factory()->create([
            'name' => 'T-Shirt',
            'brand' => 'Nike',
            'base_price' => $price,
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'NIK-TS-BLK-M',
            'barcode' => '8680001999999',
            'color' => 'Siyah',
            'size' => 'M',
            'price' => $price,
            'is_active' => true,
        ]);

        Stock::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'reserved_quantity' => 0,
            'minimum_quantity' => 0,
        ]);

        return $variant->fresh(['stock', 'product.category', 'product.brandRecord']) ?? $variant;
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(): array
    {
        return [
            'email' => 'client@nova.example',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'address' => '12 Atelier Street',
            'city' => 'Berlin',
            'postal_code' => '10115',
            'country' => 'Germany',
            'delivery' => 'standard',
            'payment' => 'card',
        ];
    }
}
