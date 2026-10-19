<?php

namespace App\Http\Controllers;

use App\Enums\PersonType;
use App\Http\Requests\PersonRequest;
use App\Models\Person;
use App\Traits\LogActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Person Controller
 *
 * The contacts list (a Livewire table), a contact's page with their listings and contracts, and the add / edit pages.
 * Routes are gated on App\Policies\PersonPolicy.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class PersonController extends Controller
{
    use LogActivity;

    /**
     * The contacts list
     *
     * @return View
     */
    public function index(): View
    {
        return view('people.index');
    }

    /**
     * The add contact page (?type=owner opens it as an owner)
     *
     * @return View
     */
    public function create(): View
    {
        $type = request()->validate(['type' => ['nullable', Rule::enum(PersonType::class)]])['type'] ?? PersonType::Customer->value;

        return view('people.create', ['person' => new Person(['type' => $type])]);
    }

    /**
     * Create the contact
     *
     * @param  PersonRequest  $request
     * @return RedirectResponse
     */
    public function store(PersonRequest $request): RedirectResponse
    {
        $person = Person::create($request->validated());
        $this->log($person, 'person-create');

        return redirect()->route('backend.people.show', $person)->with('success', __('people.created'));
    }

    /**
     * A contact: details, the listings they own and the contracts they signed
     *
     * @param  Person  $person
     * @return View
     */
    public function show(Person $person): View
    {
        return view('people.show', [
            'person' => $person->load(['properties.coverPhoto', 'contracts.property']),
        ]);
    }

    /**
     * The edit contact page
     *
     * @param  Person  $person
     * @return View
     */
    public function edit(Person $person): View
    {
        return view('people.edit', ['person' => $person]);
    }

    /**
     * Save the contact
     *
     * @param  PersonRequest  $request
     * @param  Person  $person
     * @return RedirectResponse
     */
    public function update(PersonRequest $request, Person $person): RedirectResponse
    {
        $person->update($request->validated());
        $this->log($person, 'person-update');

        return redirect()->route('backend.people.show', $person)->with('success', __('people.updated'));
    }
}
