{{-- Pagination in Metronic (KTUI) buttons for Livewire tables: << < 1 2 3 ... 10 > >> (phones: only << < > >>, .page-numbers in app.css) --}}
@if ($paginator->hasPages())
  @php
    $pageName = $paginator->getPageName();
    $elements = $elements ?? ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator ? \Illuminate\Pagination\UrlWindow::make($paginator) : []);
    if (isset($elements['first'])) {
      $elements = array_filter([
        $elements['first'],
        is_array($elements['slider']) ? '...' : null,
        $elements['slider'],
        is_array($elements['last']) ? '...' : null,
        $elements['last'],
      ]);
    }
  @endphp
  <nav class="flex items-center gap-1" role="navigation" aria-label="@lang('common.pagination')">
    {{-- First Page (<<) --}}
    <button type="button"
      class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline"
      wire:click="gotoPage(1, '{{ $pageName }}')"
      @disabled($paginator->onFirstPage())
      title="@lang('common.first')"
      aria-label="@lang('common.first')">
      <i class="ki-filled ki-double-left text-xs"></i>
    </button>

    {{-- Previous Page (<) --}}
    <button type="button"
      class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline"
      wire:click="previousPage('{{ $pageName }}')"
      @disabled($paginator->onFirstPage())
      title="@lang('common.previous')"
      aria-label="@lang('common.previous')">
      <i class="ki-filled ki-left text-xs"></i>
    </button>

    {{-- Pagination Elements (page numbers: tablet and up only, they overflow a phone) --}}
    <span class="page-numbers">
      @if (isset($elements) && is_array($elements))
        @foreach ($elements as $element)
          {{-- "Three Dots" Separator --}}
          @if (is_string($element))
            <span class="flex h-7 w-7 items-center justify-center text-xs text-muted-foreground select-none" aria-disabled="true">
              {{ $element }}
            </span>
          @endif

          {{-- Array Of Links --}}
          @if (is_array($element))
            @foreach ($element as $page => $url)
              @if ($page == $paginator->currentPage())
                <button type="button"
                  class="kt-btn kt-btn-sm min-w-7 h-7 p-0 kt-btn-primary font-medium"
                  aria-current="page"
                  wire:key="paginator-{{ $pageName }}-page-{{ $page }}">
                  {{ $page }}
                </button>
              @else
                <button type="button"
                  class="kt-btn kt-btn-sm min-w-7 h-7 p-0 kt-btn-outline"
                  wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                  wire:key="paginator-{{ $pageName }}-page-{{ $page }}"
                  aria-label="@lang('pagination.goto', ['page' => $page])">
                  {{ $page }}
                </button>
              @endif
            @endforeach
          @endif
        @endforeach
      @endif
    </span>

    {{-- Next Page (>) --}}
    <button type="button"
      class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline"
      wire:click="nextPage('{{ $pageName }}')"
      @disabled(! $paginator->hasMorePages())
      title="@lang('common.next')"
      aria-label="@lang('common.next')">
      <i class="ki-filled ki-right text-xs"></i>
    </button>

    {{-- Last Page (>>) --}}
    <button type="button"
      class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline"
      wire:click="gotoPage({{ $paginator->lastPage() }}, '{{ $pageName }}')"
      @disabled(! $paginator->hasMorePages())
      title="@lang('common.last')"
      aria-label="@lang('common.last')">
      <i class="ki-filled ki-double-right text-xs"></i>
    </button>
  </nav>
@endif
