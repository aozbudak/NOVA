<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Cart
{
    public function __construct(private Catalog $catalog) {}

    /**
     * @return Collection<int, array{
     *     key: string,
     *     product: array<string, mixed>,
     *     size: string,
     *     color: string,
     *     quantity: int,
     *     unit_original: float,
     *     unit_price: float,
     *     line_discount: float,
     *     line_total: float,
     *     discount_percent: int|null,
     *     discount_name: string|null
     * }>
     */
    public function items(): Collection
    {
        return collect(session('cart', []))
            ->map(function (array $line): ?array {
                $product = $this->catalog->find((int) $line['product_id']);

                if ($product === null) {
                    return null;
                }

                $quantity = (int) $line['quantity'];
                $size = (string) $line['size'];
                $color = (string) ($line['color'] ?? '');
                $pricing = $this->linePricing($product, $size, $color);

                return [
                    'key' => $line['key'],
                    'product' => $product,
                    'size' => $size,
                    'color' => $color,
                    'quantity' => $quantity,
                    'unit_original' => $pricing['original'],
                    'unit_price' => $pricing['price'],
                    'line_discount' => round(($pricing['original'] - $pricing['price']) * $quantity, 2),
                    'line_total' => round($pricing['price'] * $quantity, 2),
                    'discount_percent' => $pricing['percent'],
                    'discount_name' => $pricing['name'],
                ];
            })
            ->filter()
            ->values();
    }

    public function count(): int
    {
        return (int) collect(session('cart', []))->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    /**
     * @return array{subtotal: float, discount: float, tax: float, total: float}
     */
    public function totals(): array
    {
        $items = $this->items();
        $discount = (float) $items->sum('line_discount');
        $total = (float) $items->sum('line_total');
        $subtotal = round($total + $discount, 2);
        $tax = (float) $items->sum(function (array $line): float {
            $rate = $line['product']['vat_rate'] ?? 20;

            return (float) Price::breakdown($line['line_total'], $rate)['vat'];
        });

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    public function add(int $productId, string $size, int $quantity, ?string $color = null): void
    {
        $product = $this->catalog->find($productId);

        if ($product === null) {
            throw ValidationException::withMessages([
                'product_id' => __('validation.exists', ['attribute' => 'product_id']),
            ]);
        }

        $size = Str::upper($size);
        $sizes = collect($product['sizes'] ?? []);

        if ($sizes->isNotEmpty()) {
            $option = $sizes->first(
                fn (mixed $row): bool => is_array($row) && Str::upper((string) ($row['code'] ?? '')) === $size,
            );

            if ($option === null || ($option['in_stock'] ?? true) === false) {
                throw ValidationException::withMessages([
                    'size' => __('storefront.cart.unavailable'),
                ]);
            }
        }

        $items = collect(session('cart', []));
        $color = trim((string) $color);
        $key = $color === ''
            ? $productId.'-'.Str::upper($size)
            : $productId.'-'.Str::upper($size).'-'.Str::upper($color);
        $existing = $items->search(fn (array $line): bool => $line['key'] === $key);

        if ($existing !== false) {
            $line = $items[$existing];
            $line['quantity'] = min(10, (int) $line['quantity'] + $quantity);
            $items[$existing] = $line;
        } else {
            $items->push([
                'key' => $key,
                'product_id' => $productId,
                'size' => Str::upper($size),
                'color' => $color,
                'quantity' => min(10, $quantity),
            ]);
        }

        session(['cart' => $items->values()->all()]);
    }

    public function update(string $key, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($key);

            return;
        }

        $items = collect(session('cart', []))->map(function (array $line) use ($key, $quantity): array {
            if ($line['key'] === $key) {
                $line['quantity'] = min(10, $quantity);
            }

            return $line;
        });

        session(['cart' => $items->values()->all()]);
    }

    public function remove(string $key): void
    {
        $items = collect(session('cart', []))
            ->reject(fn (array $line): bool => $line['key'] === $key)
            ->values()
            ->all();

        session(['cart' => $items]);
    }

    public function clear(): void
    {
        session()->forget('cart');
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array{original: float, price: float, percent: int|null, name: string|null}
     */
    private function linePricing(array $product, string $size, string $color): array
    {
        $match = collect($product['variantPrices'] ?? [])->first(function (array $row) use ($size, $color): bool {
            if (Str::upper((string) ($row['size'] ?? '')) !== Str::upper($size)) {
                return false;
            }

            if ($color === '') {
                return true;
            }

            return Str::upper((string) ($row['color'] ?? '')) === Str::upper($color);
        });

        if (is_array($match)) {
            $price = (float) $match['price'];
            $original = (float) ($match['oldPrice'] ?? $price);

            return [
                'original' => $original,
                'price' => $price,
                'percent' => $match['percent'] ?? $product['discountPercent'] ?? null,
                'name' => $match['discountName'] ?? $product['discountName'] ?? null,
            ];
        }

        $price = (float) $product['price'];
        $original = (float) ($product['oldPrice'] ?? $price);

        return [
            'original' => $original,
            'price' => $price,
            'percent' => $product['discountPercent'] ?? null,
            'name' => $product['discountName'] ?? null,
        ];
    }
}
