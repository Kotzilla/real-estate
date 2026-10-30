<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Has Comments
 *
 * Staff notes on a model, shown and added by the `comments` Livewire component
 * (`@livewire('comments', ['model' => $enquiry])`). The model needs a morph map alias (AppServiceProvider) and a
 * policy: `view` to read the notes, `update` to add one.
 *
 * @package App\Traits
 */
trait HasComments
{
    /**
     * Its notes, newest first
     *
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest()->latest('id');
    }
}
