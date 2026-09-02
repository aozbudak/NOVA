<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    public function test_categories_table_lists_catalog_names_and_product_counts(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Categories')
            ->assertSee('Shirts')
            ->assertSee('Outerwear')
            ->assertDontSee('This module is ready for operational data.');
    }

    public function test_cashier_is_forbidden_from_categories(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }
}
