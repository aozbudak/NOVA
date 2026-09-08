<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_customer_orders_api_returns_401(): void
    {
        $this->getJson(route('api.orders.index'))->assertUnauthorized();
        $this->getJson(route('api.orders.show', 'NV-20260908-0001'))->assertUnauthorized();
    }

    public function test_customer_orders_api_lists_only_own_orders(): void
    {
        $this->registerAda();
        $this->completedOrderFor($this->ada(), ['order_number' => 'NV-20260908-0001']);
        Order::factory()->for(Customer::factory())->create(['order_number' => 'NV-FOREIGN-0001']);

        $this->getJson(route('api.orders.index'))
            ->assertOk()
            ->assertJsonPath('data.0.number', 'NV-20260908-0001')
            ->assertJsonPath('data.0.item_count', 1)
            ->assertJsonPath('data.0.payment_method', 'card')
            ->assertJsonPath('data.0.returnable', true)
            ->assertJsonMissing(['number' => 'NV-FOREIGN-0001']);
    }

    public function test_customer_order_detail_api_returns_owned_order(): void
    {
        $this->registerAda();
        $this->completedOrderFor($this->ada(), ['order_number' => 'NV-20260908-0001']);

        $this->getJson(route('api.orders.show', 'NV-20260908-0001'))
            ->assertOk()
            ->assertJsonPath('data.number', 'NV-20260908-0001')
            ->assertJsonPath('data.items.0.name', 'Basic Shirt')
            ->assertJsonPath('data.items.0.brand', 'NOVA')
            ->assertJsonPath('data.items.0.size', 'M')
            ->assertJsonPath('data.items.0.image', 'https://example.com/shirt.jpg');
    }

    public function test_customer_order_detail_api_returns_404_for_another_customers_order(): void
    {
        $this->registerAda();
        $foreign = Order::factory()->for(Customer::factory())->create(['order_number' => 'NV-FOREIGN-0001']);

        $this->getJson(route('api.orders.show', $foreign->order_number))->assertNotFound();
        $this->getJson(route('api.orders.show', $foreign->id))->assertNotFound();
        $this->get(route('account.orders.show', $foreign->order_number))->assertNotFound();
    }

    public function test_pos_sale_for_the_customer_appears_in_sales_history(): void
    {
        $this->registerAda();
        $this->ada()->update(['slug' => 'ada-lovelace']);
        $variant = $this->variantWithStock(5, 1250);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'card',
            'customer_id' => 'ada-lovelace',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1],
            ],
        ])->assertCreated();

        $number = Order::query()->value('order_number');

        $this->assertNotNull($number);

        $this->getJson(route('api.orders.index'))
            ->assertOk()
            ->assertJsonPath('data.0.number', $number)
            ->assertJsonPath('data.0.total', 1250);

        $this->get(route('account.orders'))
            ->assertSee($number)
            ->assertSee('Completed');

        $this->get(route('account.orders.show', $number))
            ->assertSee($number)
            ->assertSee('T-Shirt')
            ->assertSee('Request a return');
    }

    public function test_empty_order_history_renders_empty_state(): void
    {
        $this->registerAda();

        $this->get(route('account.orders'))
            ->assertSee('No orders yet')
            ->assertSee("You don't have any orders yet.");
    }

    private function registerAda(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    private function ada(): Customer
    {
        return Customer::query()->where('email', 'ada@nova.example')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function completedOrderFor(Customer $customer, array $overrides = []): Order
    {
        $product = Product::factory()->create([
            'name' => 'Basic Shirt',
            'brand' => 'NOVA',
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'NOVA01-'.fake()->unique()->bothify('??##'),
            'color' => 'White',
            'size' => 'M',
            'price' => 389,
            'is_active' => true,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image_url' => 'https://example.com/shirt.jpg',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        Stock::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'minimum_quantity' => 0,
        ]);

        $order = Order::factory()->for($customer)->create([
            'order_number' => 'NV-20260908-0001',
            'status' => 'completed',
            'subtotal' => 389,
            'total_amount' => 389,
            'currency' => 'TRY',
            ...$overrides,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Basic Shirt',
            'sku' => $variant->sku,
            'color' => 'White',
            'size' => 'M',
            'quantity' => 1,
            'unit_price' => 389,
            'discount_amount' => 0,
            'total_price' => 389,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'card',
            'amount' => 389,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        return $order->fresh(['items', 'payments', 'returns']) ?? $order;
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
            'color' => 'Black',
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

        return $variant->fresh(['stock', 'product']) ?? $variant;
    }
}
