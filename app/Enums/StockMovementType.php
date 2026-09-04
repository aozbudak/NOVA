<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Return = 'return';
    case ExchangeIn = 'exchange_in';
    case ExchangeOut = 'exchange_out';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromIntent(string $type, int $delta): self
    {
        return match ($type) {
            self::Purchase->value, 'in' => self::Purchase,
            self::Sale->value => self::Sale,
            self::Return->value => self::Return,
            self::ExchangeIn->value => self::ExchangeIn,
            self::ExchangeOut->value => self::ExchangeOut,
            'exchange' => $delta >= 0 ? self::ExchangeIn : self::ExchangeOut,
            self::AdjustmentIn->value => self::AdjustmentIn,
            self::AdjustmentOut->value => self::AdjustmentOut,
            'out', 'manual', 'adjustment' => $delta >= 0 ? self::AdjustmentIn : self::AdjustmentOut,
            default => $delta >= 0 ? self::AdjustmentIn : self::AdjustmentOut,
        };
    }

    public function isInbound(): bool
    {
        return match ($this) {
            self::Purchase, self::Return, self::ExchangeIn, self::AdjustmentIn => true,
            self::Sale, self::ExchangeOut, self::AdjustmentOut => false,
        };
    }

    public function displayType(): string
    {
        return match ($this) {
            self::Purchase => 'purchase',
            self::Sale => 'sale',
            self::Return => 'return',
            self::ExchangeIn, self::ExchangeOut => 'exchange',
            self::AdjustmentIn, self::AdjustmentOut => 'manual',
        };
    }

    public function signedQuantity(int $quantity): int
    {
        $absolute = abs($quantity);

        return $this->isInbound() ? $absolute : -$absolute;
    }
}
