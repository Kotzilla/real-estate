@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Contacts'), route('backend.people.index')],
        [$person->fullName(), route('backend.people.show', $person)],
        [__('common.edit')],
    ],
])

@section('title', $person->fullName())

@section('heading', $person->fullName())

@section('actions')
  <div class="flex items-center gap-2.5">
    <a href="{{ route('backend.people.show', $person) }}" class="kt-btn kt-btn-sm kt-btn-outline">@lang('common.back')</a>
    <button type="submit" form="person-form" class="kt-btn kt-btn-sm kt-btn-primary">@lang('common.save')</button>
  </div>
@endsection

@section('content')
  @include('people.partials.form', ['action' => route('backend.people.update', $person)])
@endsection
