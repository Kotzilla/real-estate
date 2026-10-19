@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Contacts')],
    ],
])

@section('title', __('Contacts'))

@section('content')
  @livewire('people.table')
@endsection
