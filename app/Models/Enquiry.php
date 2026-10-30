<?php

namespace App\Models;

use App\Enums\EnquiryStatus;
use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Enquiry Model
 *
 * A visitor asking about something on the public site (a listing's "arrange a viewing" form): a name, a phone number
 * and an optional message. Staff move it open → in progress → closed and keep their follow-up notes as comments.
 *
 * @package App\Models
 */
class Enquiry extends Model
{
    use HasComments;

    // the column default, so a new model has it before it is saved
    protected $attributes = ['status' => 'open'];

    protected $fillable = ['name', 'phone', 'message', 'status'];

    /**
     * The attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['status' => EnquiryStatus::class];
    }

    /**
     * What it is about: a property (null once that is deleted)
     *
     * @return MorphTo<Model, $this>
     */
    public function enquirable(): MorphTo
    {
        return $this->morphTo();
    }
}
