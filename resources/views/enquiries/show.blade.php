@extends('layouts.app', [
    'breadcrumbs' => [
        [__('Enquiries'), route('backend.enquiries.index')],
        [$enquiry->name],
    ],
])

@php
  $subject = $enquiry->enquirable;
@endphp

@section('title', $enquiry->name)

@section('heading')
  <span class="flex flex-wrap items-center gap-2.5">
    {{ $enquiry->name }}
    <span class="kt-badge kt-badge-sm {{ $enquiry->status->badgeClass() }}">{{ $enquiry->status->label() }}</span>
  </span>
@endsection

@section('actions')
  @can('update', $enquiry)
    {{-- one button per status it can move to --}}
    <div class="flex items-center gap-2.5">
      @foreach (\App\Enums\EnquiryStatus::cases() as $status)
        @continue($status === $enquiry->status)
        <form method="POST" action="{{ route('backend.enquiries.update', $enquiry) }}">
          @csrf
          @method('PUT')
          <input type="hidden" name="status" value="{{ $status->value }}">
          <button type="submit" class="kt-btn kt-btn-sm {{ $status === \App\Enums\EnquiryStatus::Closed ? 'kt-btn-outline' : 'kt-btn-primary' }}">
            @lang('enquiries.move_to', ['status' => e($status->label())])
          </button>
        </form>
      @endforeach
    </div>
  @endcan
@endsection

@section('content')
  <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="flex flex-col gap-5 lg:col-span-2">
      @livewire('comments', ['model' => $enquiry])
    </div>

    <div class="flex flex-col gap-5">
      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-user text-lg text-primary"></i> @lang('enquiries.visitor')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-2 text-sm">
          <span class="font-medium text-foreground">{{ $enquiry->name }}</span>
          <a href="tel:{{ $enquiry->phone }}" class="flex items-center gap-1.5 hover:text-primary"><i class="ki-filled ki-phone"></i> {{ $enquiry->phone }}</a>
          <span class="text-2xs text-muted-foreground">@lang('enquiries.received') {{ $enquiry->created_at->format('d/m/Y H:i') }}</span>
          @if ($enquiry->message)
            <div class="mt-1 whitespace-pre-line border-t border-border pt-3">{{ $enquiry->message }}</div>
          @endif
        </div>
      </div>

      <div class="kt-card">
        <div class="kt-card-header">
          <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-home-3 text-lg text-primary"></i> @lang('enquiries.property')</h3>
        </div>
        <div class="kt-card-content flex flex-col gap-1 text-sm">
          @if ($subject instanceof \App\Models\Property)
            <a href="{{ route('backend.properties.show', $subject) }}" class="font-medium hover:text-primary">{{ $subject->title() }}</a>
            <span class="font-mono text-2xs text-muted-foreground">{{ $subject->code }}</span>
          @else
            <span class="text-muted-foreground">—</span>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
