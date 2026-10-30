<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Enquiry Request
 *
 * Validates the "arrange a viewing" form on a listing's public page: anyone may send it (the route is throttled).
 *
 * @package App\Http\Requests
 */
class EnquiryRequest extends FormRequest
{
    /**
     * Rules for an enquiry; `website` is a honeypot field people never see, so only bots fill it
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
            'message' => ['nullable', 'string', 'max:1000'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * Validation attribute names, as the form labels them
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => __('listing.enquiry.name'), 'phone' => __('listing.enquiry.phone'), 'message' => __('listing.enquiry.message')];
    }

    /**
     * Strip spaces, dashes and brackets from the phone before validating, so 081-234 5678 is stored as 0812345678
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->phone)) {
            $this->merge(['phone' => preg_replace('/[\s\-()]/', '', $this->phone)]);
        }
    }
}
