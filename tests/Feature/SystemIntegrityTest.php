<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Database\Seeders\AdminCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_call_inventory_or_product_apis(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->getJson(route('api.inventory.index'))
            ->assertForbidden();

        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->getJson(route('api.products.index'))
            ->assertForbidden();

        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->getJson(route('api.pos.items'))
            ->assertOk();
    }

    public function test_sale_rejects_insufficient_stock_and_keeps_quantity(): void
    {
        $this->seed(AdminCatalogSeeder::class);

        $variant = ProductVariant::query()->where('sku', 'NOVA01-WHI-S')->firstOrFail();
        $before = (int) $variant->stock?->quantity;

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => 'NOVA01-WHI-S', 'quantity' => $before + 5],
            ],
        ])->assertUnprocessable();

        $this->assertSame($before, (int) $variant->fresh()?->stock?->quantity);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_sale_writes_stock_movement_payment_and_audit(): void
    {
        $this->seed(AdminCatalogSeeder::class);

        $variant = ProductVariant::query()->where('sku', 'NOVA01-WHI-S')->firstOrFail();
        $before = (int) $variant->stock?->quantity;

        $this->postJson(route('api.sales.store'), [
            'payment' => 'card',
            'items' => [
                ['sku' => 'NOVA01-WHI-S', 'quantity' => 2],
            ],
        ])->assertCreated();

        $this->assertSame($before - 2, (int) $variant->fresh()?->stock?->quantity);
        $this->assertTrue(Order::query()->where('order_number', 'like', 'NV-%')->exists());
        $this->assertTrue(Payment::query()->where('status', 'completed')->exists());
        $this->assertTrue(StockMovement::query()->where('movement_type', 'sale')->where('quantity', -2)->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'sale.completed')->exists());
    }

    public function test_return_restores_stock_and_cannot_run_twice(): void
    {
        $this->seed(AdminCatalogSeeder::class);

        $variant = ProductVariant::query()->where('sku', 'NOVA01-WHI-S')->firstOrFail();
        $before = (int) $variant->stock?->quantity;

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => 'NOVA01-WHI-S', 'quantity' => 2],
            ],
        ])->assertCreated();

        $order = Order::query()->where('order_number', 'like', 'NV-%')->firstOrFail();

        $this->postJson(route('api.returns.store'), [
            'sale' => $order->order_number,
        ])->assertCreated();

        $this->assertSame($before, (int) $variant->fresh()?->stock?->quantity);
        $this->assertSame('returned', $order->fresh()?->status);
        $this->assertTrue(Payment::query()->where('order_id', $order->id)->where('status', 'refunded')->exists());
        $this->assertTrue(StockMovement::query()->where('movement_type', 'return')->where('quantity', 2)->exists());

        $this->postJson(route('api.returns.store'), [
            'sale' => $order->order_number,
        ])->assertUnprocessable();
    }

    public function test_second_register_open_is_rejected(): void
    {
        $this->postJson(route('api.cash.open'), ['opening' => 1000])->assertOk();
        $this->postJson(route('api.cash.open'), ['opening' => 500])->assertUnprocessable();
    }

    public function test_sale_rejects_a_discount_greater_than_the_line_total(): void
    {
        $this->seed(AdminCatalogSeeder::class);

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => 'NOVA01-WHI-S', 'quantity' => 1, 'discount' => 99999],
            ],
        ])->assertUnprocessable();
    }
}
