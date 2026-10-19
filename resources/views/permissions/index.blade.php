@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Settings')],
        [__('Permissions')],
    ],
])

@section('title', __('Permissions'))

@section('content')
  @livewire('permissions.matrix')
@endsection
