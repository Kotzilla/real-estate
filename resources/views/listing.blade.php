{{-- A listing's public page (ListingController): photos, price, facts, description, facilities, where it is and who to
     ask. Search engines get its own title, description, canonical URL and Open Graph card. --}}
@extends('layouts.site')

@php
  $title = $property->title();
  $area = $property->location ?: __('home.island');
  $offer = $property->is_sale && $property->is_rent ? 'sale_or_rent' : ($property->is_sale ? 'sale' : 'rent');
  $description = trim(app()->getLocale() === 'th' && $property->description_th ? $property->description_th : (string) $property->description);
  $photos = $property->photos->sortByDesc('is_cover')->values();
  $placeholder = asset('assets/img/placeholder.png');
  $count = fn ($n, $one, $many) => __($n === 1 ? $one : $many, ['count' => $n]);
  $facts = array_filter([
    'home-2' => $property->bedrooms ? $count($property->bedrooms, 'home.bedroom', 'home.bedrooms_n') : null,
    'drop' => $property->bathrooms ? $count($property->bathrooms, 'home.bathroom', 'home.bathrooms_n') : null,
    'size' => $property->area ? __('home.living', ['m2' => number_format((float) $property->area)]) : null,
    'abstract-41' => $property->land_area ? __('home.land', ['m2' => number_format((float) $property->land_area)]) : null,
    'star' => $property->is_furnished ? __('listing.furnished') : null,
    'heart' => $property->is_pet ? __('listing.pets') : null,
  ]);
  $agent = $property->agent;
@endphp

@section('title', __('listing.title', ['title' => $title, 'type' => $property->type->label(), 'offer' => __('listing.'.$offer), 'area' => $area]))
@section('description', \Illuminate\Support\Str::limit($description ?: __('home.meta'), 155))

@section('canonical', $property->publicUrl())
@section('og_title', $title)
@section('og_description', \Illuminate\Support\Str::limit($description ?: __('home.meta'), 155))
@if ($photos->isNotEmpty())
  @section('og_image', $photos->first()->src('lg'))
@endif

@push('scripts')
  @vite('resources/js/limited-textarea.js')
@endpush

