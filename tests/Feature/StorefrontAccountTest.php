<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StorefrontAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_redirects_guests_to_login(): void
    {
        $this->get(route('account.show'))->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_sees_account_dashboard(): void
    {
        $this->withSession([
            'storefront.customer' => [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@nova.example',
            ],
        ]);

        $this->get(route('account.show'))
            ->assertSee('Ada')
            ->assertSee('Overview')
            ->assertSee('No orders yet')
            ->assertDontSee('NOVA-1024');
    }

    public function test_authenticated_customer_sees_own_order_history(): void
    {
        $this->registerAda();

        Order::factory()->for($this->ada())->create([
            'order_number' => 'NOVA-1024',
            'status' => 'completed',
            'total_amount' => 389,
            'currency' => 'EUR',
        ]);

        $this->get(route('account.orders'))
            ->assertSee('NOVA-1024')
            ->assertSee('Completed');
    }

    public function test_order_history_hides_another_customers_orders(): void
    {
        $this->registerAda();

        Order::factory()->for(Customer::factory())->create([
            'order_number' => 'NOVA-9999',
        ]);

        $this->get(route('account.orders'))->assertDontSee('NOVA-9999');
    }

    public function test_authenticated_customer_sees_profile_and_password_forms(): void
    {
        $this->withSession([
            'storefront.customer' => [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@nova.example',
            ],
        ]);

        $this->get(route('account.profile'))
            ->assertSee('ada@nova.example')
            ->assertSee('Details')
            ->assertSee('Password')
            ->assertSee('Current password')
            ->assertSee('New password')
            ->assertDontSee('Quiet luxury, considered construction');
    }

    public function test_guest_profile_update_redirects_to_login(): void
    {
        $this->put(route('account.profile.update'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
        ])->assertRedirect(route('login'));
    }

    public function test_guest_password_update_redirects_to_login(): void
    {
        $this->put(route('account.password'), [
            'current_password' => 'password123',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertRedirect(route('login'));
    }

    public function test_profile_update_persists_and_toasts(): void
    {
        $this->registerAda();

        $this->put(route('account.profile.update'), [
            'first_name' => 'Ada',
            'last_name' => 'Byron',
            'email' => 'ada.byron@nova.example',
            'phone' => '0532 000 00 99',
        ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHas('status', 'Profile updated.');

        $this->assertDatabaseHas('customers', [
            'first_name' => 'Ada',
            'last_name' => 'Byron',
            'email' => 'ada.byron@nova.example',
            'phone' => '0532 000 00 99',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'Ada Byron',
            'email' => 'ada.byron@nova.example',
            'phone' => '0532 000 00 99',
        ]);

        $this->get(route('account.profile'))
            ->assertSee('Ada Byron')
            ->assertSee('ada.byron@nova.example')
            ->assertSee('value="0532 000 00 99"', false);
    }

    public function test_profile_update_rejects_empty_payload(): void
    {
        $this->registerAda();

        $this->from(route('account.profile'))
            ->put(route('account.profile.update'), [])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email']);
    }

    public function test_profile_update_rejects_taken_email(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->post(route('account.logout'));

        $this->registerAda();

        $this->from(route('account.profile'))
            ->put(route('account.profile.update'), [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'grace@nova.example',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHasErrors('email');

        $this->assertSame('ada@nova.example', Customer::query()->where('first_name', 'Ada')->value('email'));
    }

    public function test_profile_escapes_script_in_name(): void
    {
        $this->registerAda();

        $this->put(route('account.profile.update'), [
            'first_name' => '<script>alert(1)</script>',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
        ])->assertRedirect(route('account.profile'));

        $this->get(route('account.profile'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_password_update_persists_and_allows_login(): void
    {
        $this->registerAda();

        $this->put(route('account.password'), [
            'current_password' => 'password123',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHas('status', 'Password updated.');

        $this->assertTrue(Hash::check('newpass123', User::query()->where('email', 'ada@nova.example')->value('password')));

        $this->post(route('account.logout'));

        $this->post(route('login.store'), [
            'email' => 'ada@nova.example',
            'password' => 'newpass123',
        ])->assertRedirect(route('account.show'));
    }

    public function test_password_update_rejects_wrong_current_password(): void
    {
        $this->registerAda();

        $this->from(route('account.profile'))
            ->put(route('account.password'), [
                'current_password' => 'wrong-password',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHasErrors(['current_password' => __('auth.password')]);

        $this->assertTrue(Hash::check('password123', User::query()->where('email', 'ada@nova.example')->value('password')));
    }

    public function test_short_password_is_rejected(): void
    {
        $this->registerAda();

        $this->from(route('account.profile'))
            ->put(route('account.password'), [
                'current_password' => 'password123',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHasErrors('password');
    }

    public function test_static_page_renders_editorial_copy(): void
    {
        $this->get(route('pages.show', 'about'))
            ->assertOk()
            ->assertSee('About NOVA')
            ->assertSee('contemporary fashion house');
    }

    public function test_unknown_static_page_returns_404(): void
    {
        $this->get(route('pages.show', 'secret'))->assertNotFound();
    }

    public function test_guest_address_store_redirects_to_login(): void
    {
        $this->post(route('account.addresses.store'), $this->addressPayload())->assertRedirect(route('login'));
    }

    public function test_address_create_persists_and_lists(): void
    {
        $this->registerAda();

        $this->post(route('account.addresses.store'), $this->addressPayload())
            ->assertRedirect(route('account.addresses'))
            ->assertSessionHas('status', 'Address saved.');

        $this->assertDatabaseHas('customer_addresses', [
            'title' => 'Home',
            'city' => 'Berlin',
            'address_line' => '12 Atelier Street',
            'is_default' => true,
        ]);

        $this->get(route('account.addresses'))
            ->assertSee('Home')
            ->assertSee('12 Atelier Street')
            ->assertSee('Default address');
    }

    public function test_address_store_rejects_empty_payload(): void
    {
        $this->registerAda();

        $this->from(route('account.addresses'))
            ->post(route('account.addresses.store'), [])
            ->assertRedirect(route('account.addresses'))
            ->assertSessionHasErrors(['title', 'first_name', 'last_name', 'city', 'district', 'address_line']);
    }

    public function test_address_update_persists(): void
    {
        $this->registerAda();

        $address = CustomerAddress::factory()->for($this->ada())->create(['title' => 'Home']);

        $this->put(route('account.addresses.update', $address), $this->addressPayload([
            'title' => 'Studio',
            'address_line' => '9 Canal Road',
        ]))
            ->assertRedirect(route('account.addresses'))
            ->assertSessionHas('status', 'Address saved.');

        $this->assertSame('Studio', $address->fresh()?->title);
        $this->assertSame('9 Canal Road', $address->fresh()?->address_line);
    }

    public function test_address_delete_removes_the_record(): void
    {
        $this->registerAda();

        $address = CustomerAddress::factory()->for($this->ada())->create(['title' => 'Home']);

        $this->delete(route('account.addresses.destroy', $address))
            ->assertRedirect(route('account.addresses'))
            ->assertSessionHas('status', 'Address deleted.');

        $this->assertDatabaseMissing('customer_addresses', ['id' => $address->id]);
    }

    public function test_address_default_updates_only_the_chosen_record(): void
    {
        $this->registerAda();

        $home = CustomerAddress::factory()->for($this->ada())->create(['title' => 'Home', 'is_default' => true]);
        $office = CustomerAddress::factory()->for($this->ada())->create(['title' => 'Office', 'is_default' => false]);

        $this->put(route('account.addresses.default', $office))
            ->assertRedirect(route('account.addresses'))
            ->assertSessionHas('status', 'Default address updated.');

        $this->assertFalse((bool) $home->fresh()?->is_default);
        $this->assertTrue((bool) $office->fresh()?->is_default);
    }

    public function test_address_update_returns_404_for_another_customers_address(): void
    {
        $this->registerAda();

        $address = CustomerAddress::factory()->create(['title' => 'Secret']);

        $this->put(route('account.addresses.update', $address), $this->addressPayload(['title' => 'Taken']))
            ->assertNotFound();

        $this->assertSame('Secret', $address->fresh()?->title);
    }

    public function test_address_escapes_script_in_title(): void
    {
        $this->registerAda();

        $this->post(route('account.addresses.store'), $this->addressPayload([
            'title' => '<script>alert(1)</script>',
        ]))->assertRedirect(route('account.addresses'));

        $this->get(route('account.addresses'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    private function registerAda(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    private function ada(): Customer
    {
        return Customer::query()->where('email', 'ada@nova.example')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function addressPayload(array $overrides = []): array
    {
        return [
            'title' => 'Home',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'phone' => '0532 000 00 01',
            'city' => 'Berlin',
            'district' => 'Mitte',
            'address_line' => '12 Atelier Street',
            'postal_code' => '10115',
            'is_default' => '1',
            ...$overrides,
        ];
    }
}
