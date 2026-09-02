<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    public function test_profile_renders_identity_and_password_sections(): void
    {
        $response = $this->get(route('admin.profile.show'));

        $response->assertOk();
        $response->assertSee('My profile');
        $response->assertSee('Name');
        $response->assertSee('Email');
        $response->assertSee('Phone');
        $response->assertSee('Role');
        $response->assertSee('Profile information');
        $response->assertSee('Change password');
        $response->assertSee('Ayşe Yılmaz');
        $response->assertSee('ayse.yilmaz@nova.store');
        $response->assertSee('Super Admin');
        $response->assertSee('Audit log');
        $response->assertSee('Settings');
    }

    public function test_cashier_profile_omits_abilities_the_role_does_not_have(): void
    {
        $response = $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.profile.show'));

        $response->assertOk();
        $response->assertSee('POS');
        $response->assertSee('Sales');
        $response->assertSee('Customers');
        $response->assertSee('Returns');
        $this->assertStringNotContainsString('>Settings</span>', $response->getContent());
        $this->assertStringNotContainsString('>Audit log</span>', $response->getContent());
        $this->assertStringNotContainsString('>Inventory</span>', $response->getContent());
    }

    public function test_profile_update_persists_and_toasts(): void
    {
        $this->put(route('admin.profile.update'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@nova.store',
            'phone' => '0532 000 00 99',
        ])
            ->assertRedirect(route('admin.profile.show'))
            ->assertSessionHas('status', 'Profile updated successfully.');

        $this->get(route('admin.profile.show'))
            ->assertOk()
            ->assertSee('value="Ada Lovelace"', false)
            ->assertSee('ada@nova.store');
    }

    public function test_short_password_is_rejected(): void
    {
        $this->from(route('admin.profile.show'))
            ->put(route('admin.profile.password'), [
                'current_password' => 'secret123',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertRedirect(route('admin.profile.show'))
            ->assertSessionHasErrors('password');
    }
}
