<?php

namespace App\Enums;

/**
 * Property Type Enum
 *
 * What kind of property a listing is.
 *
 * @package App\Enums
 */
enum PropertyType: string
{
    case Condo = 'condo';
    case House = 'house';
    case Villa = 'villa';
    case Townhouse = 'townhouse';
    case Apartment = 'apartment';
    case Land = 'land';
    case Commercial = 'commercial';

    /**
     * Every case's database value
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Human-readable label
     *
     * @return string
     */
    public function label(): string
    {
        return __('properties.types.'.$this->value);
    }

    /**
     * Badge CSS class
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Land => 'kt-badge-light kt-badge-warning',
            self::Commercial => 'kt-badge-light kt-badge-info',
            default => 'kt-badge-light kt-badge-secondary',
        };
    }
}
