<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SaleReturn;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_sale_creates_order_payment_and_decrements_stock(): void
    {
        $this->travelTo('2026-09-06 10:00:00');
        $variant = $this->variantWithStock(10, 250);

        $response = $this->postJson(route('api.sales.store'), [
            'payment' => 'card',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.number', 'NV-20260906-0001');
        $response->assertJsonPath('data.status', 'completed');
        $response->assertJsonPath('data.total', 500);
        $response->assertJsonPath('message', 'Sale completed successfully.');

        $this->getJson(route('api.sales.show', 'NV-20260906-0001'))
            ->assertOk()
            ->assertJsonPath('data.number', 'NV-20260906-0001');

        $this->assertSame(8, (int) $variant->fresh()->stock?->quantity);
        $this->assertSame(1, Order::query()->count());
        $this->assertSame('completed', Order::query()->value('status'));
        $this->assertTrue(Payment::query()->where('status', 'completed')->where('amount', 500)->exists());
        $this->assertTrue(StockMovement::query()->where('movement_type', 'sale')->where('quantity', 2)->exists());
    }

    public function test_cash_sale_creates_a_cash_transaction_when_the_register_is_open(): void
    {
        $variant = $this->variantWithStock(5, 1000);
        $this->post(route('admin.cash.open.store'), [
            'opening' => 0,
            'date' => now()->toDateString(),
        ])->assertRedirect();

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertTrue(CashRegister::query()->where('is_active', true)->exists());
        $this->assertTrue(CashTransaction::query()->where('transaction_type', 'sale')->where('amount', 1000)->exists());
    }

    public function test_sale_returns_422_when_stock_is_insufficient_and_leaves_no_records(): void
    {
        $variant = $this->variantWithStock(2, 100);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 3],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.items.0', 'Insufficient stock.');

        $this->assertSame(2, (int) $variant->fresh()->stock?->quantity);
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, Payment::query()->count());
        $this->assertSame(0, CashTransaction::query()->count());
        $this->assertSame(0, StockMovement::query()->where('movement_type', 'sale')->count());
    }

    public function test_sale_returns_422_when_discount_exceeds_the_line_total(): void
    {
        $variant = $this->variantWithStock(5, 500);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'card',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1, 'discount' => 600],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.items.0', 'Discount cannot exceed the line total.');

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(5, (int) $variant->fresh()->stock?->quantity);
    }

    public function test_return_restores_stock_refunds_payment_and_rejects_a_second_return(): void
    {
        $variant = $this->variantWithStock(10, 1000);
        $this->post(route('admin.cash.open.store'), [
            'opening' => 0,
            'date' => now()->toDateString(),
        ]);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 2],
            ],
        ])->assertCreated();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(8, (int) $variant->fresh()->stock?->quantity);

        $this->get(route('admin.returns.create', ['sale' => $order->order_number]))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('T-Shirt')
            ->assertSee('All items on this sale will be returned.')
            ->assertDontSee('Full return');

        $this->postJson(route('api.returns.store'), [
            'sale' => $order->order_number,
            'reason' => 'wrong_size',
        ])->assertCreated();

        $this->assertSame('returned', $order->fresh()->status);
        $this->assertSame(10, (int) $variant->fresh()->stock?->quantity);
        $this->assertTrue(SaleReturn::query()->where('order_id', $order->id)->exists());
        $this->assertTrue(Payment::query()->where('order_id', $order->id)->where('status', 'refunded')->exists());
        $this->assertTrue(StockMovement::query()->where('movement_type', 'return')->where('quantity', 2)->exists());
        $this->assertTrue(StockMovement::query()->where('note', $order->order_number)->exists());
        $this->assertSame(
            -2000.0,
            (float) CashTransaction::query()->where('transaction_type', 'refund')->value('amount'),
        );

        $this->get(route('admin.sales.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Returned');

        $this->get(route('admin.returns.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Wrong size');

        $this->postJson(route('api.returns.store'), [
            'sale' => $order->order_number,
            'reason' => 'defective',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.sale.0', 'This sale has already been returned.');

        $this->assertSame(10, (int) $variant->fresh()->stock?->quantity);
        $this->assertSame(1, SaleReturn::query()->count());
        $this->assertSame(1, Payment::query()->where('status', 'refunded')->count());
        $this->assertSame(1, CashTransaction::query()->where('transaction_type', 'refund')->count());
    }

    public function test_other_payment_requires_a_note(): void
    {
        $variant = $this->variantWithStock(2, 100);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'other',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.note.0', 'Enter a note for other payments.');

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_other_payment_stores_the_note_and_increments_the_sale_number(): void
    {
        $this->travelTo('2026-09-06 10:00:00');
        $variant = $this->variantWithStock(5, 100);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'other',
            'note' => 'Havale / EFT',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.number', 'NV-20260906-0001')
            ->assertJsonPath('data.payment', 'other')
            ->assertJsonPath('data.note', 'Havale / EFT');

        $this->assertTrue(
            Payment::query()
                ->where('payment_method', 'other')
                ->where('transaction_reference', 'Havale / EFT')
                ->exists(),
        );

        $this->postJson(route('api.sales.store'), [
            'payment' => 'card',
            'items' => [
                ['sku' => $variant->sku, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.number', 'NV-20260906-0002');

        $this->get(route('admin.sales.show', 'NV-20260906-0001'))
            ->assertOk()
            ->assertSee('NV-20260906-0001')
            ->assertSee('Havale / EFT');

        $this->get(route('admin.returns.create', ['sale' => 'NV-20260906-0001']))
            ->assertOk()
            ->assertSee('NV-20260906-0001');
    }

    public function test_pos_item_search_matches_barcode_sku_and_name(): void
    {
        $variant = $this->variantWithStock(4, 199);

        $this->getJson(route('api.pos.items', ['q' => $variant->barcode]))
            ->assertOk()
            ->assertJsonPath('data.0.sku', $variant->sku)
            ->assertJsonPath('data.0.name', 'T-Shirt')
            ->assertJsonPath('data.0.brand', 'Nike');

        $this->getJson(route('api.pos.items', ['q' => $variant->sku]))
            ->assertOk()
            ->assertJsonPath('data.0.barcode', $variant->barcode);

        $this->getJson(route('api.pos.items', ['q' => 'T-Shirt']))
            ->assertOk()
            ->assertJsonFragment(['sku' => $variant->sku]);
    }

    public function test_empty_sales_and_returns_lists_do_not_render_catalog_fixtures(): void
    {
        $this->get(route('admin.sales.index'))
            ->assertOk()
            ->assertSee('No sales');

        $this->get(route('admin.returns.index'))
            ->assertOk()
            ->assertSee('Create return');
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

        return $variant->fresh(['stock', 'product']);
    }
}
