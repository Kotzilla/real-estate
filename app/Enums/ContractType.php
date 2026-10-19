<?php

namespace App\Enums;

/**
 * Contract Type Enum
 *
 * A lease (monthly rent for a number of months) or a sale (agreement to sell and purchase).
 *
 * @package App\Enums
 */
enum ContractType: string
{
    case Rent = 'rent';
    case Sale = 'sale';

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
        return __('contracts.types.'.$this->value);
    }

    /**
     * Badge CSS class
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Rent => 'kt-badge-light kt-badge-info',
            self::Sale => 'kt-badge-light kt-badge-primary',
        };
    }
}
