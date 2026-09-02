<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    public function test_settings_use_category_navigation_instead_of_one_form(): void
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Settings');
        $response->assertSee('General');
        $response->assertSee('Store');
        $response->assertSee('Sales');
        $response->assertSee('Inventory');
        $response->assertSee('Notifications');
        $response->assertSee('Security');
        $response->assertSee('System');
        $response->assertSee('Store name');
        $response->assertSee('Store email');
        $response->assertSee('Phone');
        $response->assertSee('Address');
        $response->assertSee('Currency');
    }

    public function test_each_settings_category_renders_its_fields(): void
    {
        $this->get(route('admin.settings.index', 'store'))
            ->assertOk()
            ->assertSee('Store information')
            ->assertSee('Opening hours')
            ->assertSee('Default settings');

        $this->get(route('admin.settings.index', 'sales'))
            ->assertOk()
            ->assertSee('Default discount')
            ->assertSee('Payment settings')
            ->assertSee('Receipt settings');

        $this->get(route('admin.settings.index', 'inventory'))
            ->assertOk()
            ->assertSee('Low stock threshold')
            ->assertSee('Allow negative stock');

        $this->get(route('admin.settings.index', 'notifications'))
            ->assertOk()
            ->assertSee('Low stock notifications')
            ->assertSee('Sales notifications')
            ->assertSee('Return notifications');

        $this->get(route('admin.settings.index', 'security'))
            ->assertOk()
            ->assertSee('Session timeout')
            ->assertSee('Minimum password length')
            ->assertSee('Login protection');

        $this->get(route('admin.settings.index', 'system'))
            ->assertOk()
            ->assertSee('API configuration')
            ->assertSee('System status')
            ->assertSee('Operational');
    }

    public function test_general_settings_persist_and_show_a_toast(): void
    {
        $this->put(route('admin.settings.update', 'general'), [
            'store_name' => 'NOVA Atelier',
            'store_email' => 'atelier@nova.store',
            'phone' => '0212 111 11 11',
            'address' => 'Galata, Istanbul',
            'currency' => 'EUR',
        ])
            ->assertRedirect(route('admin.settings.index', 'general'))
            ->assertSessionHas('status', 'Settings saved successfully.');

        $this->get(route('admin.settings.index', 'general'))
            ->assertOk()
            ->assertSee('NOVA Atelier')
            ->assertSee('atelier@nova.store');
    }

    public function test_unknown_settings_category_returns_404(): void
    {
        $this->get(route('admin.settings.index', 'billing'))->assertNotFound();
    }

    public function test_cashier_is_forbidden_from_settings(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }
}
