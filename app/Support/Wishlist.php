<?php

namespace App\Support;

use Illuminate\Support\Collection;

class Wishlist
{
    public function __construct(private Catalog $catalog) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function products(): Collection
    {
        $ids = $this->ids();

        return $this->catalog->all()
            ->filter(fn (array $product): bool => in_array($product['id'], $ids, true))
            ->values();
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return collect(session('wishlist', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function contains(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function toggle(int $productId): bool
    {
        $ids = collect($this->ids());

        if ($ids->contains($productId)) {
            session(['wishlist' => $ids->reject(fn (int $id): bool => $id === $productId)->values()->all()]);

            return false;
        }

        $ids->push($productId);
        session(['wishlist' => $ids->unique()->values()->all()]);

        return true;
    }
}
