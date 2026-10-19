@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Contracts'), route('backend.contracts.index')],
        [__('contracts.create.title')],
    ],
])

@section('title', __('contracts.create.title'))

@section('heading', __('contracts.create.title'))

@section('actions')
  <div class="flex items-center gap-2.5">
    <a href="{{ route('backend.contracts.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">@lang('common.back')</a>
    <button type="submit" form="contract-form" class="kt-btn kt-btn-sm kt-btn-primary">@lang('common.save')</button>
  </div>
@endsection

@section('content')
  @include('contracts.partials.form', ['action' => route('backend.contracts.store')])
@endsection
