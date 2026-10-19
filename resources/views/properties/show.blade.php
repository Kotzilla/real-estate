@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Properties'), route('backend.properties.index')],
        [$property->code],
    ],
])

@php
  $cover = $property->photos->firstWhere('is_cover', true) ?? $property->photos->first();
  $facilities = $property->tagsWithType(\App\Models\Property::FACILITY);
  $details = array_filter([
      __('properties.form.type') => $property->type->label(),
      __('properties.form.bedrooms') => $property->bedrooms ?: null,
      __('properties.form.bathrooms') => $property->bathrooms ?: null,
      __('properties.form.floor') => $property->floor,
      __('properties.form.area') => $property->area ? number_format((float) $property->area).' m²' : null,
      __('properties.form.land_area') => $property->land_area ? number_format((float) $property->land_area).' m²' : null,
      __('properties.form.is_furnished') => $property->is_furnished ? __('common.yes') : __('common.no'),
      __('properties.form.is_pet') => $property->is_pet ? __('common.yes') : __('common.no'),
  ], fn ($value) => $value !== null);
@endphp

@section('title', $property->title())

@section('heading')
  <span class="flex flex-wrap items-center gap-2.5">
    {{ $property->title() }}
    <span class="font-mono text-sm font-normal text-muted-foreground">{{ $property->code }}</span>
    <span class="kt-badge kt-badge-sm {{ $property->status->badgeClass() }}">{{ $property->status->label() }}</span>
  </span>
@endsection

@section('actions')
  <div class="flex items-center gap-2.5">
    @can('create', \App\Models\Contract::class)
      <a href="{{ route('backend.contracts.create', ['property' => $property->id]) }}" class="kt-btn kt-btn-sm kt-btn-outline gap-1.5">
        <i class="ki-filled ki-document"></i> @lang('properties.show.new_contract')
      </a>
    @endcan
    @can('update', $property)
      <a href="{{ route('backend.properties.edit', $property) }}" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5">
        <i class="ki-filled ki-pencil"></i> @lang('common.edit')
      </a>
    @endcan
  </div>
@endsection

