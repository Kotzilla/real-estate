<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Contract;
use App\Models\Enquiry;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use App\Policies\ActivityPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * App Service Provider
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services
     *
     * @return void
     */
    public function boot(): void
    {
        // Photos, tags and the activity log store these aliases, never class names, so renaming a class never breaks stored rows
        Relation::enforceMorphMap([
            'property' => Property::class,
            'person' => Person::class,
            'contract' => Contract::class,
            'enquiry' => Enquiry::class,
            'comment' => Comment::class,
            'user' => User::class,
            'role' => \Spatie\Permission\Models\Role::class,
        ]);

        // The package's model, so not found by the App\Policies naming convention
        Gate::policy(Activity::class, ActivityPolicy::class);

        // Sign-in attempts (`throttle:login`): 5 a minute per account and address, against password guessing
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('login')).'|'.$request->ip()));
    }
}
