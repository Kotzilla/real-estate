@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Properties'), route('backend.properties.index')],
        [$property->code, route('backend.properties.show', $property)],
        [__('common.edit')],
    ],
])

@section('title', __('properties.edit.title', ['code' => $property->code]))

@section('heading', __('properties.edit.title', ['code' => $property->code]))

@section('actions')
  <div class="flex items-center gap-2.5">
    <a href="{{ route('backend.properties.show', $property) }}" class="kt-btn kt-btn-sm kt-btn-outline">@lang('common.back')</a>
    <button type="submit" form="property-form" class="kt-btn kt-btn-sm kt-btn-primary">@lang('common.save')</button>
  </div>
@endsection

@section('content')
  @include('properties.partials.form', ['action' => route('backend.properties.update', $property)])
@endsection
