{{-- The contract add / edit form, shared by both pages. $contract is new on the create page. The lease-only fields
     hide for a sale; the commission preview follows the figures as they are typed (the model works out the saved one). --}}
@php
  $type = old('type', $contract->type?->value ?? 'rent');
  $select = fn (string $field, $value) => (string) old($field, $contract->{$field}) === (string) $value;
@endphp
<form id="contract-form" method="POST" action="{{ $action }}"
  x-data="{
    type: @js($type),
    price: @js((float) old('price', $contract->price)),
    months: @js((int) old('months', $contract->months)),
    percent: @js((float) old('commission_percent', $contract->commission_percent)),
    get value() { return this.type === 'rent' ? this.price * this.months : this.price },
    get commission() { return Math.round(this.value * this.percent) / 100 },
    money(n) { return (Number(n) || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }) },
  }">
  @csrf
  @if ($contract->exists)
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
      {{-- What, to whom, which kind --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-document text-lg text-primary"></i> @lang('contracts.form.deal')</h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 gap-4 lg:grid-cols-2">
          <div class="lg:col-span-2">
            <span class="mb-1 block text-sm font-semibold text-foreground required">@lang('contracts.form.type')</span>
            <div class="flex flex-wrap gap-5">
              @foreach (\App\Enums\ContractType::cases() as $case)
                <label class="flex items-center gap-2 text-sm">
                  <input type="radio" class="kt-radio" name="type" value="{{ $case->value }}" x-model="type" @error('type') aria-invalid="true" @enderror>
                  {{ $case->label() }}
                </label>
              @endforeach
            </div>
            @error('type') <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
          </div>

          <x-field name="property_id" :label="__('contracts.form.property_id')" required>
            <div x-data="ktSelect(null, { enableSearch: true })">
              <select x-ref="select" id="f-property_id" name="property_id" class="kt-select w-full" @error('property_id') aria-invalid="true" @enderror>
                <option value="">—</option>
                @foreach ($properties as $property)
                  <option value="{{ $property->id }}" @selected($select('property_id', $property->id))>{{ $property->code }} · {{ $property->title() }}</option>
                @endforeach
              </select>
            </div>
          </x-field>

          <x-field name="customer_id" :label="__('contracts.form.customer_id')" required>
            <div x-data="ktSelect(null, { enableSearch: true })">
              <select x-ref="select" id="f-customer_id" name="customer_id" class="kt-select w-full" @error('customer_id') aria-invalid="true" @enderror>
                <option value="">—</option>
                @foreach ($customers as $customer)
                  <option value="{{ $customer->id }}" @selected($select('customer_id', $customer->id))>{{ $customer->fullName('en') }}{{ $customer->phone ? ' · '.$customer->phone : '' }}</option>
                @endforeach
              </select>
            </div>
            @can('create', \App\Models\Person::class)
              <a href="{{ route('backend.people.create', ['type' => 'customer']) }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-2xs text-primary hover:underline">
                <i class="ki-filled ki-plus"></i> @lang('contracts.form.new_customer')
              </a>
            @endcan
          </x-field>

          <x-field name="status" :label="__('contracts.form.status')" required :hint="__('contracts.form.status_hint')">
            <div x-data="ktSelect(null)">
              <select x-ref="select" id="f-status" name="status" class="kt-select w-full" @error('status') aria-invalid="true" @enderror>
                @foreach (\App\Enums\ContractStatus::cases() as $case)
                  <option value="{{ $case->value }}" @selected(old('status', $contract->status?->value) === $case->value)>{{ $case->label() }}</option>
                @endforeach
              </select>
            </div>
          </x-field>
        </div>
      </div>

      {{-- The money --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-dollar text-lg text-primary"></i> @lang('contracts.form.money')</h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 gap-4 lg:grid-cols-3">
          <x-field name="price" type="number" step="0.01" min="0" :label="__('contracts.form.price')" :value="$contract->price" required
            :hint="__('contracts.form.price_hint')" x-model.number="price" />
          <div x-show="type === 'rent'" x-cloak>
            <x-field name="months" type="number" min="1" max="120" :label="__('contracts.form.months')" :value="$contract->months" required x-model.number="months" />
          </div>
          <div x-show="type === 'rent'" x-cloak>
            <x-field name="payment_day" type="number" min="1" max="31" :label="__('contracts.form.payment_day')" :value="$contract->payment_day" required :hint="__('contracts.form.payment_day_hint')" />
          </div>
          <x-field name="deposit" type="number" step="0.01" min="0" :label="__('contracts.form.deposit')" :value="$contract->deposit" :hint="__('contracts.form.deposit_hint')" />
          <div x-show="type === 'rent'" x-cloak>
            <x-field name="advance" type="number" step="0.01" min="0" :label="__('contracts.form.advance')" :value="$contract->advance" :hint="__('contracts.form.advance_hint')" />
          </div>
          <x-field name="commission_percent" type="number" step="0.01" min="0" max="100" :label="__('contracts.form.commission_percent')" :value="$contract->commission_percent" required x-model.number="percent" />

          <div class="flex flex-wrap gap-x-8 gap-y-2 rounded-lg bg-muted/50 px-4 py-3 text-sm lg:col-span-3">
            <span><span class="text-muted-foreground">@lang('contracts.form.value')</span> <b class="text-mono" x-text="'฿' + money(value)"></b></span>
            <span><span class="text-muted-foreground">@lang('contracts.form.commission')</span> <b class="text-mono" x-text="'฿' + money(commission)"></b></span>
          </div>
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-calendar text-lg text-primary"></i> @lang('contracts.form.dates')</h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 gap-4 lg:grid-cols-3">
          <x-field name="signed_on" type="date" :label="__('contracts.form.signed_on')" :value="$contract->signed_on" required />
          <x-field name="start_on" type="date" :label="__('contracts.form.start_on')" :value="$contract->start_on" required />
          <x-field name="end_on" type="date" :label="__('contracts.form.end_on')" :value="$contract->end_on" :hint="__('contracts.form.end_on_hint')" />
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-5">
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-people text-lg text-primary"></i> @lang('contracts.form.staff')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4">
          @foreach (['user_id', 'witness_id'] as $field)
            <x-field :name="$field" :label="__('contracts.form.'.$field)">
              <div x-data="ktSelect(null)">
                <select x-ref="select" id="f-{{ $field }}" name="{{ $field }}" class="kt-select w-full" @error($field) aria-invalid="true" @enderror>
                  <option value="">—</option>
                  @foreach ($staff as $member)
                    <option value="{{ $member->id }}" @selected($select($field, $member->id))>{{ $member->name }}</option>
                  @endforeach
                </select>
              </div>
            </x-field>
          @endforeach
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-note-2 text-lg text-primary"></i> @lang('contracts.form.notes')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4">
          <x-field type="textarea" name="terms" :label="__('contracts.form.terms')" :value="$contract->terms" maxlength="5000" rows="5" :hint="__('contracts.form.terms_hint')" />
          <x-field type="textarea" name="remark" :label="__('contracts.form.remark')" :value="$contract->remark" maxlength="2000" :hint="__('contracts.form.remark_hint')" />
        </div>
      </div>
    </div>
  </div>
</form>
