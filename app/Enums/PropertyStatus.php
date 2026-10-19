<?php

namespace App\Enums;

/**
 * Property Status Enum
 *
 * Where a listing stands. An active contract moves it to rented or sold (App\Models\Contract::syncPropertyStatus).
 *
 * @package App\Enums
 */
enum PropertyStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Rented = 'rented';
    case Sold = 'sold';

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
        return __('properties.statuses.'.$this->value);
    }

    /**
     * Badge CSS class
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Available => 'kt-badge-light kt-badge-success',
            self::Reserved => 'kt-badge-light kt-badge-warning',
            self::Rented => 'kt-badge-light kt-badge-info',
            self::Sold => 'kt-badge-light kt-badge-secondary',
        };
    }
}
