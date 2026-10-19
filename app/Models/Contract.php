<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Enums\PropertyStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

/**
 * Contract Model
 *
 * A lease or a sale of one property to one customer, printed in English and Thai (ContractController::print). The
 * owner is the property's. The commission is worked out on every save; signing (active) marks the property rented or
 * sold, and a lease that ends or a deal that is cancelled puts it back on the market.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Models
 */
class Contract extends Model
{
    use HasFactory, SoftDeletes;

    // Lease terms printed in both languages, kept here so the English and Thai copies can't drift apart (findnest)
    public const LATE_FEE_PER_DAY = 500;

    public const LATE_GRACE_DAYS = 5;

    public const DEPOSIT_REFUND_DAYS = 30;

    public const RENEWAL_NOTICE_DAYS = 30;

    public const INSPECTION_NOTICE_HOURS = 48;

    public const SMALL_REPAIR_CAP = 500;

    public const MOVE_OUT_DAYS = 7;

    public const CLEANING_FEE_GENERAL = 1000;

    public const CLEANING_FEE_BEDSHEET = 350;

    public const CLEANING_FEE_AIRCON = 500;

    // Sale terms: the buyer may walk away within this many days after the transfer date is missed
    public const SALE_DEFAULT_DAYS = 15;

    // The commission a new contract starts with: one month's rent on a year's lease, 3% of a sale
    public const DEFAULT_COMMISSION = ['rent' => 8.33, 'sale' => 3];

    // the column defaults, so a new model has them before it is saved (calculate / syncPropertyStatus read them)
    protected $attributes = ['status' => 'draft', 'deposit' => 0, 'advance' => 0, 'commission_percent' => 0];

    protected $fillable = [
        'property_id', 'customer_id', 'user_id', 'witness_id', 'code', 'type', 'status', 'months', 'price', 'deposit',
        'advance', 'payment_day', 'commission_percent', 'terms', 'remark', 'signed_on', 'start_on', 'end_on',
    ];

    /**
     * The attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ContractType::class,
            'status' => ContractStatus::class,
            'price' => 'decimal:2',
            'deposit' => 'decimal:2',
            'advance' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission' => 'decimal:2',
            'signed_on' => 'date',
            'start_on' => 'date',
            'end_on' => 'date',
        ];
    }

    /**
     * Give a new contract its code, work the commission out on every save and keep the property's status in step
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Contract $contract) {
            $contract->code ??= static::nextCode((int) $contract->signed_on?->year ?: (int) now()->year);
        });
        static::saving(fn (Contract $contract) => $contract->calculate());
        static::saved(fn (Contract $contract) => $contract->syncPropertyStatus());
    }

    /**
     * The next free code of the year: RE-2026-0001
     *
     * @param  int  $year
     * @return string
     */
    public static function nextCode(int $year): string
    {
        // ponytail: count+1 per year, fine for one back office; two saves in the same instant would clash on the unique index
        $prefix = 'RE-'.$year.'-';
        $last = static::withTrashed()->where('code', 'like', $prefix.'%')->max('code');
        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * The property let or sold
     *
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class)->withTrashed();
    }

    /**
     * The tenant / buyer
     *
     * @return BelongsTo<Person, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'customer_id')->withTrashed();
    }

    /**
     * The agent who closed it
     *
     * @return BelongsTo<User, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * Who signs as witness
     *
     * @return BelongsTo<User, $this>
     */
    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_id')->withTrashed();
    }

    /**
     * Whether it is a lease
     *
     * @return bool
     */
    public function isRent(): bool
    {
        return $this->type === ContractType::Rent;
    }

    /**
     * The whole deal's value: rent × months for a lease, the price for a sale
     *
     * @return float
     */
    public function value(): float
    {
        return $this->isRent() ? (float) $this->price * (int) $this->months : (float) $this->price;
    }

    /**
     * What the customer pays on signing: deposit (+ advance rent for a lease)
     *
     * @return float
     */
    public function dueOnSigning(): float
    {
        return (float) $this->deposit + ($this->isRent() ? (float) $this->advance : 0);
    }

    /**
     * Work the commission out from the deal's value
     *
     * @return void
     */
    public function calculate(): void
    {
        if (! $this->isRent()) {
            $this->months = null;
            $this->advance = 0;
            $this->payment_day = null;
        }
        $this->commission = round($this->value() * (float) $this->commission_percent / 100, 2);
    }

    /**
     * Put the property's status in step: signed → rented / sold, ended or cancelled → back on the market
     *
     * @return void
     */
    public function syncPropertyStatus(): void
    {
        if (! $this->wasChanged('status') && ! $this->wasRecentlyCreated) {
            return;
        }

        $status = match ($this->status) {
            ContractStatus::Active => $this->isRent() ? PropertyStatus::Rented : PropertyStatus::Sold,
            ContractStatus::Completed => $this->isRent() ? PropertyStatus::Available : PropertyStatus::Sold,
            ContractStatus::Cancelled => PropertyStatus::Available,
            ContractStatus::Draft => null,
        };

        // another signed contract still holds the property: leave it as that one set it
        $heldElsewhere = $status === PropertyStatus::Available && static::query()
            ->where('property_id', $this->property_id)->whereKeyNot($this->id)
            ->where('status', ContractStatus::Active->value)->exists();

        if ($status && ! $heldElsewhere) {
            $this->property?->update(['status' => $status]);
        }
    }
    /**
     * An amount as the printed copy writes it out: "One Hundred Twenty-Five Thousand Baht Only" / "หนึ่งแสนสองหมื่นห้าพันบาทถ้วน"
     *
     * @param  float|string|null  $amount
     * @param  string  $lang
     * @return string
     */
    public static function inWords(float|string|null $amount, string $lang): string
    {
        // ponytail: whole baht only, satang are dropped; contract amounts are round figures
        $words = Number::spell((int) round((float) $amount), locale: $lang);

        return $lang === 'th'
            ? str_replace("\u{200B}", '', $words).'บาทถ้วน'
            : Str::title($words).' Baht Only';
    }

    /**
     * A date as the printed copy writes it: "19th October 2026" / "19 ตุลาคม 2569" (Buddhist year)
     *
     * @param  CarbonInterface|null  $date
     * @param  string  $lang
     * @return string
     */
    public static function dateIn(?CarbonInterface $date, string $lang): string
    {
        if (! $date) {
            return '-';
        }

        return $lang === 'th'
            ? $date->locale('th')->translatedFormat('j F').' '.($date->year + 543)
            : $date->locale('en')->format('jS F Y');
    }

}