@section('content')
  <div class="kt-scrollable-y-auto grow">
    <article class="listing">
      <a href="{{ route('listings') }}" class="listing-back"><i class="ki-filled ki-arrow-left"></i> @lang('listing.back')</a>

      <header class="listing-head">
        <div>
          <h1 class="listing-title">{{ $title }}</h1>
          <p class="listing-meta"><i class="ki-filled ki-geolocation"></i> @lang('home.type_in_area', ['type' => e($property->type->label()), 'area' => e($area)])</p>
        </div>
        @if ($property->status === \App\Enums\PropertyStatus::Reserved)
          <span class="listing-flag">@lang('listing.reserved')</span>
        @endif
      </header>

      <div class="listing-gallery">
        @forelse ($photos->take(5) as $photo)
          <img src="{{ $photo->src($loop->first ? 'lg' : 'md') }}" alt="{{ $photo->alt ?: __('listing.photo', ['n' => $loop->iteration, 'title' => $title]) }}"
            @unless ($loop->first) loading="lazy" @endunless onerror="this.onerror = null; this.src = '{{ $placeholder }}'">
        @empty
          <img src="{{ $placeholder }}" alt="">
        @endforelse
      </div>

      <div class="listing-body">
        <div class="listing-main">
          @if ($facts)
            <section class="listing-section">
              <h2 class="listing-h2"><i class="ki-filled ki-information-2"></i> @lang('listing.details')</h2>
              <ul class="listing-facts">
                @foreach ($facts as $icon => $fact)
                  <li><i class="ki-filled ki-{{ $icon }}"></i> {{ $fact }}</li>
                @endforeach
              </ul>
            </section>
          @endif

          @if ($description)
            <section class="listing-section">
              <h2 class="listing-h2"><i class="ki-filled ki-document"></i> @lang('listing.description')</h2>
              <div class="listing-text">{!! nl2br(e($description)) !!}</div>
            </section>
          @endif

          @if ($facilities->isNotEmpty())
            <section class="listing-section">
              <h2 class="listing-h2"><i class="ki-filled ki-check"></i> @lang('listing.facilities')</h2>
              <ul class="listing-facilities">
                @foreach ($facilities as $facility)
                  <li><i class="ki-filled ki-{{ \App\Models\Property::facilityIcon($facility) }}"></i> {{ $facility }}</li>
                @endforeach
              </ul>
            </section>
          @endif

          @if ($property->lat && $property->lng)
            <section class="listing-section">
              <h2 class="listing-h2"><i class="ki-filled ki-geolocation"></i> @lang('listing.location')</h2>
              <a href="https://www.google.com/maps?q={{ $property->lat }},{{ $property->lng }}" target="_blank" rel="noopener" class="listing-map">
                @if ($mapToken)
                  <img src="https://api.mapbox.com/styles/v1/mapbox/light-v11/static/pin-l+2f6b62({{ $property->lng }},{{ $property->lat }})/{{ $property->lng }},{{ $property->lat }},13,0/800x400@2x?access_token={{ $mapToken }}"
                    alt="{{ __('listing.map', ['title' => $title]) }}" loading="lazy">
                @else
                  <span>{{ $property->lat }}, {{ $property->lng }}</span>
                @endif
              </a>
            </section>
          @endif
        </div>

        <aside class="listing-aside">
          <div class="listing-box">
            @if ($property->is_sale)
              <p class="listing-offer">@lang('listing.for_sale')</p>
              <p class="listing-price">฿{{ number_format((float) $property->sale_price) }}</p>
            @endif
            @if ($property->is_rent)
              <p class="listing-offer">@lang('listing.for_rent')</p>
              <p class="listing-price">฿{{ number_format((float) $property->rent_price) }} <small>@lang('listing.per_month')</small></p>
            @endif
            <p class="listing-code">@lang('listing.code') {{ $property->code }}</p>
          </div>

          @if ($agent)
            <div class="listing-box">
              <h2 class="listing-h2"><i class="ki-filled ki-profile-circle"></i> @lang('listing.contact')</h2>
              <p class="listing-agent">{{ $agent->name }} <small>@lang('listing.agent')</small></p>
              <div class="flex flex-col gap-2">
                @if ($agent->phone)
                  <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agent->phone) }}" class="listing-button"><i class="ki-filled ki-phone"></i> @lang('listing.call') {{ $agent->phone }}</a>
                @endif
                @if ($agent->email)
                  <a href="mailto:{{ $agent->email }}?subject={{ rawurlencode($property->code.' '.$property->name) }}" class="listing-button is-quiet"><i class="ki-filled ki-sms"></i> @lang('listing.email')</a>
                @endif
              </div>
            </div>
          @endif

          <div class="listing-box" id="enquiry">
            <h2 class="listing-h2"><i class="ki-filled ki-calendar"></i> @lang('listing.enquiry.title')</h2>
            @if (session('enquiry_sent'))
              <p class="listing-sent"><i class="ki-filled ki-check-circle"></i> @lang('listing.enquiry.sent')</p>
            @else
              <p class="listing-hint">@lang('listing.enquiry.hint')</p>
              <form method="POST" action="{{ route('listing.enquire', $property->code) }}" class="listing-form">
                @csrf
                {{-- honeypot: hidden from people, so only bots fill it (EnquiryRequest) --}}
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                <label>
                  <span class="required">@lang('listing.enquiry.name')</span>
                  <input type="text" name="name" value="{{ old('name') }}" maxlength="100" autocomplete="name" class="kt-input" @error('name') aria-invalid="true" @enderror>
                  @error('name')<small class="listing-error">{{ $message }}</small>@enderror
                </label>
                <label>
                  <span class="required">@lang('listing.enquiry.phone')</span>
                  <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="20" autocomplete="tel" class="kt-input" @error('phone') aria-invalid="true" @enderror>
                  @error('phone')<small class="listing-error">{{ $message }}</small>@enderror
                </label>
                <label>
                  <span>@lang('listing.enquiry.message')</span>
                  <x-textarea name="message" maxlength="1000" :placeholder="__('listing.enquiry.placeholder')" :aria-invalid="$errors->has('message') ? 'true' : null">{{ old('message') }}</x-textarea>
                  @error('message')<small class="listing-error">{{ $message }}</small>@enderror
                </label>
                <button type="submit" class="listing-button"><i class="ki-filled ki-calendar-tick"></i> @lang('listing.enquiry.send')</button>
              </form>
            @endif
          </div>
        </aside>
      </div>
    </article>

    @include('layouts.partials.site-footer')
  </div>
@endsection
