<div class="kt-card">
  <div class="kt-card-header">
    <h3 class="kt-card-title flex items-center gap-2"><i class="ki-filled ki-messages text-lg text-primary"></i> @lang('comments.title')</h3>
  </div>
  <div class="kt-card-content flex flex-col gap-5">
    @can('update', $model)
      <form wire:submit="add" class="flex flex-col gap-2.5">
        {{-- keyed on the count, so after a note is added the box comes back empty and short --}}
        <div wire:key="comment-box-{{ $comments->count() }}">
          <x-field type="textarea" name="body" :label="__('comments.body')" wire:model="body" rows="2" maxlength="2000" :placeholder="__('comments.placeholder')" required />
        </div>
        <div>
          <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary gap-1.5" wire:loading.attr="disabled">
            <i class="ki-filled ki-plus"></i> @lang('comments.add')
          </button>
        </div>
      </form>
    @endcan

    <div class="flex flex-col gap-4">
      @forelse ($comments as $comment)
        <div wire:key="comment-{{ $comment->id }}" class="border-s-2 border-border ps-3">
          <div class="text-2xs text-muted-foreground">
            <span class="font-medium text-foreground">{{ $comment->user?->name ?? '—' }}</span>
            · <span title="{{ $comment->created_at->format('d/m/Y H:i') }}">{{ $comment->created_at->diffForHumans() }}</span>
          </div>
          <div class="mt-1 whitespace-pre-line text-sm">{{ $comment->body }}</div>
        </div>
      @empty
        <p class="text-sm text-muted-foreground">@lang('comments.empty')</p>
      @endforelse
    </div>
  </div>
</div>
