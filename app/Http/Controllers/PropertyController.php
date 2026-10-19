<?php

namespace App\Http\Controllers;

use App\Enums\PersonType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Http\Requests\PropertyRequest;
use App\Models\District;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use App\Traits\LogActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Tags\Tag;

/**
 * Property Controller
 *
 * The listings list (a Livewire table), a listing's page and the add / edit pages with photos and facilities. Routes
 * are gated on App\Policies\PropertyPolicy.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Controllers
 */
class PropertyController extends Controller
{
    use LogActivity;

    /**
     * The properties list
     *
     * @return View
     */
    public function index(): View
    {
        return view('properties.index');
    }

    /**
     * The add property page
     *
     * @return View
     */
    public function create(): View
    {
        return view('properties.create', [
            'property' => new Property([
                'type' => PropertyType::Villa, 'status' => PropertyStatus::Available, 'is_rent' => true,
                'is_published' => true, 'user_id' => auth()->id(),
                'district_id' => District::query()->where('code', Property::HOME_DISTRICT)->value('id'), 'owner_id' => request()->integer('owner') ?: null,
            ]),
            ...$this->options(),
        ]);
    }

    /**
     * Create the property with its facilities and photos
     *
     * @param  PropertyRequest  $request
     * @return RedirectResponse
     */
    public function store(PropertyRequest $request): RedirectResponse
    {
        $property = DB::transaction(function () use ($request) {
            $property = Property::create($request->propertyValues());
            $property->syncTagsWithType($request->facilities(), Property::FACILITY);
            foreach ($request->file('photos', []) as $file) {
                $property->addPhoto($file, $request->user()->id);
            }

            return $property;
        });
        $this->log($property, 'property-create');

        return redirect()->route('backend.properties.show', $property)->with('success', __('properties.created'));
    }

    /**
     * A listing: photos, details, owner and its contracts
     *
     * @param  Property  $property
     * @return View
     */
    public function show(Property $property): View
    {
        return view('properties.show', [
            'property' => $property->load(['owner', 'agent', 'province', 'district', 'photos', 'tags', 'contracts.customer']),
        ]);
    }

    /**
     * The edit property page
     *
     * @param  Property  $property
     * @return View
     */
    public function edit(Property $property): View
    {
        return view('properties.edit', ['property' => $property->load(['photos', 'tags']), ...$this->options()]);
    }

    /**
     * Save the property, its facilities and photos
     *
     * @param  PropertyRequest  $request
     * @param  Property  $property
     * @return RedirectResponse
     */
    public function update(PropertyRequest $request, Property $property): RedirectResponse
    {
        DB::transaction(function () use ($request, $property) {
            $property->update($request->propertyValues());
            $property->syncTagsWithType($request->facilities(), Property::FACILITY);
            $property->removePhotos($request->validated('remove_photos', []));
            foreach ($request->file('photos', []) as $file) {
                $property->addPhoto($file, $request->user()->id);
            }
            if ($request->filled('cover_photo_id')) {
                $property->makeCover((int) $request->validated('cover_photo_id'));
            }
        });
        $this->log($property, 'property-update');

        return redirect()->route('backend.properties.show', $property)->with('success', __('properties.updated'));
    }

    /**
     * The form's select options: owners, agents, districts with their province, facilities used so far
     *
     * @return array<string, mixed>
     */
    protected function options(): array
    {
        return [
            'owners' => Person::query()->where('type', PersonType::Owner->value)->orderBy('first_name')->get(),
            'agents' => User::query()->orderBy('name')->get(['id', 'name']),
            'districts' => District::query()->with('province')->get()->sortBy(fn (District $d) => $d->province->label().' '.$d->label()),
            'allFacilities' => Tag::getWithType(Property::FACILITY)->pluck('name')->values()->all(),
        ];
    }
}
