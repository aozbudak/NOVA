<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontWishlistTest extends TestCase
{
    public function test_empty_wishlist_renders_the_empty_state(): void
    {
        $this->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Your wishlist is empty');
    }

    public function test_toggles_a_product_on_the_wishlist(): void
    {
        $this->postJson(route('wishlist.store'), ['product_id' => 2])
            ->assertOk()
            ->assertJsonPath('added', true);

        $this->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Fluid Silk Midi Dress');

        $this->postJson(route('wishlist.store'), ['product_id' => 2])
            ->assertOk()
            ->assertJsonPath('added', false);
    }
}
