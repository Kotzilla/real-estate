{{-- The property add / edit form, shared by both pages. $property is new on the create page. --}}
@php
  $facilities = old('facilities', $property->exists ? $property->tagsWithType(\App\Models\Property::FACILITY)->pluck('name')->join(', ') : '');
  $switch = fn (string $field) => (bool) old($field, $property->{$field});
@endphp
<form id="property-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
  @csrf
  @if ($property->exists)
    @method('PUT')
  @endif

  @if ($errors->any())
    <div class="mb-5 flex items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">
      <i class="ki-filled ki-information-2"></i>
      @lang('common.has_errors')
    </div>
  @endif

  <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="flex flex-col gap-5 lg:col-span-2">
      {{-- What it is, in both languages --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-home-3 text-lg text-primary"></i> @lang('properties.form.listing')</h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 gap-4 lg:grid-cols-2">
          <x-field name="name" :label="__('properties.form.name')" :value="$property->name" required maxlength="255" />
          <x-field name="name_th" :label="__('properties.form.name_th')" :value="$property->name_th" maxlength="255" />

          <x-field name="type" :label="__('properties.form.type')" required>
            <div x-data="ktSelect(null)">
              <select x-ref="select" id="f-type" name="type" class="kt-select w-full" @error('type') aria-invalid="true" @enderror>
                @foreach (\App\Enums\PropertyType::cases() as $case)
                  <option value="{{ $case->value }}" @selected(old('type', $property->type?->value) === $case->value)>{{ $case->label() }}</option>
                @endforeach
              </select>
            </div>
          </x-field>

          <x-field name="status" :label="__('properties.form.status')" required :hint="__('properties.form.status_hint')">
            <div x-data="ktSelect(null)">
              <select x-ref="select" id="f-status" name="status" class="kt-select w-full" @error('status') aria-invalid="true" @enderror>
                @foreach (\App\Enums\PropertyStatus::cases() as $case)
                  <option value="{{ $case->value }}" @selected(old('status', $property->status?->value) === $case->value)>{{ $case->label() }}</option>
                @endforeach
              </select>
            </div>
          </x-field>

          <div class="lg:col-span-2">
            <x-field type="textarea" name="description" :label="__('properties.form.description')" :value="$property->description" maxlength="5000" rows="5" />
          </div>
          <div class="lg:col-span-2">
            <x-field type="textarea" name="description_th" :label="__('properties.form.description_th')" :value="$property->description_th" maxlength="5000" rows="5" />
          </div>
        </div>
      </div>

      {{-- Where it is: the district sets the province --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-geolocation text-lg text-primary"></i> @lang('properties.form.where')</h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 gap-4 lg:grid-cols-2">
          <x-field name="district_id" :label="__('properties.form.district_id')" required>
            <div x-data="ktSelect(null, { enableSearch: true })">
              <select x-ref="select" id="f-district_id" name="district_id" class="kt-select w-full" @error('district_id') aria-invalid="true" @enderror>
                <option value="">—</option>
                @foreach ($districts as $district)
                  <option value="{{ $district->id }}" @selected((string) old('district_id', $property->district_id) === (string) $district->id)>{{ $district->label() }}, {{ $district->province->label() }}</option>
                @endforeach
              </select>
            </div>
          </x-field>
          <x-field name="location" :label="__('properties.form.location')" :value="$property->location" :hint="__('properties.form.location_hint')" maxlength="255" />
          <x-field name="address" :label="__('properties.form.address')" :value="$property->address" maxlength="255" />
          <x-field name="address_th" :label="__('properties.form.address_th')" :value="$property->address_th" maxlength="255" />
          <x-field name="lat" type="number" step="any" :label="__('properties.form.lat')" :value="$property->lat" placeholder="7.8804" />
          <x-field name="lng" type="number" step="any" :label="__('properties.form.lng')" :value="$property->lng" placeholder="98.3923" />
        </div>
      </div>

      {{-- Size, rooms and what comes with it --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-element-11 text-lg text-primary"></i> @lang('properties.form.features')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4">
          <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <x-field name="bedrooms" type="number" min="0" max="50" :label="__('properties.form.bedrooms')" :value="$property->bedrooms" required />
            <x-field name="bathrooms" type="number" min="0" max="50" :label="__('properties.form.bathrooms')" :value="$property->bathrooms" required />
            <x-field name="floor" type="number" min="0" max="200" :label="__('properties.form.floor')" :value="$property->floor" />
            <x-field name="area" type="number" step="0.01" min="0" :label="__('properties.form.area')" :value="$property->area" />
            <x-field name="land_area" type="number" step="0.01" min="0" :label="__('properties.form.land_area')" :value="$property->land_area" />
          </div>

          <x-field name="facilities" :label="__('properties.form.facilities')" :hint="__('properties.form.facilities_hint')">
            <div wire:ignore x-data="tagInput({ whitelist: @js($allFacilities) })">
              <input type="text" x-ref="input" id="f-facilities" name="facilities" value="{{ $facilities }}" class="kt-input w-full" @error('facilities') aria-invalid="true" @enderror>
            </div>
          </x-field>

          <div class="flex flex-wrap gap-6">
            @foreach (['is_furnished', 'is_pet'] as $field)
              <label class="flex items-center gap-2.5 text-sm text-foreground">
                <input type="hidden" name="{{ $field }}" value="0">
                <input type="checkbox" class="kt-switch" name="{{ $field }}" value="1" @checked($switch($field))>
                <span>@lang("properties.form.$field")</span>
              </label>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-5">
      {{-- For sale, for rent or both, with the matching price --}}
      <div class="kt-card" x-data="{ sale: @js($switch('is_sale')), rent: @js($switch('is_rent')) }">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-dollar text-lg text-primary"></i> @lang('properties.form.offer')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4">
          <label class="flex items-center gap-2.5 text-sm font-semibold text-foreground">
            <input type="hidden" name="is_sale" value="0">
            <input type="checkbox" class="kt-switch" name="is_sale" value="1" x-model="sale" @error('is_sale') aria-invalid="true" @enderror>
            <span>@lang('properties.form.is_sale')</span>
          </label>
          <div x-show="sale" x-cloak>
            <x-field name="sale_price" type="number" step="1" min="0" :label="__('properties.form.sale_price')" :value="$property->sale_price ? (int) $property->sale_price : null" required />
          </div>

          <label class="flex items-center gap-2.5 text-sm font-semibold text-foreground">
            <input type="hidden" name="is_rent" value="0">
            <input type="checkbox" class="kt-switch" name="is_rent" value="1" x-model="rent">
            <span>@lang('properties.form.is_rent')</span>
          </label>
          <div x-show="rent" x-cloak>
            <x-field name="rent_price" type="number" step="1" min="0" :label="__('properties.form.rent_price')" :value="$property->rent_price ? (int) $property->rent_price : null" required />
          </div>
          @error('is_sale') <span class="block text-sm text-destructive">{{ $message }}</span> @enderror
        </div>
      </div>

      {{-- Who owns it and who handles it --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-people text-lg text-primary"></i> @lang('properties.form.people')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4">
          <x-field name="owner_id" :label="__('properties.form.owner_id')">
            <div x-data="ktSelect(null, { enableSearch: true })">
              <select x-ref="select" id="f-owner_id" name="owner_id" class="kt-select w-full" @error('owner_id') aria-invalid="true" @enderror>
                <option value="">—</option>
                @foreach ($owners as $owner)
                  <option value="{{ $owner->id }}" @selected((string) old('owner_id', $property->owner_id) === (string) $owner->id)>{{ $owner->fullName('en') }}{{ $owner->phone ? ' · '.$owner->phone : '' }}</option>
                @endforeach
              </select>
            </div>
            @can('create', \App\Models\Person::class)
              <a href="{{ route('backend.people.create', ['type' => 'owner']) }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-2xs text-primary hover:underline">
                <i class="ki-filled ki-plus"></i> @lang('properties.form.new_owner')
              </a>
            @endcan
          </x-field>

          <x-field name="user_id" :label="__('properties.form.user_id')">
            <div x-data="ktSelect(null)">
              <select x-ref="select" id="f-user_id" name="user_id" class="kt-select w-full" @error('user_id') aria-invalid="true" @enderror>
                <option value="">—</option>
                @foreach ($agents as $agent)
                  <option value="{{ $agent->id }}" @selected((string) old('user_id', $property->user_id) === (string) $agent->id)>{{ $agent->name }}</option>
                @endforeach
              </select>
            </div>
          </x-field>
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-note-2 text-lg text-primary"></i> @lang('properties.form.internal')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4">
          <label class="flex items-center gap-2.5 text-sm text-foreground">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" class="kt-switch" name="is_published" value="1" @checked($switch('is_published'))>
            <span>@lang('properties.form.is_published')</span>
          </label>
          <x-field type="textarea" name="remark" :label="__('properties.form.remark')" :value="$property->remark" maxlength="2000" :hint="__('properties.form.remark_hint')" />
        </div>
      </div>

      <x-photo-card :photos="$property->photos ?? collect()" />
    </div>
  </div>
</form>
