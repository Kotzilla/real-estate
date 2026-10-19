@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Contacts'), route('backend.people.index')],
        [__('people.create.title')],
    ],
])

@section('title', __('people.create.title'))

@section('heading', __('people.create.title'))

@section('actions')
  <div class="flex items-center gap-2.5">
    <a href="{{ route('backend.people.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">@lang('common.back')</a>
    <button type="submit" form="person-form" class="kt-btn kt-btn-sm kt-btn-primary">@lang('common.save')</button>
  </div>
@endsection

@section('content')
  @include('people.partials.form', ['action' => route('backend.people.store')])
@endsection
