<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontSearchTest extends TestCase
{
    public function test_search_page_finds_matching_products(): void
    {
        $this->get(route('search', ['q' => 'coat']))
            ->assertOk()
            ->assertSee('Structured Wool Coat');
    }

    public function test_search_json_returns_compact_results(): void
    {
        $this->getJson(route('search', ['q' => 'blazer']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Tailored Crepe Blazer']);
    }
}
