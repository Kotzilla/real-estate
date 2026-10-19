{{--
  The photos card of an add / edit form: the model's photos (tick to remove, pick the cover) and a dropzone for new
  ones, posted as photos[], remove_photos[] and cover_photo_id (App\Traits\HasPhotos on save). From store's product form.
  <x-photo-card :photos="$property->photos" />
--}}
@props(['photos'])
@php
  $removed = array_map('intval', old('remove_photos', []));
  $coverId = (int) old('cover_photo_id', $photos->firstWhere('is_cover', true)?->id);
@endphp
<div class="kt-card">
  <div class="kt-card-header">
    <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-picture text-lg text-primary"></i> @lang('photos.title')</h3>
  </div>
  <div class="kt-card-content flex flex-col gap-4">
    @if ($photos->isNotEmpty())
      <div class="grid grid-cols-2 gap-3">
        @foreach ($photos as $photo)
          <div class="flex flex-col gap-1.5 rounded-lg border border-border p-2">
            <button type="button" class="overflow-hidden rounded-md" onclick="openLightbox({ url: @js($photo->url), name: @js($photo->name), isImage: true })">
              <img src="{{ $photo->src('sm') }}" alt="{{ $photo->alt }}" loading="lazy" class="h-28 w-full object-cover">
            </button>
            <label class="flex items-center gap-1.5 text-xs text-foreground">
              <input type="radio" class="kt-radio kt-radio-sm" name="cover_photo_id" value="{{ $photo->id }}" @checked($coverId === $photo->id)>
              @lang('photos.cover')
            </label>
            <label class="flex items-center gap-1.5 text-xs text-destructive">
              <input type="checkbox" class="kt-checkbox kt-checkbox-sm" name="remove_photos[]" value="{{ $photo->id }}" @checked(in_array($photo->id, $removed, true))>
              @lang('photos.remove')
            </label>
          </div>
        @endforeach
      </div>
    @endif

    <div x-data="{
      items: [],
      formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
      },
      handleFiles(files) {
        Array.from(files || []).forEach((file) => this.items.push({ file, name: file.name, sizeText: this.formatSize(file.size), url: URL.createObjectURL(file) }));
        this.syncInput();
      },
      remove(index) {
        URL.revokeObjectURL(this.items[index]?.url);
        this.items.splice(index, 1);
        this.syncInput();
      },
      syncInput() {
        const dt = new DataTransfer();
        this.items.forEach((item) => dt.items.add(item.file));
        this.$refs.fileInput.files = dt.files;
      }
    }">
      <label for="f-photos" class="mb-1.5 block text-sm font-semibold text-foreground">@lang('photos.add')</label>
      <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border p-6 text-center transition-colors hover:border-primary/60 hover:bg-muted/30">
        <input type="file" id="f-photos" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" x-ref="fileInput"
          @change="handleFiles($event.target.files)" class="absolute inset-0 size-full cursor-pointer opacity-0"
          @error('photos') aria-invalid="true" @enderror @error('photos.*') aria-invalid="true" @enderror>
        <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
          <i class="ki-filled ki-file-up text-2xl"></i>
        </div>
        <p class="text-sm font-medium text-foreground">@lang('common.photo_dropzone_text')</p>
        <p class="mt-1 text-xs text-muted-foreground">@lang('common.photo_hint')</p>
      </div>
      @error('photos') <span class="mt-1.5 block text-xs text-destructive">{{ $message }}</span> @enderror
      @error('photos.*') <span class="mt-1.5 block text-xs text-destructive">{{ $message }}</span> @enderror

      <div class="mt-3 flex flex-col gap-2" x-show="items.length" x-cloak>
        <template x-for="(item, index) in items" :key="index">
          <div class="flex items-center gap-3 rounded-lg border border-border bg-card p-2.5">
            <img :src="item.url" alt="" class="size-12 shrink-0 rounded-lg border border-border object-cover">
            <div class="min-w-0 flex-1 text-start">
              <p class="truncate text-sm font-medium text-foreground" x-text="item.name"></p>
              <p class="text-xs text-muted-foreground" x-text="item.sizeText"></p>
            </div>
            <button type="button" @click="remove(index)" class="kt-btn kt-btn-xs kt-btn-dim kt-btn-icon relative z-10 shrink-0 text-muted-foreground hover:text-destructive" title="@lang('common.remove_file')">
              <i class="ki-filled ki-cross text-xs"></i>
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>
