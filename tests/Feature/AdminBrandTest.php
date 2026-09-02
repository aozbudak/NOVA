<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminBrandTest extends TestCase
{
    public function test_brands_table_lists_catalog_brands(): void
    {
        $this->get(route('admin.brands.index'))
            ->assertOk()
            ->assertSee('Brands')
            ->assertSee('NOVA')
            ->assertSee('Atelier')
            ->assertDontSee('This module is ready for operational data.');
    }

    public function test_cashier_is_forbidden_from_brands(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.brands.index'))
            ->assertForbidden();
    }
}
