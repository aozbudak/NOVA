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
            ->assertSee('Add category')
            ->assertSee('data-admin-layer="add-category"', false)
            ->assertDontSee('This module is ready for operational data.');
    }

    public function test_cashier_is_forbidden_from_categories(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_cashier_is_forbidden_from_creating_a_category(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->post(route('admin.categories.store'), [
                'name' => 'Jackets',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_empty_payload_is_rejected(): void
    {
        $this->from(route('admin.categories.index'))
            ->post(route('admin.categories.store'), [])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors(['name', 'status']);
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        $this->from(route('admin.categories.index'))
            ->post(route('admin.categories.store'), [
                'name' => 'Shirts',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('name');
    }

    public function test_category_is_created_and_listed(): void
    {
        $this->post(route('admin.categories.store'), [
            'name' => 'Jackets',
            'status' => 'inactive',
        ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category created successfully.');

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Jackets')
            ->assertSee('Inactive');
    }
}
