<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PropertyController;
use App\Models\Contract;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;

// Public site: the landing page, the map search, a listing's page, the privacy policy
Route::get('/', HomeController::class)->name('home');
Route::get('/properties', [ListingController::class, 'index'])->name('listings');
Route::get('/property/{code}/{slug?}', [ListingController::class, 'show'])->where('code', '[A-Z]{3}-[0-9]+')->name('listing');
Route::view('/privacy', 'privacy')->name('privacy');
Route::redirect('/login', '/backend/login');
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

// Back office: everything staff use lives under /backend (routes named backend.*), so a public site can take the root
Route::prefix('backend')->group(function () {
    // Sign-in (signed-in users are sent on to the dashboard). Laravel's own names (login, password.reset,
    // password.update): the auth middleware and the reset email build them.
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');

        // Where the invitation link from the Users page lands
        Route::get('/set-password/{token}', [SetPasswordController::class, 'create'])->name('password.reset');
        Route::post('/set-password', [SetPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.update');
    });

    // Signed-in staff only (guests are sent to the sign-in page); names are backend.*
    Route::middleware('auth')->name('backend.')->group(function () {
        Route::redirect('/', '/backend/dashboard');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

        // Listings (App\Policies\PropertyPolicy)
        Route::get('/properties', [PropertyController::class, 'index'])->can('viewAny', Property::class)->name('properties.index');
        Route::get('/properties/create', [PropertyController::class, 'create'])->can('create', Property::class)->name('properties.create');
        Route::post('/properties', [PropertyController::class, 'store'])->can('create', Property::class)->name('properties.store');
        Route::get('/properties/{property}', [PropertyController::class, 'show'])->can('view', 'property')->name('properties.show');
        Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->can('update', 'property')->name('properties.edit');
        Route::put('/properties/{property}', [PropertyController::class, 'update'])->can('update', 'property')->name('properties.update');

        // Owners, customers, outside agents (App\Policies\PersonPolicy)
        Route::get('/people', [PersonController::class, 'index'])->can('viewAny', Person::class)->name('people.index');
        Route::get('/people/create', [PersonController::class, 'create'])->can('create', Person::class)->name('people.create');
        Route::post('/people', [PersonController::class, 'store'])->can('create', Person::class)->name('people.store');
        Route::get('/people/{person}', [PersonController::class, 'show'])->can('view', 'person')->name('people.show');
        Route::get('/people/{person}/edit', [PersonController::class, 'edit'])->can('update', 'person')->name('people.edit');
        Route::put('/people/{person}', [PersonController::class, 'update'])->can('update', 'person')->name('people.update');

        // Leases and sales (App\Policies\ContractPolicy), printed in English or Thai
        Route::get('/contracts', [ContractController::class, 'index'])->can('viewAny', Contract::class)->name('contracts.index');
        Route::get('/contracts/create', [ContractController::class, 'create'])->can('create', Contract::class)->name('contracts.create');
        Route::post('/contracts', [ContractController::class, 'store'])->can('create', Contract::class)->name('contracts.store');
        Route::get('/contracts/{contract}', [ContractController::class, 'show'])->can('view', 'contract')->name('contracts.show');
        Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->can('update', 'contract')->name('contracts.edit');
        Route::put('/contracts/{contract}', [ContractController::class, 'update'])->can('update', 'contract')->name('contracts.update');
        Route::get('/contracts/{contract}/print/{lang}', [ContractController::class, 'print'])->can('view', 'contract')
            ->whereIn('lang', ['en', 'th'])->name('contracts.print');

        // Staff accounts: App\Policies\UserPolicy (settings.manage), checked again in the component
        Route::view('/users', 'users.index')->can('viewAny', User::class)->name('users.index');
        Route::view('/users/archived', 'users.index')->can('viewAny', User::class)->name('users.archived');
        Route::view('/permissions', 'permissions.index')->can('viewAny', User::class)->name('permissions.index');

        // Activity log (App\Livewire\Activity\Table)
        Route::view('/activity', 'activity.index')->can('viewAny', Activity::class)->name('activity.index');
    });
});

// Last: an unknown URL still goes through the web middleware, so its 404 page is in the visitor's language
Route::fallback(fn () => abort(404));
