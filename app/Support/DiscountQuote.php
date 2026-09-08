<?php

namespace App\Support;

final readonly class DiscountQuote
{
    public function __construct(
        public string $unitOriginal,
        public string $unitFinal,
        public string $unitAmount,
        public string $lineOriginal,
        public string $lineFinal,
        public string $lineAmount,
        public ?int $percent,
        public ?string $name,
        public ?string $scope,
        public ?string $discountId,
        public ?string $type = null,
        public ?string $value = null,
    ) {}

    public function hasDiscount(): bool
    {
        return bccomp($this->unitAmount, '0', 2) > 0;
    }

    /**
     * @return array{
     *     original: float,
     *     price: float,
     *     amount: float,
     *     percent: int|null,
     *     name: string|null,
     *     scope: string|null
     * }
     */
    public function display(): array
    {
        return [
            'original' => (float) $this->unitOriginal,
            'price' => (float) $this->unitFinal,
            'amount' => (float) $this->unitAmount,
            'percent' => $this->percent,
            'name' => $this->name,
            'scope' => $this->scope,
        ];
    }
}
