<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    public function test_nova_1024_finds_the_sale_and_related_products(): void
    {
        $response = $this->getJson(route('admin.search', ['q' => 'NOVA-1024']));

        $response->assertOk();
        $response->assertJsonPath('groups.sales.0.label', 'NOVA-1024');
        $response->assertJsonPath('groups.sales.0.meta', 'Elif Kaya');
        $labels = collect($response->json('groups.products'))->pluck('label')->all();
        $this->assertContains('Basic Shirt', $labels);
        $this->assertContains('Tailored Trouser', $labels);
    }

    public function test_search_groups_cover_customers_suppliers_and_users(): void
    {
        $response = $this->getJson(route('admin.search', ['q' => 'Elif']));

        $response->assertOk();
        $response->assertJsonPath('groups.customers.0.label', 'Elif Kaya');

        $this->getJson(route('admin.search', ['q' => 'Atelier Mills']))
            ->assertOk()
            ->assertJsonPath('groups.suppliers.0.label', 'Atelier Mills');

        $this->getJson(route('admin.search', ['q' => 'Ayşe Yılmaz']))
            ->assertOk()
            ->assertJsonPath('groups.users.0.label', 'Ayşe Yılmaz');
    }

    public function test_cashier_search_omits_modules_outside_the_role(): void
    {
        $response = $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->getJson(route('admin.search', ['q' => 'NOVA-1024']));

        $response->assertOk();
        $response->assertJsonPath('groups.sales.0.label', 'NOVA-1024');
        $this->assertSame([], $response->json('groups.products'));
        $this->assertSame([], $response->json('groups.suppliers'));
        $this->assertSame([], $response->json('groups.users'));
    }

    public function test_search_overlay_is_present_on_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-admin-search', false)
            ->assertSee('Search modules, pages, records');
    }
}
