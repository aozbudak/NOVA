<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontCartTest extends TestCase
{
    public function test_adds_a_catalog_product_to_the_bag(): void
    {
        $response = $this->postJson(route('cart.store'), [
            'product_id' => 1,
            'size' => 'M',
            'quantity' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonPath('count', 1);
        $this->assertSame(1, session('cart')[0]['product_id']);
    }

    public function test_rejects_a_product_that_is_not_in_the_catalog(): void
    {
        $this->postJson(route('cart.store'), [
            'product_id' => 999,
            'size' => 'M',
            'quantity' => 1,
        ])->assertUnprocessable();
    }

    public function test_updates_quantity_and_removes_the_line_at_zero(): void
    {
        $this->post(route('cart.store'), [
            'product_id' => 1,
            'size' => 'S',
            'quantity' => 2,
        ]);

        $this->patchJson(route('cart.update', '1-S'), ['quantity' => 0])
            ->assertOk()
            ->assertJsonPath('count', 0);

        $this->assertSame([], session('cart'));
    }
}
