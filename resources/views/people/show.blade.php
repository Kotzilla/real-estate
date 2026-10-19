@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Contacts'), route('backend.people.index')],
        [$person->fullName()],
    ],
])

@php
  $rows = array_filter([
      __('people.form.first_name_th') => $person->first_name_th ? $person->fullName('th') : null,
      __('people.form.nickname') => $person->nickname,
      __('people.form.nationality') => $person->nationality,
      __('people.form.id_card_no') => $person->id_card_no,
      __('people.form.passport_no') => $person->passport_no,
      __('people.form.birth_on') => $person->birth_on?->format('d/m/Y'),
      __('people.form.phone') => $person->phone,
      __('people.form.email') => $person->email,
      __('people.form.line_id') => $person->line_id,
      __('people.form.whatsapp') => $person->whatsapp,
      __('people.form.address') => $person->address,
      __('people.form.address_th') => $person->address_th,
  ]);
  $bank = array_filter([
      __('people.form.bank_name') => $person->bank_name,
      __('people.form.bank_account_name') => $person->bank_account_name,
      __('people.form.bank_account_no') => $person->bank_account_no,
  ]);
@endphp

@section('title', $person->fullName())

@section('heading')
  <span class="flex flex-wrap items-center gap-2.5">
    {{ $person->fullName() }}
    <span class="kt-badge kt-badge-sm {{ $person->type->badgeClass() }}">{{ $person->type->label() }}</span>
  </span>
@endsection

@section('actions')
  <div class="flex items-center gap-2.5">
    @if ($person->type === \App\Enums\PersonType::Owner)
      @can('create', \App\Models\Property::class)
        <a href="{{ route('backend.properties.create', ['owner' => $person->id]) }}" class="kt-btn kt-btn-sm kt-btn-outline gap-1.5">
          <i class="ki-filled ki-home-3"></i> @lang('people.show.new_property')
        </a>
      @endcan
    @endif
    @can('update', $person)
      <a href="{{ route('backend.people.edit', $person) }}" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5">
        <i class="ki-filled ki-pencil"></i> @lang('common.edit')
      </a>
    @endcan
  </div>
@endsection

@section('content')
  <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="flex flex-col gap-5">
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-user text-lg text-primary"></i> @lang('people.form.details')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-2 text-sm">
          @forelse ($rows as $label => $value)
            <div class="flex justify-between gap-3">
              <span class="shrink-0 text-muted-foreground">{{ $label }}</span>
              <span class="text-end text-foreground">{{ $value }}</span>
            </div>
          @empty
            <span class="text-muted-foreground">—</span>
          @endforelse
        </div>
      </div>

      @if ($bank)
        <div class="kt-card">
          <div class="kt-card-header">
            <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-bank text-lg text-primary"></i> @lang('people.form.bank')</h3>
          </div>
          <div class="kt-card-content flex flex-col gap-2 text-sm">
            @foreach ($bank as $label => $value)
              <div class="flex justify-between gap-3">
                <span class="shrink-0 text-muted-foreground">{{ $label }}</span>
                <span class="text-end text-foreground">{{ $value }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      @if ($person->remark)
        <div class="kt-card">
          <div class="kt-card-header">
            <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-note-2 text-lg text-primary"></i> @lang('people.form.remark')</h3>
          </div>
          <div class="kt-card-content whitespace-pre-line text-sm">{{ $person->remark }}</div>
        </div>
      @endif
    </div>

    <div class="flex flex-col gap-5 lg:col-span-2">
      {{-- Listings they own --}}
      <div class="kt-card kt-card-grid">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-home-3 text-lg text-primary"></i> @lang('people.show.properties')</h3>
        </div>
        <div class="kt-card-table kt-scrollable-x-auto">
          <table class="kt-table kt-table-border">
            <tbody>
              @forelse ($person->properties as $property)
                <tr>
                  <td>
                    <a href="{{ route('backend.properties.show', $property) }}" class="font-medium hover:text-primary">{{ $property->title() }}</a>
                    <span class="block font-mono text-2xs text-muted-foreground">{{ $property->code }}</span>
                  </td>
                  <td><span class="kt-badge kt-badge-sm {{ $property->type->badgeClass() }}">{{ $property->type->label() }}</span></td>
                  <td class="text-end"><span class="kt-badge kt-badge-sm {{ $property->status->badgeClass() }}">{{ $property->status->label() }}</span></td>
                </tr>
              @empty
                <tr>
                  <td class="py-6 text-center text-muted-foreground">@lang('people.show.no_properties')</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Contracts they signed as tenant / buyer --}}
      <div class="kt-card kt-card-grid">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-document text-lg text-primary"></i> @lang('Contracts')</h3>
          @can('create', \App\Models\Contract::class)
            <a href="{{ route('backend.contracts.create', ['customer' => $person->id]) }}" class="kt-btn kt-btn-sm kt-btn-outline gap-1.5">
              <i class="ki-filled ki-plus"></i> @lang('contracts.table.add')
            </a>
          @endcan
        </div>
        <div class="kt-card-table kt-scrollable-x-auto">
          <table class="kt-table kt-table-border">
            <tbody>
              @forelse ($person->contracts as $contract)
                <tr>
                  <td><a href="{{ route('backend.contracts.show', $contract) }}" class="font-mono font-medium hover:text-primary">{{ $contract->code }}</a></td>
                  <td class="text-sm">{{ $contract->property->title() }}</td>
                  <td><span class="kt-badge kt-badge-sm {{ $contract->type->badgeClass() }}">{{ $contract->type->label() }}</span></td>
                  <td class="text-sm">{{ $contract->start_on->format('d/m/Y') }} – {{ $contract->end_on?->format('d/m/Y') ?? '…' }}</td>
                  <td class="text-end"><span class="kt-badge kt-badge-sm {{ $contract->status->badgeClass() }}">{{ $contract->status->label() }}</span></td>
                </tr>
              @empty
                <tr>
                  <td class="py-6 text-center text-muted-foreground">@lang('contracts.table.empty')</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
