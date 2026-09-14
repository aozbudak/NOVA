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
            ->assertSee('Parent categories')
            ->assertSee('Edit category')
            ->assertSee('Delete category')
            ->assertSee('data-admin-layer="add-category"', false)
            ->assertSee('Header')
            ->assertSee('Add to header')
            ->assertSee('No categories in the header yet.')
            ->assertSee('Storefront covers')
            ->assertSee('The new standard')
            ->assertSee('Collections')
            ->assertSee('Save covers')
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

    public function test_cashier_is_forbidden_from_updating_a_category(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->put(route('admin.categories.update', 'jackets'), [
                'name' => 'Coats',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_empty_update_payload_is_rejected(): void
    {
        $this->from(route('admin.categories.index'))
            ->put(route('admin.categories.update', 'shirts'), [])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors(['name', 'status']);
    }

    public function test_unknown_category_update_returns_404(): void
    {
        $this->put(route('admin.categories.update', 'missing'), [
            'name' => 'Coats',
            'status' => 'active',
        ])->assertNotFound();
    }

    public function test_category_is_updated_and_listed(): void
    {
        $this->post(route('admin.categories.store'), [
            'name' => 'Jackets',
            'status' => 'inactive',
        ])->assertRedirect(route('admin.categories.index'));

        $this->put(route('admin.categories.update', 'jackets'), [
            'name' => 'Coats',
            'status' => 'active',
        ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category updated successfully.');

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Coats')
            ->assertSee('Active')
            ->assertDontSee('Jackets');
    }

    public function test_cashier_is_forbidden_from_deleting_a_category(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->delete(route('admin.categories.destroy', 'jackets'))
            ->assertForbidden();
    }

    public function test_unknown_category_delete_returns_404(): void
    {
        $this->delete(route('admin.categories.destroy', 'missing'))
            ->assertNotFound();
    }

    public function test_category_is_deleted_and_removed_from_the_list(): void
    {
        $this->post(route('admin.categories.store'), [
            'name' => 'Jackets',
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->delete(route('admin.categories.destroy', 'jackets'))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category deleted successfully.');

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertDontSee('Jackets');
    }

    public function test_cashier_is_forbidden_from_adding_a_header_category(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->post(route('admin.categories.header.store'), [
                'category_id' => 'shirts',
            ])
            ->assertForbidden();
    }

    public function test_empty_header_payload_is_rejected(): void
    {
        $this->from(route('admin.categories.index'))
            ->post(route('admin.categories.header.store'), [])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('category_id');
    }

    public function test_unknown_header_category_returns_404(): void
    {
        $this->post(route('admin.categories.header.store'), [
            'category_id' => 'missing',
        ])->assertNotFound();
    }

    public function test_category_can_be_added_to_and_removed_from_the_header(): void
    {
        $this->post(route('admin.categories.store'), [
            'name' => 'Jackets',
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->post(route('admin.categories.header.store'), [
            'category_id' => 'jackets',
        ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Header categories updated.');

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Remove from header')
            ->assertDontSee('No categories in the header yet.');

        $this->delete(route('admin.categories.header.destroy', 'jackets'))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Header categories updated.');

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('No categories in the header yet.');
    }

    public function test_cashier_is_forbidden_from_removing_a_header_category(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->delete(route('admin.categories.header.destroy', 'shirts'))
            ->assertForbidden();
    }
}
