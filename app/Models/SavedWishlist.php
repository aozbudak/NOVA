<?php

namespace App\Models;

use App\Models\Concerns\HasUuidKeys;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['customer_id'])]
class SavedWishlist extends Model
{
    use HasUuidKeys;

    protected $table = 'wishlists';

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<SavedWishlistItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SavedWishlistItem::class, 'wishlist_id');
    }
}
