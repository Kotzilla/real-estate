<?php

namespace App\Http\Requests;

use App\Enums\PersonType;
use App\Models\Person;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Person Request
 *
 * Validates the contact add / edit form. Only the first name is required; the contracts need more (an ID, an
 * address), which the contract page points out when it is missing.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Http\Requests
 */
class PersonRequest extends FormRequest
{
    /**
     * Whether the user may add or edit contacts
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $person = $this->route('person');

        return $person
            ? (bool) $this->user()?->can('update', $person)
            : (bool) $this->user()?->can('create', Person::class);
    }

    /**
     * Validation rules for a contact
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PersonType::class)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'first_name_th' => ['nullable', 'string', 'max:255'],
            'last_name_th' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'id_card_no' => ['nullable', 'string', 'max:20'],
            'passport_no' => ['nullable', 'string', 'max:30'],
            'birth_on' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_th' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:30'],
            'remark' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Validation attribute names, as the form labels them
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return collect(array_keys($this->rules()))
            ->mapWithKeys(fn (string $field) => [$field => __("people.form.$field")])
            ->all();
    }

    /**
     * Strip spaces and dashes from the phone before validating, so 081-234 5678 is stored as 0812345678
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->phone)) {
            $this->merge(['phone' => preg_replace('/[\s-]/', '', $this->phone)]);
        }
    }
}
