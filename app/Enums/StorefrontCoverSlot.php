<?php

namespace App\Enums;

enum StorefrontCoverSlot: string
{
    case Hero = 'hero';
    case Women = 'women';
    case Men = 'men';
    case Collections = 'collections';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return __('admin.categories.cover_'.$this->value);
    }
}
