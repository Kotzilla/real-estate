@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Enquiries')],
    ],
])

@section('title', __('Enquiries'))

@section('content')
  @livewire('enquiries.table')
@endsection
