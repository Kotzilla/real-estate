{{-- The contact add / edit form, shared by both pages. $person is new on the create page. --}}
<form id="person-form" method="POST" action="{{ $action }}">
  @csrf
  @if ($person->exists)
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
      {{-- Names in English and Thai: each copy of a contract prints its own --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-user text-lg text-primary"></i> @lang('people.form.details')</h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 gap-4 lg:grid-cols-2">
          <x-field name="first_name" :label="__('people.form.first_name')" :value="$person->first_name" required maxlength="255" />
          <x-field name="last_name" :label="__('people.form.last_name')" :value="$person->last_name" maxlength="255" />
          <x-field name="first_name_th" :label="__('people.form.first_name_th')" :value="$person->first_name_th" maxlength="255" />
          <x-field name="last_name_th" :label="__('people.form.last_name_th')" :value="$person->last_name_th" maxlength="255" />
          <x-field name="nickname" :label="__('people.form.nickname')" :value="$person->nickname" maxlength="50" />
          <x-field name="nationality" :label="__('people.form.nationality')" :value="$person->nationality" maxlength="50" placeholder="Thai, British…" />
          <x-field name="id_card_no" :label="__('people.form.id_card_no')" :value="$person->id_card_no" maxlength="20" inputmode="numeric" />
          <x-field name="passport_no" :label="__('people.form.passport_no')" :value="$person->passport_no" maxlength="30" />
          <x-field name="birth_on" type="date" :label="__('people.form.birth_on')" :value="$person->birth_on" :hint="__('people.form.birth_on_hint')" />
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-geolocation-home text-lg text-primary"></i> @lang('people.form.contact')</h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 gap-4 lg:grid-cols-2">
          <x-field name="phone" type="tel" :label="__('people.form.phone')" :value="$person->phone" maxlength="20" inputmode="tel" placeholder="0812345678" />
          <x-field name="email" type="email" :label="__('people.form.email')" :value="$person->email" maxlength="255" />
          <x-field name="line_id" :label="__('people.form.line_id')" :value="$person->line_id" maxlength="100" />
          <x-field name="whatsapp" :label="__('people.form.whatsapp')" :value="$person->whatsapp" maxlength="30" />
          <div class="lg:col-span-2">
            <x-field name="address" :label="__('people.form.address')" :value="$person->address" maxlength="255" />
          </div>
          <div class="lg:col-span-2">
            <x-field name="address_th" :label="__('people.form.address_th')" :value="$person->address_th" maxlength="255" />
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-5">
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-profile-circle text-lg text-primary"></i> @lang('people.form.type')</h3>
        </div>
        <div class="kt-card-content">
          <x-field name="type" :label="__('people.form.type')" required>
            <div x-data="ktSelect(null)">
              <select x-ref="select" id="f-type" name="type" class="kt-select w-full" @error('type') aria-invalid="true" @enderror>
                @foreach (\App\Enums\PersonType::cases() as $case)
                  <option value="{{ $case->value }}" @selected(old('type', $person->type?->value) === $case->value)>{{ $case->label() }}</option>
                @endforeach
              </select>
            </div>
          </x-field>
        </div>
      </div>

      {{-- Owners: where the tenant pays the rent (printed on the lease) --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-bank text-lg text-primary"></i> @lang('people.form.bank')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4">
          <x-field name="bank_name" :label="__('people.form.bank_name')" :value="$person->bank_name" maxlength="100" placeholder="Kasikorn Bank" />
          <x-field name="bank_account_name" :label="__('people.form.bank_account_name')" :value="$person->bank_account_name" maxlength="255" />
          <x-field name="bank_account_no" :label="__('people.form.bank_account_no')" :value="$person->bank_account_no" maxlength="30" />
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-note-2 text-lg text-primary"></i> @lang('people.form.remark')</h3>
        </div>
        <div class="kt-card-content">
          <x-field type="textarea" name="remark" :label="__('people.form.remark')" :value="$person->remark" maxlength="2000" :hint="__('people.form.remark_hint')" />
        </div>
      </div>
    </div>
  </div>
</form>
