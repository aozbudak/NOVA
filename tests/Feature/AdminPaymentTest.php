<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminPaymentTest extends TestCase
{
    public function test_payments_table_renders_methods_and_references(): void
    {
        $response = $this->get(route('admin.payments.index'));

        $response->assertOk();
        $response->assertSee('Payments');
        $response->assertSee('NOVA-1024');
        $response->assertSee('Cash');
        $response->assertSee('Card');
        $response->assertSee('Other');
        $response->assertSee('PAY-1024');
        $response->assertSee('Elif Kaya');
    }
}
