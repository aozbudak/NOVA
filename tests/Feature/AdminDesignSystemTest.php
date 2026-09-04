<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminDesignSystemTest extends TestCase
{
    public function test_products_list_uses_shared_table_filters_pagination_and_error_state(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('data-admin-table', false);
        $response->assertSee('data-table-error', false);
        $response->assertSee('Unable to load data.');
        $response->assertSee('Please check your connection and try again.');
        $response->assertSee('RETRY');
        $response->assertSee('Showing');
        $response->assertSee('of');
        $response->assertSee('sort=name', false);
        $response->assertSee('data-label="Product"', false);
    }

    public function test_active_product_filters_render_chips(): void
    {
        $response = $this->get(route('admin.products.index', [
            'category' => 'Dresses',
            'status' => 'active',
        ]));

        $response->assertOk();
        $response->assertSee('Category:');
        $response->assertSee('Dresses');
        $response->assertSee('Status:');
        $response->assertSee('Active');
    }

    public function test_product_form_marks_required_fields_and_shows_the_field_system(): void
    {
        $this->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'username', 'role', 'status', 'password']);

        $response = $this->from(route('admin.users.create'))
            ->followingRedirects()
            ->post(route('admin.users.store'), []);

        $response->assertOk();
        $response->assertSee('Required');
        $response->assertSee('aria-invalid="true"', false);
        $response->assertSee('The first name field is required.');
    }

    public function test_sidebar_follows_the_module_map(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Stock movements');
        $response->assertSee('Cash register');
        $response->assertSee('Cash movements');
        $response->assertSee('Sales reports');
        $response->assertSee('Inventory reports');
        $response->assertSee('Return reports');
        $response->assertSee('Cash reports');
        $response->assertSee('Customer reports');
        $response->assertSee('Supplier reports');
        $response->assertSee('System management');
        $response->assertSee('Site management');
        $response->assertSee('About NOVA');
        $response->assertSee('Roles & permissions');
        $response->assertSee('data-nav-group="site"', false);
        $response->assertSee('Notifications');
        $response->assertSee('Profile');
        $response->assertSee('Settings');
        $response->assertDontSee(route('admin.users.index'), false);
        $response->assertSee('data-admin-sidebar', false);
        $response->assertSee('data-nav-group="general"', false);
        $response->assertSee('data-nav-group="store"', false);
        $response->assertSee('data-nav-group="site"', false);
        $response->assertSee('data-nav-group="system"', false);
        $response->assertDontSee('data-nav-group="management"', false);
        $response->assertSee('data-nav-group-toggle', false);
        $response->assertSee('data-active-group="true"', false);
        $response->assertSee('data-active="true"', false);
        $response->assertSee('data-dropdown="user"', false);
        $response->assertSee('Log out');
        $response->assertSee('hreflang="tr"', false);
        $response->assertSee('hreflang="de"', false);
        $response->assertSee('hreflang="fr"', false);
    }

    public function test_sidebar_opens_the_active_module_group(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('data-nav-group="store"', false);
        $response->assertSee('data-active-group="true"', false);
        $response->assertSee('data-active="true"', false);
    }

    public function test_notifications_page_lists_items(): void
    {
        $this->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Low stock: Basic T-Shirt');
    }
}
