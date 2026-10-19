@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Contracts')],
    ],
])

@section('title', __('Contracts'))

@section('content')
  @livewire('contracts.table')
@endsection
