<?php

namespace App\Http\Controllers;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Enums\PersonType;
use App\Http\Requests\ContractRequest;
use App\Models\Contract;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use App\Traits\LogActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Contract Controller
 *
 * The contracts list (a Livewire table), a contract's page, the add / edit pages and the printable copy in English or
 * Thai. "New contract" on a property opens the form filled in from the listing. Routes are gated on
 * App\Policies\ContractPolicy.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class ContractController extends Controller
{
    use LogActivity;

    /**
     * The contracts list
     *
     * @return View
     */
    public function index(): View
    {
        return view('contracts.index');
    }

    /**
     * The add contract page; ?property= fills in the type, price and agent from that listing
     *
     * @return View
     */
    public function create(): View
    {
        $property = Property::query()->find(request()->integer('property'));
        $type = $property && ! $property->is_rent ? ContractType::Sale : ContractType::Rent;

        return view('contracts.create', [
            'contract' => new Contract([
                'property_id' => $property?->id,
                'customer_id' => request()->integer('customer') ?: null,
                'user_id' => $property?->user_id ?? auth()->id(),
                'type' => $type,
                'status' => ContractStatus::Draft,
                'months' => $type === ContractType::Rent ? 12 : null,
                'payment_day' => $type === ContractType::Rent ? 1 : null,
                'price' => $type === ContractType::Rent ? $property?->rent_price : $property?->sale_price,
                'commission_percent' => Contract::DEFAULT_COMMISSION[$type->value],
                'signed_on' => today(),
                'start_on' => today(),
            ]),
            ...$this->options(),
        ]);
    }

    /**
     * Create the contract
     *
     * @param  ContractRequest  $request
     * @return RedirectResponse
     */
    public function store(ContractRequest $request): RedirectResponse
    {
        $contract = Contract::create($request->contractValues());
        $this->log($contract, 'contract-create');

        return redirect()->route('backend.contracts.show', $contract)->with('success', __('contracts.created'));
    }

    /**
     * A contract: the parties, the money, the dates, and what the printed copy is still missing
     *
     * @param  Contract  $contract
     * @return View
     */
    public function show(Contract $contract): View
    {
        $contract->load(['property.owner', 'property.province', 'property.district', 'customer', 'agent', 'witness']);

        return view('contracts.show', ['contract' => $contract, 'missing' => $this->missing($contract)]);
    }

    /**
     * The edit contract page
     *
     * @param  Contract  $contract
     * @return View
     */
    public function edit(Contract $contract): View
    {
        return view('contracts.edit', ['contract' => $contract, ...$this->options()]);
    }

    /**
     * Save the contract
     *
     * @param  ContractRequest  $request
     * @param  Contract  $contract
     * @return RedirectResponse
     */
    public function update(ContractRequest $request, Contract $contract): RedirectResponse
    {
        $contract->update($request->contractValues());
        $this->log($contract, 'contract-update');

        return redirect()->route('backend.contracts.show', $contract)->with('success', __('contracts.updated'));
    }

    /**
     * The printable contract in English or Thai: a lease or an agreement to sell and purchase
     *
     * @param  Contract  $contract
     * @param  string  $lang
     * @return View
     */
    public function print(Contract $contract, string $lang): View
    {
        $contract->load(['property.owner', 'property.province', 'property.district', 'customer', 'agent', 'witness']);
        $this->log($contract, 'contract-print', ['lang' => $lang]);
        app()->setLocale($lang); // the copy's own language for labels (property type…), whatever the user's

        return view('contracts.print', ['contract' => $contract, 'lang' => $lang]);
    }

    /**
     * What the printed copy needs but the contact or listing does not have yet (the contract page lists them)
     *
     * @param  Contract  $contract
     * @return array<int, string>
     */
    protected function missing(Contract $contract): array
    {
        $owner = $contract->property?->owner;
        $customer = $contract->customer;

        return array_keys(array_filter([
            'owner' => ! $owner,
            'owner_id' => $owner && ! $owner->documentNo(),
            'owner_address' => $owner && ! $owner->address,
            'owner_bank' => $owner && $contract->isRent() && ! $owner->bank_account_no,
            'customer_id' => ! $customer->documentNo(),
            'customer_address' => ! $contract->isRent() && ! $customer->address,
            'end_on' => ! $contract->end_on,
        ]));
    }

    /**
     * The form's select options: properties, customers, staff
     *
     * @return array<string, mixed>
     */
    protected function options(): array
    {
        return [
            'properties' => Property::query()->orderBy('code')->get(['id', 'code', 'name', 'rent_price', 'sale_price', 'is_rent', 'is_sale', 'user_id']),
            'customers' => Person::query()->where('type', PersonType::Customer->value)->orderBy('first_name')->get(),
            'staff' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
