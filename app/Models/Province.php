<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Province Model
 *
 * One of the 14 southern provinces a property can be in (seeded, not edited here).
 *
 * @package App\Models
 */
class Province extends Model
{
    protected $fillable = ['code', 'name', 'name_th'];

    /**
     * The province's districts
     *
     * @return HasMany<District, $this>
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class)->orderBy('name');
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
