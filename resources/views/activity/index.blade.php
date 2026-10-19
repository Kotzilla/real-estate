@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Activity log')],
    ],
])

@section('title', __('Activity log'))

@section('content')
  @livewire('activity.table')
@endsection
