<?php

namespace App\Models;

use App\Enums\PersonType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Person Model
 *
 * A contact outside the company: an owner (lessor / seller), a customer (tenant / buyer) or an outside agent. The
 * contracts print the English name and address on the English copy and the Thai ones (falling back to English) on
 * the Thai copy.
 *
 * @package App\Models
 */
class Person extends Model
{
    use HasFactory, SoftDeletes;

    // the column default, so a new model has it before it is saved
    protected $attributes = ['type' => 'customer'];

    protected $fillable = [
        'type', 'first_name', 'last_name', 'first_name_th', 'last_name_th', 'nickname', 'nationality', 'id_card_no',
        'passport_no', 'phone', 'email', 'line_id', 'whatsapp', 'address', 'address_th', 'bank_name',
        'bank_account_name', 'bank_account_no', 'remark', 'birth_on',
    ];

    /**
     * The attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PersonType::class,
            'birth_on' => 'date',
        ];
    }

    /**
     * The listings this person owns
     *
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    /**
     * The contracts this person signed as tenant / buyer
     *
     * @return HasMany<Contract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'customer_id');
    }

    /**
     * Full name in English, or in Thai when asked and given (falls back to English)
     *
     * @param  string|null  $locale
     * @return string
     */
    public function fullName(?string $locale = null): string
    {
        $thai = ($locale ?? app()->getLocale()) === 'th' && $this->first_name_th;

        return trim($thai ? $this->first_name_th.' '.$this->last_name_th : $this->first_name.' '.$this->last_name);
    }

    /**
     * Address in English, or in Thai when asked and given (falls back to English)
     *
     * @param  string|null  $locale
     * @return string|null
     */
    public function addressIn(?string $locale = null): ?string
    {
        return ($locale ?? app()->getLocale()) === 'th' && $this->address_th ? $this->address_th : $this->address;
    }

    /**
     * The ID the contract prints: Thai national ID, else passport
     *
     * @return string|null
     */
    public function documentNo(): ?string
    {
        return $this->id_card_no ?: $this->passport_no;
    }
}
