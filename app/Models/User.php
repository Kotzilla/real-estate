<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * Back-office staff: admins, managers and the agents who list properties and sign contracts (a contract's agent and
 * witness). The role is spatie/permission's, not a column: App\Enums\UserRole lists the roles the Users page offers,
 * App\Policies\UserPolicy holds the permission checks. Archived accounts are soft-deleted, so old contracts still name them.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Models
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    // a session active within this many minutes counts as online (an open back-office tab polls the page)
    public const ONLINE_MINUTES = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Whether the user is an administrator (the guard that keeps the last admin in place)
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin->value);
    }

    /**
     * The initials the avatar shows: the first letter of the first two words of their name
     *
     * @return string
     */
    public function initials(): string
    {
        return Str::upper(Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word) => Str::substr($word, 0, 1))
            ->join(''));
    }

    /**
     * What the Users list shows under the name: the email, or the username when there is no email yet
     *
     * @return string
     */
    public function signInAs(): string
    {
        return (string) ($this->email ?: $this->username);
    }

    /**
     * When each of these users was last seen, keyed by user id (a session is a row in `sessions`, which Laravel
     * stamps with last_activity on every request, so an open tab counts)
     *
     * @param  array<int, int>  $ids
     * @return array<int, Carbon>
     */
    public static function lastSeen(array $ids): array
    {
        return DB::table('sessions')->whereIn('user_id', $ids)->groupBy('user_id')
            ->selectRaw('user_id, max(last_activity) as seen')->pluck('seen', 'user_id')
            ->map(fn ($seen) => Carbon::createFromTimestamp((int) $seen, config('app.timezone')))->all();
    }
}
