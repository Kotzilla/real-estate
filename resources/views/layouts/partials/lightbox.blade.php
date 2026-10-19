<div x-data="ktLightbox"
     x-show="isOpen"
     x-cloak
     @keydown.escape.window="close()"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] flex flex-col bg-black/90 backdrop-blur-md select-none"
     role="dialog"
     aria-modal="true">

  {{-- Top Bar / Actions --}}
  <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b border-white/10 bg-black/40 shrink-0 z-10">
    {{-- Left: File info --}}
    <div class="flex items-center gap-3 min-w-0 pr-2">
      <div class="flex size-8 items-center justify-center rounded-lg bg-white/10 text-white shrink-0">
        <i class="ki-filled text-base" :class="isPdf ? 'ki-file-sheet' : (isImage ? 'ki-picture' : 'ki-document')"></i>
      </div>
      <div class="min-w-0 flex items-center gap-2">
        <span x-text="name" class="font-medium text-white text-sm truncate max-w-[180px] sm:max-w-xs md:max-w-md" :title="name"></span>
        <span x-show="size" x-text="size" class="text-xs text-white/60 bg-white/10 px-2 py-0.5 rounded shrink-0 hidden sm:inline-block"></span>
      </div>
    </div>

    {{-- Center: Image Controls (Zoom, Rotate) --}}
    <div x-show="isImage" class="flex items-center gap-1 bg-white/10 rounded-lg p-1">
      <button type="button" @click="zoomOut()"
        class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost text-white/80 hover:text-white hover:bg-white/10"
        title="Zoom Out">
        <i class="ki-filled ki-minus text-xs"></i>
      </button>
      <button type="button" @click="resetZoom()"
        class="px-2 py-0.5 text-xs text-white/90 hover:text-white hover:bg-white/10 rounded font-mono"
        title="Reset Zoom">
        <span x-text="Math.round(zoom * 100) + '%'"></span>
      </button>
      <button type="button" @click="zoomIn()"
        class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost text-white/80 hover:text-white hover:bg-white/10"
        title="Zoom In">
        <i class="ki-filled ki-plus text-xs"></i>
      </button>
      <div class="w-px h-3.5 bg-white/20 mx-0.5"></div>
      <button type="button" @click="rotate()"
        class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost text-white/80 hover:text-white hover:bg-white/10"
        title="Rotate">
        <i class="ki-filled ki-arrows-circle text-xs"></i>
      </button>
    </div>

    {{-- Right: Open in new tab, Download, Close --}}
    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
      <a :href="url" target="_blank" rel="noopener noreferrer"
        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-white/80 hover:text-white hover:bg-white/10"
        title="@lang('Open in new tab')">
        <i class="ki-filled ki-arrow-up-right text-base"></i>
      </a>
      <a :href="downloadUrl || url"
        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-white/80 hover:text-white hover:bg-white/10"
        title="@lang('Download')">
        <i class="ki-filled ki-file-down text-base"></i>
      </a>
      <div class="w-px h-4 bg-white/20 mx-1"></div>
      <button type="button" @click="close()"
        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-white/80 hover:text-white hover:bg-white/10"
        title="@lang('Close')">
        <i class="ki-filled ki-cross text-lg"></i>
      </button>
    </div>
  </div>

  {{-- Preview Area --}}
  <div class="relative flex-1 flex items-center justify-center p-3 sm:p-6 overflow-hidden" @wheel="onWheel($event)">
    {{-- Loading Spinner --}}
    <div x-show="isLoading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 pointer-events-none text-white/80 z-20">
      <span class="inline-block size-8 animate-spin rounded-full border-2 border-white/20 border-t-white"></span>
      <span class="text-xs font-medium">@lang('Loading...')</span>
    </div>

    {{-- Image Preview --}}
    <template x-if="isImage && url">
      <div class="relative size-full flex items-center justify-center kt-scrollable-auto" @click.self="close()">
        <img :src="url" :alt="name"
          @load="isLoading = false"
          class="max-h-[85vh] max-w-[90vw] object-contain transition-transform duration-150 rounded shadow-2xl pointer-events-auto select-none"
          :style="`transform: scale(${zoom}) rotate(${rotation}deg);`"
          draggable="false"
        >
      </div>
    </template>

    {{-- PDF Preview --}}
    <template x-if="isPdf && url">
      <div class="size-full max-w-6xl max-h-[88vh] flex items-center justify-center" @click.self="close()">
        <iframe :src="url"
          @load="isLoading = false"
          class="w-full h-full rounded-xl border border-white/10 shadow-2xl bg-white"
          title="PDF Preview">
        </iframe>
      </div>
    </template>

    {{-- Other File Type Fallback --}}
    <template x-if="!isImage && !isPdf && url">
      <div class="flex flex-col items-center justify-center text-center p-8 text-white" @click.self="close()">
        <div class="flex size-20 items-center justify-center rounded-2xl bg-white/10 mb-4">
          <i class="ki-filled ki-document text-4xl text-primary"></i>
        </div>
        <p class="text-base font-medium mb-1" x-text="name"></p>
        <p class="text-xs text-white/60 mb-5" x-text="size"></p>
        <a :href="downloadUrl || url" class="kt-btn kt-btn-sm kt-btn-primary gap-2">
          <i class="ki-filled ki-file-down"></i>
          <span>@lang('Download')</span>
        </a>
      </div>
    </template>
  </div>
</div>
