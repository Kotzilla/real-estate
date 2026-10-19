@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Properties'), route('backend.properties.index')],
        [__('properties.create.title')],
    ],
])

@section('title', __('properties.create.title'))

@section('heading', __('properties.create.title'))

@section('actions')
  <div class="flex items-center gap-2.5">
    <a href="{{ route('backend.properties.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">@lang('common.back')</a>
    <button type="submit" form="property-form" class="kt-btn kt-btn-sm kt-btn-primary">@lang('common.save')</button>
  </div>
@endsection

@section('content')
  @include('properties.partials.form', ['action' => route('backend.properties.store')])
@endsection
