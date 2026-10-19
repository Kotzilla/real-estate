<?php

namespace App\Enums;

/**
 * User Role Enum
 *
 * The roles a back-office user has. spatie/permission stores them in its own tables (there is no `role` column), so this
 * enum is the list the Users page offers and the badge it draws; authorization stays with spatie/permission — see
 * App\Policies\UserPolicy.
 *
 * @package App\Enums
 */
enum UserRole: string
{
    case Admin = 'admin';     // everything: listings, contracts, settings and staff accounts
    case Manager = 'manager'; // every listing and contract, no settings
    case Agent = 'agent';     // lists properties, closes deals

    /**
     * Every role's database value (what spatie/permission holds)
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Human-readable label for the role
     *
     * @return string
     */
    public function label(): string
    {
        return __('users.roles.'.$this->value);
    }

    /**
     * Badge CSS class for the role
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Admin => 'kt-badge-light kt-badge-destructive',
            self::Manager => 'kt-badge-light kt-badge-primary',
            self::Agent => 'kt-badge-light kt-badge-info',
        };
    }
}
