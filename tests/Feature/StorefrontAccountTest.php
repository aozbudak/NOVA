<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontAccountTest extends TestCase
{
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
            ->assertSee('NOVA-1024')
            ->assertSee('Overview');
    }

    public function test_authenticated_customer_sees_order_history(): void
    {
        $this->withSession([
            'storefront.customer' => [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@nova.example',
            ],
        ]);

        $this->get(route('account.orders'))->assertSee('NOVA-1024');
    }

    public function test_authenticated_customer_sees_profile_email(): void
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
            ->assertDontSee('Quiet luxury, considered construction');
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
}
