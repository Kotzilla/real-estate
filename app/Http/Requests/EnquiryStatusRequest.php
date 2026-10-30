<?php

namespace App\Http\Requests;

use App\Enums\EnquiryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Enquiry Status Request
 *
 * Validates a status change on an enquiry's page (the route checks EnquiryPolicy::update).
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Http\Requests
 */
class EnquiryStatusRequest extends FormRequest
{
    /**
     * The new status
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(EnquiryStatus::class)]];
    }
}
