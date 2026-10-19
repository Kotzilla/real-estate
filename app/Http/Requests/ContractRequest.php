<?php

namespace App\Http\Requests;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Contract Request
 *
 * Validates the contract add / edit form. A lease needs its term (months) and the rent's due day; its end date is
 * worked out from the start and the term when left empty. The commission itself is the model's (Contract::calculate).
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Http\Requests
 */
class ContractRequest extends FormRequest
{
    /**
     * Whether the user may add or edit contracts
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $contract = $this->route('contract');

        return $contract
            ? (bool) $this->user()?->can('update', $contract)
            : (bool) $this->user()?->can('create', Contract::class);
    }

    /**
     * Validation rules for a contract
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'customer_id' => ['required', 'integer', 'exists:people,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'witness_id' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['required', Rule::enum(ContractType::class)],
            'status' => ['required', Rule::enum(ContractStatus::class)],
            'months' => ['nullable', 'required_if:type,rent', 'integer', 'min:1', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'deposit' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'advance' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'payment_day' => ['nullable', 'required_if:type,rent', 'integer', 'min:1', 'max:31'],
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'remark' => ['nullable', 'string', 'max:2000'],
            'signed_on' => ['required', 'date'],
            'start_on' => ['required', 'date'],
            'end_on' => ['nullable', 'date', 'after_or_equal:start_on'],
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
            ->mapWithKeys(fn (string $field) => [$field => __("contracts.form.$field")])
            ->all();
    }

    /**
     * The contract's columns: money fields default to 0, a lease's end date from its start and term
     *
     * @return array<string, mixed>
     */
    public function contractValues(): array
    {
        $values = $this->validated();
        $values['deposit'] = $values['deposit'] ?? 0;
        $values['advance'] = $values['advance'] ?? 0;

        if ($values['type'] === ContractType::Rent->value && empty($values['end_on'])) {
            $values['end_on'] = Carbon::parse($values['start_on'])->addMonthsNoOverflow((int) $values['months'])->subDay()->toDateString();
        }

        return $values;
    }
}
