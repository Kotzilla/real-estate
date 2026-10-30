<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Traits\HasPhotos;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;

/**
 * Property Model
 *
 * A listing for sale, for rent or both, with its owner, the agent in charge, location, prices and photos. Facilities
 * (pool, sea view…) are spatie tags of type "facility". The code (e.g. PHU-0012) is made on create from the province.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Models
 */
class Property extends Model
{
    use HasFactory, HasPhotos, HasTags, SoftDeletes;

    public const FACILITY = 'facility';

    // The island the site covers: Ko Pha-ngan district (Surat Thani). The home page shows it; new listings start there.
    public const HOME_DISTRICT = '8405';

    // A facility's icon on the public page: the first word its name contains (facilities are free tags), else a tick
    public const FACILITY_ICONS = [
        'pool' => 'drop', 'beach' => 'ocean', 'sunset' => 'sun', 'sea' => 'ocean', 'bay' => 'ocean', 'lake' => 'ocean',
        'stream' => 'ocean', 'jungle' => 'tree', 'mountain' => 'tree', 'garden' => 'tree', 'coconut' => 'tree',
        'view' => 'eye', 'wi-fi' => 'wifi', 'wifi' => 'wifi', 'internet' => 'wifi', 'air' => 'cloud', 'parking' => 'car',
        'garage' => 'car', 'road' => 'route', 'market' => 'shop', 'shop' => 'shop', 'gym' => 'heart',
        'electric' => 'flash-circle', 'chanote' => 'document', 'title' => 'document', 'licen' => 'document',
        'staff' => 'people', 'security' => 'security-user',
    ];

    // the column defaults, so a new model has them before it is saved
    protected $attributes = ['status' => 'available', 'bedrooms' => 0, 'bathrooms' => 0];

    protected $fillable = [
        'owner_id', 'user_id', 'province_id', 'district_id', 'code', 'type', 'status', 'name', 'name_th',
        'description', 'description_th', 'address', 'address_th', 'location', 'bedrooms', 'bathrooms', 'floor', 'area',
        'land_area', 'sale_price', 'rent_price', 'lat', 'lng', 'remark', 'is_sale', 'is_rent', 'is_furnished', 'is_pet',
        'is_published',
    ];

    /**
     * The attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'sale_price' => 'decimal:2',
            'rent_price' => 'decimal:2',
            'area' => 'decimal:2',
            'land_area' => 'decimal:2',
            'is_sale' => 'boolean',
            'is_rent' => 'boolean',
            'is_furnished' => 'boolean',
            'is_pet' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Give a new listing its code
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Property $property) {
            $property->code ??= static::nextCode($property->province_id);
        });
    }

    /**
     * The next free code: the province's first three letters (PHU, KRA…) and a running number
     *
     * @param  int|null  $provinceId
     * @return string
     */
    public static function nextCode(?int $provinceId): string
    {
        // ponytail: max(id)+1, fine for one back office; two saves in the same instant would clash on the unique index
        $prefix = Str::upper(Str::substr(Province::query()->whereKey($provinceId)->value('name') ?? 'PRP', 0, 3));
        $next = (int) static::withTrashed()->max('id') + 1;

        return $prefix.'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * The lessor / seller
     *
     * @return BelongsTo<Person, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'owner_id')->withTrashed();
    }

    /**
     * The agent in charge
     *
     * @return BelongsTo<User, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

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
     * The district it is in
     *
     * @return BelongsTo<District, $this>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Its leases and sales, newest first
     *
     * @return HasMany<Contract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->latest('signed_on')->latest('id');
    }

    /**
     * Visitors' requests to see it, from its public page
     *
     * @return MorphMany<Enquiry, $this>
     */
    public function enquiries(): MorphMany
    {
        return $this->morphMany(Enquiry::class, 'enquirable');
    }

    /**
     * The listings the public site shows: published, on the island, still on the market
     *
     * @param  Builder<Property>  $query
     * @return void
     */
    public function scopeListed(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereIn('status', [PropertyStatus::Available->value, PropertyStatus::Reserved->value])
            ->whereHas('district', fn ($district) => $district->where('code', self::HOME_DISTRICT));
    }

    /**
     * The words in its public URL: the English name and the area unless the name has it, e.g. sea-view-villa-srithanu
     *
     * @return string
     */
    public function slug(): string
    {
        $name = Str::slug($this->name);
        $area = Str::slug((string) $this->location);

        return trim($area === '' || str_contains($name, $area) ? $name : $name.'-'.$area, '-') ?: 'property';
    }

    /**
     * Its public page: /property/PHU-0001/sea-view-villa-srithanu
     *
     * @return string
     */
    public function publicUrl(): string
    {
        return route('listing', [$this->code, $this->slug()]);
    }

    /**
     * The Keenicon for a facility, e.g. "Private pool" => drop
     *
     * @param  string  $facility
     * @return string
     */
    public static function facilityIcon(string $facility): string
    {
        $name = Str::lower($facility);
        foreach (self::FACILITY_ICONS as $word => $icon) {
            if (str_contains($name, $word)) {
                return $icon;
            }
        }

        return 'check';
    }

    /**
     * Name in the current language (falls back to English)
     *
     * @param  string|null  $locale
     * @return string
     */
    public function title(?string $locale = null): string
    {
        return ($locale ?? app()->getLocale()) === 'th' && $this->name_th ? $this->name_th : $this->name;
    }

    /**
     * Address in the given language (falls back to English), with the district and province
     *
     * @param  string|null  $locale
     * @return string
     */
    public function fullAddress(?string $locale = null): string
    {
        $thai = ($locale ?? app()->getLocale()) === 'th';
        $address = $thai && $this->address_th ? $this->address_th : $this->address;

        return collect([
            $address,
            $thai ? $this->district?->name_th : $this->district?->name,
            $thai ? $this->province?->name_th : $this->province?->name,
        ])->filter()->join(', ');
    }
}
