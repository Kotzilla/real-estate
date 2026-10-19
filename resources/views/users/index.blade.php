@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Settings')],
        [__('Users')],
    ],
])

@section('title', __('Users'))

@section('content')
  {{-- The sidebar's Archive link is users.archived, the Active one users.index?status=active --}}
  @livewire('users.table', ['status' => request()->routeIs('backend.users.archived') || request('status') === 'archived' ? 'archived' : 'active'])
@endsection
