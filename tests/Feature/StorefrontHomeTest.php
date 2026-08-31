<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontHomeTest extends TestCase
{
    public function test_home_renders_the_campaign_and_new_arrivals(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('NOVA');
        $response->assertSee('The new standard');
        $response->assertSee('New arrivals');
        $response->assertSee('Structured Wool Coat');
        $response->assertSee('Shop women');
    }
}
