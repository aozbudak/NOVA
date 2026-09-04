<?php

namespace Tests\Unit;

use App\Enums\StockMovementType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class StockMovementTypeTest extends TestCase
{
    #[TestWith(['in', 4, StockMovementType::Purchase])]
    #[TestWith(['purchase', 4, StockMovementType::Purchase])]
    #[TestWith(['sale', -2, StockMovementType::Sale])]
    #[TestWith(['return', 2, StockMovementType::Return])]
    #[TestWith(['out', -3, StockMovementType::AdjustmentOut])]
    #[TestWith(['adjustment', 5, StockMovementType::AdjustmentIn])]
    #[TestWith(['adjustment', -5, StockMovementType::AdjustmentOut])]
    #[TestWith(['adjustment_in', 4, StockMovementType::AdjustmentIn])]
    public function test_from_intent_maps_to_constraint_values(string $type, int $delta, StockMovementType $expected): void
    {
        $this->assertSame($expected, StockMovementType::fromIntent($type, $delta));
    }

    public function test_sale_stores_a_positive_quantity_and_a_negative_display_qty(): void
    {
        $type = StockMovementType::Sale;

        $this->assertSame(2, abs($type->signedQuantity(-2)));
        $this->assertSame(-2, $type->signedQuantity(2));
        $this->assertSame('sale', $type->displayType());
    }

    public function test_adjustment_in_displays_as_manual(): void
    {
        $this->assertSame('manual', StockMovementType::AdjustmentIn->displayType());
        $this->assertSame(4, StockMovementType::AdjustmentIn->signedQuantity(4));
    }
}
