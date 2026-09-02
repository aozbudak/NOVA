<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminInventoryTest extends TestCase
{
    public function test_inventory_table_renders_current_min_and_status(): void
    {
        $response = $this->get(route('admin.inventory.index'));

        $response->assertOk();
        $response->assertSee('Inventory');
        $response->assertSee('Current stock');
        $response->assertSee('Min stock');
        $response->assertSee('Wool Coat');
        $response->assertSee('Stock movements');
    }

    public function test_stock_movements_render_each_operation_type(): void
    {
        $response = $this->get(route('admin.inventory.movements'));

        $response->assertOk();
        $response->assertSee('Stock movements');
        $response->assertSee('Purchase');
        $response->assertSee('Sale');
        $response->assertSee('Return');
        $response->assertSee('Manual adjustment');
        $response->assertSee('Exchange');
        $response->assertSee('NV-10482');
    }

    public function test_stock_adjustment_drawer_and_update_toast(): void
    {
        $this->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee('Stock adjustment')
            ->assertSee('data-admin-layer="stock-adjust"', false);

        $this->post(route('admin.inventory.adjust'), [
            'sku' => 'NOVA01-WHI-M',
            'quantity' => 2,
            'reason' => 'Count correction',
        ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHas('status', 'Stock updated successfully.');
    }

    public function test_unknown_sku_adjustment_returns_404(): void
    {
        $this->post(route('admin.inventory.adjust'), [
            'sku' => 'MISSING',
            'quantity' => 1,
        ])->assertNotFound();
    }
}