@section('content')
  <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="flex flex-col gap-5 lg:col-span-2">
      {{-- Gallery: the cover large, the rest as thumbnails; each opens the lightbox --}}
      <div class="kt-card">
        <div class="kt-card-content flex flex-col gap-3 p-3">
          @if ($cover)
            <button type="button" class="overflow-hidden rounded-lg" onclick="openLightbox({ url: @js($cover->url), name: @js($cover->name), isImage: true })">
              <img src="{{ $cover->src('lg') }}" alt="{{ $cover->alt ?? $property->title() }}" class="aspect-[16/9] w-full object-cover">
            </button>
            @if ($property->photos->count() > 1)
              <div class="grid grid-cols-4 gap-2 lg:grid-cols-6">
                @foreach ($property->photos as $photo)
                  <button type="button" class="overflow-hidden rounded-md" onclick="openLightbox({ url: @js($photo->url), name: @js($photo->name), isImage: true })">
                    <img src="{{ $photo->src('sm') }}" alt="{{ $photo->alt }}" loading="lazy" class="aspect-square w-full object-cover">
                  </button>
                @endforeach
              </div>
            @endif
          @else
            <div class="flex aspect-[16/9] flex-col items-center justify-center gap-2 rounded-lg bg-muted text-muted-foreground">
              <i class="ki-filled ki-picture text-4xl"></i>
              <span class="text-sm">@lang('properties.show.no_photos')</span>
            </div>
          @endif
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-note-2 text-lg text-primary"></i> @lang('properties.form.description')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-4 text-sm leading-relaxed">
          @if ($property->description || $property->description_th)
            @if ($property->description)
              <p class="whitespace-pre-line">{{ $property->description }}</p>
            @endif
            @if ($property->description_th)
              <p class="whitespace-pre-line border-t border-border pt-4">{{ $property->description_th }}</p>
            @endif
          @else
            <span class="text-muted-foreground">—</span>
          @endif

          @if ($facilities->isNotEmpty())
            <div class="flex flex-wrap gap-1.5 border-t border-border pt-4">
              @foreach ($facilities as $facility)
                <span class="kt-badge kt-badge-sm kt-badge-light kt-badge-primary">{{ $facility->name }}</span>
              @endforeach
            </div>
          @endif
        </div>
      </div>

      {{-- Its leases and sales --}}
      <div class="kt-card kt-card-grid">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-document text-lg text-primary"></i> @lang('Contracts')</h3>
        </div>
        <div class="kt-card-table kt-scrollable-x-auto">
          <table class="kt-table kt-table-border">
            <thead>
              <tr>
                <th>@lang('contracts.table.contract')</th>
                <th>@lang('contracts.table.customer')</th>
                <th>@lang('contracts.table.type')</th>
                <th class="text-end">@lang('contracts.table.price')</th>
                <th>@lang('contracts.table.period')</th>
                <th>@lang('contracts.table.status')</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($property->contracts as $contract)
                <tr>
                  <td><a href="{{ route('backend.contracts.show', $contract) }}" class="font-mono font-medium hover:text-primary">{{ $contract->code }}</a></td>
                  <td class="text-sm">{{ $contract->customer->fullName() }}</td>
                  <td><span class="kt-badge kt-badge-sm {{ $contract->type->badgeClass() }}">{{ $contract->type->label() }}</span></td>
                  <td class="text-end text-sm">{{ number_format((float) $contract->price) }}</td>
                  <td class="text-sm">{{ $contract->start_on->format('d/m/Y') }} – {{ $contract->end_on?->format('d/m/Y') ?? '…' }}</td>
                  <td><span class="kt-badge kt-badge-sm {{ $contract->status->badgeClass() }}">{{ $contract->status->label() }}</span></td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="py-6 text-center text-muted-foreground">@lang('contracts.table.empty')</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-5">
      {{-- Prices --}}
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-dollar text-lg text-primary"></i> @lang('properties.form.offer')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-3">
          @if ($property->is_sale)
            <div class="flex items-baseline justify-between gap-3">
              <span class="text-sm text-muted-foreground">@lang('properties.form.sale_price')</span>
              <span class="text-lg font-semibold text-mono">฿{{ number_format((float) $property->sale_price) }}</span>
            </div>
          @endif
          @if ($property->is_rent)
            <div class="flex items-baseline justify-between gap-3">
              <span class="text-sm text-muted-foreground">@lang('properties.form.rent_price')</span>
              <span class="text-lg font-semibold text-mono">฿{{ number_format((float) $property->rent_price) }}<span class="text-sm font-normal text-muted-foreground">@lang('properties.per_month')</span></span>
            </div>
          @endif
          @unless ($property->is_published)
            <span class="kt-badge kt-badge-sm kt-badge-light kt-badge-warning self-start">@lang('properties.show.unpublished')</span>
          @endunless
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-element-11 text-lg text-primary"></i> @lang('properties.form.features')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-2 text-sm">
          @foreach ($details as $label => $value)
            <div class="flex justify-between gap-3">
              <span class="text-muted-foreground">{{ $label }}</span>
              <span class="text-end text-foreground">{{ $value }}</span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-geolocation text-lg text-primary"></i> @lang('properties.form.where')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-2 text-sm">
          @if ($property->location)
            <span class="font-medium text-foreground">{{ $property->location }}</span>
          @endif
          <span class="text-secondary-foreground">{{ $property->fullAddress() ?: '—' }}</span>
          @if ($property->lat && $property->lng)
            <a href="https://www.google.com/maps?q={{ $property->lat }},{{ $property->lng }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-primary hover:underline">
              <i class="ki-filled ki-map"></i> @lang('properties.show.map')
            </a>
          @endif
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-people text-lg text-primary"></i> @lang('properties.form.people')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-3 text-sm">
          <div>
            <span class="block text-2xs uppercase text-muted-foreground">@lang('properties.form.owner_id')</span>
            @if ($property->owner)
              <a href="{{ route('backend.people.show', $property->owner) }}" class="font-medium hover:text-primary">{{ $property->owner->fullName() }}</a>
              <span class="block text-secondary-foreground">{{ collect([$property->owner->phone, $property->owner->email])->filter()->join(' · ') }}</span>
            @else
              <span class="text-muted-foreground">—</span>
            @endif
          </div>
          <div>
            <span class="block text-2xs uppercase text-muted-foreground">@lang('properties.form.user_id')</span>
            <span class="font-medium">{{ $property->agent?->name ?? '—' }}</span>
            @if ($property->agent?->phone)
              <span class="block text-secondary-foreground">{{ $property->agent->phone }}</span>
            @endif
          </div>
        </div>
      </div>

      @if ($property->remark)
        <div class="kt-card">
          <div class="kt-card-header">
            <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-information-2 text-lg text-primary"></i> @lang('properties.form.remark')</h3>
          </div>
          <div class="kt-card-content whitespace-pre-line text-sm">{{ $property->remark }}</div>
        </div>
      @endif
    </div>
  </div>
@endsection
