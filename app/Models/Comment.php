<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Comment Model
 *
 * A staff note on a record (App\Traits\HasComments), e.g. "called, viewing on Saturday" on an enquiry.
 *
 * @package App\Models
 */
class Comment extends Model
{
    protected $fillable = ['user_id', 'body'];

    /**
     * The record it is on
     *
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Who wrote it
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
