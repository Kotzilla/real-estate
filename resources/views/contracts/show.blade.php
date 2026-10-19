@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Contracts'), route('backend.contracts.index')],
        [$contract->code],
    ],
])

@php
  $property = $contract->property;
  $owner = $property->owner;
  $money = array_filter([
      __($contract->isRent() ? 'contracts.show.rent' : 'contracts.show.sale_price') => '฿'.number_format((float) $contract->price),
      __('contracts.form.months') => $contract->isRent() ? __('contracts.months_count', ['count' => $contract->months]) : null,
      __('contracts.form.deposit') => '฿'.number_format((float) $contract->deposit),
      __('contracts.form.advance') => $contract->isRent() ? '฿'.number_format((float) $contract->advance) : null,
      __('contracts.show.due_on_signing') => '฿'.number_format($contract->dueOnSigning()),
      __('contracts.form.payment_day') => $contract->payment_day,
      __('contracts.form.value') => '฿'.number_format($contract->value()),
  ], fn ($value) => $value !== null);
@endphp

@section('title', $contract->code)

@section('heading')
  <span class="flex flex-wrap items-center gap-2.5">
    <span class="font-mono">{{ $contract->code }}</span>
    <span class="kt-badge kt-badge-sm {{ $contract->type->badgeClass() }}">{{ $contract->type->label() }}</span>
    <span class="kt-badge kt-badge-sm {{ $contract->status->badgeClass() }}">{{ $contract->status->label() }}</span>
  </span>
@endsection

@section('actions')
  <div class="flex items-center gap-2.5">
    <a href="{{ route('backend.contracts.print', [$contract, 'en']) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-outline gap-1.5">
      <i class="ki-filled ki-printer"></i> @lang('contracts.print_en')
    </a>
    <a href="{{ route('backend.contracts.print', [$contract, 'th']) }}" target="_blank" class="kt-btn kt-btn-sm kt-btn-outline gap-1.5">
      <i class="ki-filled ki-printer"></i> @lang('contracts.print_th')
    </a>
    @can('update', $contract)
      <a href="{{ route('backend.contracts.edit', $contract) }}" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5">
        <i class="ki-filled ki-pencil"></i> @lang('common.edit')
      </a>
    @endcan
  </div>
@endsection

@section('content')
  @if ($missing)
    {{-- What the printed copy will show as "-": fix it on the contact or listing before printing --}}
    <div class="mb-5 flex gap-3 rounded-lg border border-border bg-warning/10 px-4 py-3 text-sm">
      <i class="ki-filled ki-information-2 text-lg text-warning"></i>
      <div>
        <p class="font-medium text-foreground">@lang('contracts.show.missing_title')</p>
        <ul class="mt-1 list-disc ps-5 text-secondary-foreground">
          @foreach ($missing as $key)
            <li>@lang('contracts.missing.'.$key)</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="flex flex-col gap-5 lg:col-span-2">
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-home-3 text-lg text-primary"></i> @lang('contracts.form.property_id')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-1 text-sm">
          <a href="{{ route('backend.properties.show', $property) }}" class="text-base font-medium hover:text-primary">{{ $property->title() }}</a>
          <span class="font-mono text-2xs text-muted-foreground">{{ $property->code }}</span>
          <span class="text-secondary-foreground">{{ $property->fullAddress() ?: '—' }}</span>
        </div>
      </div>

      {{-- The two parties, as the contract names them --}}
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        @foreach ([
          [$contract->isRent() ? __('contracts.parties.lessor') : __('contracts.parties.seller'), $owner],
          [$contract->isRent() ? __('contracts.parties.lessee') : __('contracts.parties.buyer'), $contract->customer],
        ] as [$role, $party])
          <div class="kt-card">
            <div class="kt-card-header">
              <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-user text-lg text-primary"></i> {{ $role }}</h3>
            </div>
            <div class="kt-card-content flex flex-col gap-1 text-sm">
              @if ($party)
                <a href="{{ route('backend.people.show', $party) }}" class="font-medium hover:text-primary">{{ $party->fullName('en') }}</a>
                @if ($party->first_name_th)
                  <span class="text-secondary-foreground">{{ $party->fullName('th') }}</span>
                @endif
                <span class="text-secondary-foreground">{{ $party->documentNo() ?? '—' }}</span>
                <span class="text-secondary-foreground">{{ collect([$party->phone, $party->email])->filter()->join(' · ') }}</span>
              @else
                <span class="text-muted-foreground">—</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      @if ($contract->terms)
        <div class="kt-card">
          <div class="kt-card-header">
            <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-note-2 text-lg text-primary"></i> @lang('contracts.form.terms')</h3>
          </div>
          <div class="kt-card-content whitespace-pre-line text-sm">{{ $contract->terms }}</div>
        </div>
      @endif
    </div>

    <div class="flex flex-col gap-5">
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-dollar text-lg text-primary"></i> @lang('contracts.form.money')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-2 text-sm">
          @foreach ($money as $label => $value)
            <div class="flex justify-between gap-3">
              <span class="text-muted-foreground">{{ $label }}</span>
              <span class="text-end text-foreground">{{ $value }}</span>
            </div>
          @endforeach
          <div class="mt-1 flex justify-between gap-3 border-t border-border pt-3">
            <span class="text-muted-foreground">@lang('contracts.form.commission') ({{ rtrim(rtrim(number_format((float) $contract->commission_percent, 2), '0'), '.') }}%)</span>
            <span class="text-end font-semibold text-mono">฿{{ number_format((float) $contract->commission) }}</span>
          </div>
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-calendar text-lg text-primary"></i> @lang('contracts.form.dates')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-2 text-sm">
          @foreach (['signed_on', 'start_on', 'end_on'] as $field)
            <div class="flex justify-between gap-3">
              <span class="text-muted-foreground">@lang('contracts.form.'.$field)</span>
              <span class="text-foreground">{{ $contract->{$field}?->format('d/m/Y') ?? '—' }}</span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-people text-lg text-primary"></i> @lang('contracts.form.staff')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-2 text-sm">
          <div class="flex justify-between gap-3">
            <span class="text-muted-foreground">@lang('contracts.form.user_id')</span>
            <span class="text-foreground">{{ $contract->agent?->name ?? '—' }}</span>
          </div>
          <div class="flex justify-between gap-3">
            <span class="text-muted-foreground">@lang('contracts.form.witness_id')</span>
            <span class="text-foreground">{{ $contract->witness?->name ?? '—' }}</span>
          </div>
        </div>
      </div>

      @if ($contract->remark)
        <div class="kt-card">
          <div class="kt-card-header">
            <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-information-2 text-lg text-primary"></i> @lang('contracts.form.remark')</h3>
          </div>
          <div class="kt-card-content whitespace-pre-line text-sm">{{ $contract->remark }}</div>
        </div>
      @endif
    </div>
  </div>
@endsection
