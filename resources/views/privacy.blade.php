@extends('layouts.site')

{{-- The privacy policy (Thailand's PDPA), text in lang/{en,th}/privacy.php --}}
@php
  $name = config('app.name');
  $updated = \Illuminate\Support\Carbon::parse('2026-10-19')->locale(app()->getLocale())->translatedFormat('j F Y');
@endphp

@section('title', __('privacy.title').' — '.$name)
@section('description', __('privacy.meta', ['name' => $name]))

@push('head')
  <link rel="canonical" href="{{ route('privacy') }}">
@endpush

@section('content')
  <div class="kt-scrollable-y-auto grow">
    <article class="legal">
      <h1 class="listing-title">@lang('privacy.title')</h1>
      <p class="legal-updated">@lang('privacy.updated', ['date' => $updated])</p>
      <p>@lang('privacy.intro', ['name' => e($name)])</p>
      @foreach (__('privacy.sections') as [$heading, $text])
        <h2 class="listing-h2">{{ $heading }}</h2>
        <p>{{ str_replace(':email', config('site.email'), $text) }}</p>
      @endforeach
    </article>

    @include('layouts.partials.site-footer')
  </div>
@endsection
