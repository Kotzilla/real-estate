<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Database Seeder
 *
 * Model events stay on: properties and contracts get their codes and commission from them.
 *
 * @package Database\Seeders
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database
     *
     * @return void
     */
    public function run(): void
    {
        // Roles and permissions first: the dev admin below picks up settings.manage through the admin role
        $this->call([PermissionSeeder::class, LocationSeeder::class]);

        // Dev sign-in for the back office: admin / letmein. Change the password before any real data goes live.
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => 'letmein', // hashed by the User model's cast
        ])->assignRole('admin');

        // A few listings, contacts and contracts to click through (not in production)
        if (! app()->isProduction()) {
            $this->call(DemoSeeder::class);
        }
    }
}
