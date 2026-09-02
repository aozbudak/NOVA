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
}
