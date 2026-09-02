<?php

namespace App\Models;

use App\Models\Concerns\HasUuidKeys;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wishlist_id', 'product_id'])]
class SavedWishlistItem extends Model
{
    use HasUuidKeys;

    public const UPDATED_AT = null;

    protected $table = 'wishlist_items';

    /**
     * @return BelongsTo<SavedWishlist, $this>
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(SavedWishlist::class, 'wishlist_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
