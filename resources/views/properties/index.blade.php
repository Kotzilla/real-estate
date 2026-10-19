@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Properties')],
    ],
])

@section('title', __('Properties'))

@section('content')
  @livewire('properties.table')
@endsection
