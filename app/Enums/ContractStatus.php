<?php

namespace App\Enums;

/**
 * Contract Status Enum
 *
 * A draft is being prepared; active is signed and running; completed ran its course (lease ended, ownership transferred); cancelled never went ahead or was broken off.
 *
 * @package App\Enums
 */
enum ContractStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

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
        return __('contracts.statuses.'.$this->value);
    }

    /**
     * Badge CSS class
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'kt-badge-light kt-badge-secondary',
            self::Active => 'kt-badge-light kt-badge-success',
            self::Completed => 'kt-badge-light kt-badge-primary',
            self::Cancelled => 'kt-badge-light kt-badge-destructive',
        };
    }
}
