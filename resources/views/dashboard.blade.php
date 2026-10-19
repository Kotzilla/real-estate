@extends('layouts.app', ['breadcrumbs' => [[__('Dashboard')]]])

@php
  $tiles = array_filter([
      ['home-3', 'dashboard.for_sale', $stats['for_sale'], route('backend.properties.index', ['offer' => 'sale', 'status' => 'available'])],
      ['key', 'dashboard.for_rent', $stats['for_rent'], route('backend.properties.index', ['offer' => 'rent', 'status' => 'available'])],
      ['document', 'dashboard.rented', $stats['rented'], route('backend.contracts.index', ['type' => 'rent', 'status' => 'active'])],
      ['dollar', 'dashboard.commission', $stats['commission'] === null ? null : '฿'.number_format($stats['commission']), route('backend.contracts.index')],
  ], fn ($tile) => $tile[2] !== null);
@endphp

@section('title', __('Dashboard'))

@section('heading', __('Dashboard'))

@section('content')
  <div class="flex flex-col gap-5">
    @if ($tiles)
      <div class="dash-metrics">
        @foreach ($tiles as [$icon, $label, $value, $url])
          <a href="{{ $url }}" class="kt-card hover:border-primary">
            <div class="kt-card-content flex items-center gap-4">
              <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"><i class="ki-filled ki-{{ $icon }} text-xl"></i></span>
              <span class="flex flex-col">
                <span class="text-2xl font-semibold leading-none text-mono">{{ is_int($value) ? number_format($value) : $value }}</span>
                <span class="mt-1.5 text-sm text-secondary-foreground">@lang($label)</span>
              </span>
            </div>
          </a>
        @endforeach
      </div>
    @endif

    @if ($canContracts)
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        @foreach ([
          ['time', __('dashboard.ending', ['days' => \App\Http\Controllers\DashboardController::ENDING_DAYS]), $ending, 'end_on', __('dashboard.no_ending')],
          ['notepad-edit', __('dashboard.drafts'), $drafts, 'signed_on', __('dashboard.no_drafts')],
        ] as [$icon, $title, $contracts, $dateField, $empty])
          <div class="kt-card kt-card-grid">
            <div class="kt-card-header">
              <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-{{ $icon }} text-lg text-primary"></i> {{ $title }}</h3>
            </div>
            <div class="kt-card-table kt-scrollable-x-auto">
              <table class="kt-table kt-table-border">
                <tbody>
                  @forelse ($contracts as $contract)
                    <tr>
                      <td>
                        <a href="{{ route('backend.contracts.show', $contract) }}" class="font-mono font-medium hover:text-primary">{{ $contract->code }}</a>
                        <span class="block text-2xs text-muted-foreground">{{ $contract->property->title() }}</span>
                      </td>
                      <td class="text-sm">{{ $contract->customer->fullName() }}</td>
                      <td class="whitespace-nowrap text-end text-sm">{{ $contract->{$dateField}?->format('d/m/Y') }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td class="py-6 text-center text-muted-foreground">{{ $empty }}</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
