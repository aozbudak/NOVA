<?php

namespace App\Models;

use App\Enums\StorefrontCoverSlot;
use App\Models\Concerns\HasUuidKeys;
use Database\Factories\StorefrontCoverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slot', 'image_url'])]
class StorefrontCover extends Model
{
    /** @use HasFactory<StorefrontCoverFactory> */
    use HasFactory, HasUuidKeys;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot' => StorefrontCoverSlot::class,
        ];
    }
}
