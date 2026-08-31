<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontCheckoutTest extends TestCase
{
    public function test_empty_bag_redirects_home(): void
    {
        $this->get(route('checkout.show'))->assertRedirect(route('home'));
    }

    public function test_places_an_order_and_clears_the_bag(): void
    {
        $this->post(route('cart.store'), [
            'product_id' => 1,
            'size' => 'M',
            'quantity' => 1,
        ]);

        $this->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Order summary')
            ->assertSee('Structured Wool Coat');

        $this->post(route('checkout.store'), [
            'email' => 'client@nova.example',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'address' => '12 Atelier Street',
            'city' => 'Berlin',
            'postal_code' => '10115',
            'country' => 'Germany',
            'delivery' => 'standard',
            'payment' => 'card',
        ])->assertRedirect(route('checkout.confirmation'));

        $this->get(route('checkout.confirmation'))
            ->assertOk()
            ->assertSee('Your order is confirmed')
            ->assertSee('NOVA-');

        $this->assertSame([], session('cart', []));
    }

    public function test_checkout_rejects_invalid_delivery_options(): void
    {
        $this->post(route('cart.store'), [
            'product_id' => 1,
            'size' => 'M',
            'quantity' => 1,
        ]);

        $this->from(route('checkout.show'))
            ->post(route('checkout.store'), [
                'email' => 'client@nova.example',
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'address' => '12 Atelier Street',
                'city' => 'Berlin',
                'postal_code' => '10115',
                'country' => 'Germany',
                'delivery' => 'overnight',
                'payment' => 'card',
            ])
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHasErrors('delivery');
    }
}
