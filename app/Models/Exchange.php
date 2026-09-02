<?php

namespace App\Models;

use App\Models\Concerns\HasUuidKeys;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'return_id',
    'old_product_variant_id',
    'new_product_variant_id',
    'quantity',
    'price_difference',
    'status',
])]
class Exchange extends Model
{
    use HasUuidKeys;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_difference' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<SaleReturn, $this>
     */
    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'return_id');
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function oldVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'old_product_variant_id');
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function newVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'new_product_variant_id');
    }
}
