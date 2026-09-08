<?php

namespace App\Enums;

enum DiscountType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Percent => __('admin.discounts.type_percent'),
            self::Fixed => __('admin.discounts.type_fixed'),
        };
    }
}
