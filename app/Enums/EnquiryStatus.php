<?php

namespace App\Enums;

/**
 * Enquiry Status Enum
 *
 * Open is a new request nobody has picked up; in progress is being followed up (called, viewing arranged); closed is
 * done, whether it led to a deal or not.
 *
 * @package App\Enums
 */
enum EnquiryStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

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
        return __('enquiries.statuses.'.$this->value);
    }

    /**
     * Badge CSS class
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'kt-badge-light kt-badge-warning',
            self::InProgress => 'kt-badge-light kt-badge-info',
            self::Closed => 'kt-badge-light kt-badge-secondary',
        };
    }
}
