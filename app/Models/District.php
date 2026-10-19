<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * District Model
 *
 * An amphoe of a southern province, e.g. Kathu in Phuket (seeded, not edited here).
 *
 * @package App\Models
 */
class District extends Model
{
    protected $fillable = ['province_id', 'code', 'name', 'name_th'];

    /**
     * The province it is in
     *
     * @return BelongsTo<Province, $this>
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * The name in the current language
     *
     * @return string
     */
    public function label(): string
    {
        return app()->getLocale() === 'th' ? $this->name_th : $this->name;
    }
}
