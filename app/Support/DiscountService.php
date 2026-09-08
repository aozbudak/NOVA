<?php

namespace App\Support;

use App\Enums\DiscountType;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DiscountService
{
    /**
     * @var Collection<int, Discount>|null
     */
    private ?Collection $active = null;

    /**
     * @var array<string, string|null>|null
     */
    private ?array $categoryParents = null;

    public function quote(Product $product, ?ProductVariant $variant = null, int $quantity = 1): DiscountQuote
    {
        $quantity = max(1, $quantity);
        $original = $this->unitOriginal($product, $variant);
        $match = $this->bestMatch($product, $variant, $original);

        if ($match !== null) {
            return $this->fromDiscount($match['discount'], $match['scope'], $original, $quantity);
        }

        return $this->fromSalePrice($product, $original, $quantity);
    }

    public function amount(Product $product, ?ProductVariant $variant = null, int $quantity = 1): string
    {
        return $this->quote($product, $variant, $quantity)->lineAmount;
    }

    /**
     * @return Collection<int, Discount>
     */
    public function active(): Collection
    {
        if ($this->active instanceof Collection) {
            return $this->active;
        }

        if (! Schema::hasTable('discounts')) {
            return $this->active = collect();
        }

        return $this->active = Discount::query()
            ->active()
            ->with(['products:id', 'variants:id', 'categories:id', 'brands:id'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function forget(): void
    {
        $this->active = null;
        $this->categoryParents = null;
    }

    private function unitOriginal(Product $product, ?ProductVariant $variant): string
    {
        if ($variant !== null && $variant->price !== null) {
            return Price::money($variant->price);
        }

        return Price::money($product->base_price);
    }

    /**
     * @return array{discount: Discount, scope: string}|null
     */
    private function bestMatch(Product $product, ?ProductVariant $variant, string $original): ?array
    {
        $ranked = [];

        foreach ($this->active() as $discount) {
            $scope = $this->matchScope($discount, $product, $variant);

            if ($scope === null) {
                continue;
            }

            $ranked[] = [
                'discount' => $discount,
                'scope' => $scope,
                'priority' => $this->priority($scope),
                'amount' => $this->discountAmount($discount, $original),
            ];
        }

        if ($ranked === []) {
            return null;
        }

        usort($ranked, function (array $left, array $right): int {
            if ($left['priority'] !== $right['priority']) {
                return $left['priority'] <=> $right['priority'];
            }

            $amount = bccomp($right['amount'], $left['amount'], 2);

            if ($amount !== 0) {
                return $amount;
            }

            return strcmp((string) $right['discount']->id, (string) $left['discount']->id);
        });

        return [
            'discount' => $ranked[0]['discount'],
            'scope' => $ranked[0]['scope'],
        ];
    }

    private function matchScope(Discount $discount, Product $product, ?ProductVariant $variant): ?string
    {
        if ($discount->products->contains('id', $product->id)) {
            return 'product';
        }

        if ($variant !== null && $discount->variants->contains('id', $variant->id)) {
            return 'variant';
        }

        $categoryIds = $this->categoryIds($product);

        if ($categoryIds !== [] && $discount->categories->contains(fn (Category $category): bool => in_array($category->id, $categoryIds, true))) {
            return 'category';
        }

        if ($product->brand_id !== null && $discount->brands->contains('id', $product->brand_id)) {
            return 'brand';
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function categoryIds(Product $product): array
    {
        if ($product->category_id === null) {
            return [];
        }

        $ids = [(string) $product->category_id];
        $current = (string) $product->category_id;
        $parents = $this->categoryParentMap();
        $guard = 0;

        while (isset($parents[$current]) && $parents[$current] !== null && $guard < 12) {
            $current = $parents[$current];
            $ids[] = $current;
            $guard++;
        }

        return $ids;
    }

    /**
     * @return array<string, string|null>
     */
    private function categoryParentMap(): array
    {
        if (is_array($this->categoryParents)) {
            return $this->categoryParents;
        }

        if (! Schema::hasTable('categories')) {
            return $this->categoryParents = [];
        }

        return $this->categoryParents = Category::query()
            ->get(['id', 'parent_id'])
            ->mapWithKeys(fn (Category $category): array => [(string) $category->id => $category->parent_id])
            ->all();
    }

    private function priority(string $scope): int
    {
        return match ($scope) {
            'product' => 1,
            'variant' => 2,
            'category' => 3,
            'brand' => 4,
            default => 9,
        };
    }

    private function fromDiscount(Discount $discount, string $scope, string $original, int $quantity): DiscountQuote
    {
        $unitAmount = $this->discountAmount($discount, $original);
        $unitFinal = bcsub($original, $unitAmount, 2);
        $percent = $this->percent($discount, $original, $unitAmount);

        return new DiscountQuote(
            unitOriginal: $original,
            unitFinal: $unitFinal,
            unitAmount: $unitAmount,
            lineOriginal: bcmul($original, (string) $quantity, 2),
            lineFinal: bcmul($unitFinal, (string) $quantity, 2),
            lineAmount: bcmul($unitAmount, (string) $quantity, 2),
            percent: $percent,
            name: $discount->name,
            scope: $scope,
            discountId: $discount->id,
            type: $discount->type->value,
            value: Price::money($discount->value),
        );
    }

    private function fromSalePrice(Product $product, string $original, int $quantity): DiscountQuote
    {
        if ($product->sale_price === null) {
            return $this->none($original, $quantity);
        }

        $base = Price::money($product->base_price);
        $sale = Price::money($product->sale_price);

        if (bccomp($base, '0', 2) <= 0 || bccomp($sale, $base, 2) >= 0) {
            return $this->none($original, $quantity);
        }

        $ratio = bcdiv($sale, $base, 6);
        $unitFinal = bcmul($original, $ratio, 2);

        if (bccomp($unitFinal, $original, 2) >= 0) {
            return $this->none($original, $quantity);
        }

        $unitAmount = bcsub($original, $unitFinal, 2);
        $percent = $this->percentFromAmount($original, $unitAmount);

        return new DiscountQuote(
            unitOriginal: $original,
            unitFinal: $unitFinal,
            unitAmount: $unitAmount,
            lineOriginal: bcmul($original, (string) $quantity, 2),
            lineFinal: bcmul($unitFinal, (string) $quantity, 2),
            lineAmount: bcmul($unitAmount, (string) $quantity, 2),
            percent: $percent,
            name: null,
            scope: 'sale_price',
            discountId: null,
            type: DiscountType::Percent->value,
            value: (string) $percent,
        );
    }

    private function none(string $original, int $quantity): DiscountQuote
    {
        $line = bcmul($original, (string) $quantity, 2);

        return new DiscountQuote(
            unitOriginal: $original,
            unitFinal: $original,
            unitAmount: '0.00',
            lineOriginal: $line,
            lineFinal: $line,
            lineAmount: '0.00',
            percent: null,
            name: null,
            scope: null,
            discountId: null,
        );
    }

    private function discountAmount(Discount $discount, string $original): string
    {
        $value = Price::money($discount->value);

        if ($discount->type === DiscountType::Percent) {
            $percent = Price::rate($value, '0');
            $amount = bcdiv(bcmul($original, $percent, 4), '100', 2);
        } else {
            $amount = $value;
        }

        if (bccomp($amount, '0', 2) < 0) {
            return '0.00';
        }

        if (bccomp($amount, $original, 2) > 0) {
            return $original;
        }

        return $amount;
    }

    private function percent(Discount $discount, string $original, string $amount): ?int
    {
        if ($discount->type === DiscountType::Percent) {
            return (int) round((float) $discount->value);
        }

        return $this->percentFromAmount($original, $amount);
    }

    private function percentFromAmount(string $original, string $amount): ?int
    {
        if (bccomp($original, '0', 2) <= 0) {
            return null;
        }

        return (int) round((float) bcmul(bcdiv($amount, $original, 4), '100', 0));
    }
}
