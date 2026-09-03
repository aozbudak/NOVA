<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Cart
{
    public function __construct(private Catalog $catalog) {}

    /**
     * @return Collection<int, array{key: string, product: array<string, mixed>, size: string, color: string, quantity: int, line_total: float}>
     */
    public function items(): Collection
    {
        return collect(session('cart', []))
            ->map(function (array $line): ?array {
                $product = $this->catalog->find((int) $line['product_id']);

                if ($product === null) {
                    return null;
                }

                return [
                    'key' => $line['key'],
                    'product' => $product,
                    'size' => $line['size'],
                    'color' => $line['color'] ?? '',
                    'quantity' => (int) $line['quantity'],
                    'line_total' => $product['price'] * (int) $line['quantity'],
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
}
