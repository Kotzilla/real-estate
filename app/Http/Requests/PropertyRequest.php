<?php

namespace App\Http\Requests;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\District;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Property Request
 *
 * Validates the property add / edit form: details, prices, facilities (comma-separated tags) and photos. A listing is
 * for sale, for rent or both, and needs the matching price. The province comes from the district.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Http\Requests
 */
class PropertyRequest extends FormRequest
{
    /**
     * Whether the user may add or edit properties
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property
            ? (bool) $this->user()?->can('update', $property)
            : (bool) $this->user()?->can('create', Property::class);
    }

    /**
     * Validation rules for a property
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PropertyType::class)],
            'status' => ['required', Rule::enum(PropertyStatus::class)],
            'name' => ['required', 'string', 'max:255'],
            'name_th' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'description_th' => ['nullable', 'string', 'max:5000'],
            'owner_id' => ['nullable', 'integer', 'exists:people,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_th' => ['nullable', 'string', 'max:255'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:50'],
            'floor' => ['nullable', 'integer', 'min:0', 'max:200'],
            'area' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'land_area' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'is_sale' => ['boolean'],
            'is_rent' => ['boolean'],
            'sale_price' => ['nullable', 'required_if:is_sale,true', 'numeric', 'min:0', 'max:999999999999'],
            'rent_price' => ['nullable', 'required_if:is_rent,true', 'numeric', 'min:0', 'max:9999999999'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'is_furnished' => ['boolean'],
            'is_pet' => ['boolean'],
            'is_published' => ['boolean'],
            'facilities' => ['nullable', 'string', 'max:1000'],
            'remark' => ['nullable', 'string', 'max:2000'],
            'photos' => ['array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photos' => ['array'],
            'remove_photos.*' => ['integer'],
            'cover_photo_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * A listing has to be offered somehow: for sale, for rent or both
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->boolean('is_sale') && ! $this->boolean('is_rent')) {
                    $validator->errors()->add('is_sale', __('properties.form.offer_required'));
                }
            },
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
            ->reject(fn (string $field) => str_contains($field, '.'))
            ->mapWithKeys(fn (string $field) => [$field => __("properties.form.$field")])
            ->put('photos.*', __('properties.form.photos'))
            ->all();
    }

    /**
     * Turn the switches into booleans before validating (an unticked switch posts its hidden 0)
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        foreach (['is_sale', 'is_rent', 'is_furnished', 'is_pet', 'is_published'] as $switch) {
            $this->merge([$switch => $this->boolean($switch)]);
        }
    }

    /**
     * The property's columns: the province from the district, a price only for what it is offered as
     *
     * @return array<string, mixed>
     */
    public function propertyValues(): array
    {
        $values = $this->safe()->except(['facilities', 'photos', 'remove_photos', 'cover_photo_id']);
        $values['province_id'] = District::query()->whereKey($values['district_id'])->value('province_id');
        $values['sale_price'] = $values['is_sale'] ? $values['sale_price'] : null;
        $values['rent_price'] = $values['is_rent'] ? $values['rent_price'] : null;

        return $values;
    }

    /**
     * The facilities as a clean list (Tagify posts JSON, a plain input posts "a, b")
     *
     * @return array<int, string>
     */
    public function facilities(): array
    {
        $raw = (string) $this->validated('facilities');
        $decoded = json_decode($raw, true);
        $tags = is_array($decoded)
            ? array_map(fn ($item) => is_array($item) ? ($item['value'] ?? '') : (string) $item, $decoded)
            : explode(',', $raw);

        return array_values(array_unique(array_filter(array_map('trim', $tags))));
    }
}
