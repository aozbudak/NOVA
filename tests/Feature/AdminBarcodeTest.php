<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminBarcodeTest extends TestCase
{
    public function test_barcode_table_lists_sku_and_barcode_values(): void
    {
        $this->get(route('admin.barcode.index'))
            ->assertOk()
            ->assertSee('Barcode')
            ->assertSee('NOVA01-WHI-S')
            ->assertSee('8680001000100')
            ->assertSee('Basic Shirt')
            ->assertDontSee('This module is ready for operational data.');
    }

    public function test_barcode_search_filters_rows(): void
    {
        $this->get(route('admin.barcode.index', ['search' => 'NOVA02']))
            ->assertOk()
            ->assertSee('Wool Coat')
            ->assertDontSee('Basic Shirt');
    }

    public function test_warehouse_staff_can_open_barcode(): void
    {
        $this->withSession(['admin.role' => StaffRole::WarehouseStaff->value])
            ->get(route('admin.barcode.index'))
            ->assertOk()
            ->assertSee('Barcode');
    }
}
