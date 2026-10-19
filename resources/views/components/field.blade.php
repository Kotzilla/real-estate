{{--
  A form field: label (with the red asterisk when required), the control, an optional hint and the field's error.
  type: text / tel / email / number (an input), date (flatpickr via datePicker, value Y-m-d), textarea (auto-size, give
  it a maxlength matching the request's max:). Pass a slot instead for a select or anything else — the slot is then the
  control, and it sets its own aria-invalid.
  <x-field name="name" :label="__('properties.form.name')" :value="$property->name" required />
--}}
@props(['name', 'label', 'value' => null, 'type' => 'text', 'required' => false, 'hint' => null])
@php
  $id = 'f-'.str_replace(['[', ']', '.'], '-', $name);
  $current = old($name, $value instanceof \Carbon\CarbonInterface ? $value->toDateString() : $value);
@endphp
<div>
  <label for="{{ $id }}" class="mb-1 block text-sm font-semibold text-foreground {{ $required ? 'required' : '' }}">{{ $label }}</label>
  @if (! $slot->isEmpty())
    {{ $slot }}
  @elseif ($type === 'textarea')
    <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['rows' => 3])->class('kt-textarea auto-size w-full') }} @error($name) aria-invalid="true" @enderror>{{ $current }}</textarea>
  @elseif ($type === 'date')
    <div x-data="datePicker">
      <input x-ref="input" type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $current }}" {{ $attributes->class('kt-input w-full') }} @error($name) aria-invalid="true" @enderror>
    </div>
  @else
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $current }}" {{ $attributes->class('kt-input w-full') }} @error($name) aria-invalid="true" @enderror>
  @endif
  @if ($hint)
    <span class="mt-1 block text-2xs text-muted-foreground">{{ $hint }}</span>
  @endif
  @error($name) <span class="mt-1 block text-sm text-destructive">{{ $message }}</span> @enderror
</div>
