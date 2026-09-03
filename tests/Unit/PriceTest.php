<?php

namespace Tests\Unit;

use App\Support\Price;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    public function test_gross_price_splits_into_net_and_vat_with_two_decimals(): void
    {
        $this->assertSame([
            'gross' => '1000.00',
            'net' => '833.33',
            'vat' => '166.67',
            'rate' => '20.00',
        ], Price::breakdown(1000, 20));
    }
}
