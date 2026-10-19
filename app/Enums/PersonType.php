<?php

namespace App\Enums;

/**
 * Person Type Enum
 *
 * Who a contact is to us: the owner of a listing (lessor / seller), a customer (tenant / buyer) or an outside agent.
 *
 * @package App\Enums
 */
enum PersonType: string
{
    case Owner = 'owner';
    case Customer = 'customer';
    case Agent = 'agent';

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
        return __('people.types.'.$this->value);
    }

    /**
     * Badge CSS class
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Owner => 'kt-badge-light kt-badge-primary',
            self::Customer => 'kt-badge-light kt-badge-success',
            self::Agent => 'kt-badge-light kt-badge-info',
        };
    }
}
