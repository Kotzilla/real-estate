<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and Permissions Seeder
 *
 * The roles come from App\Enums\UserRole so the Users page's select and the seeded roles can never drift apart. Each
 * permission in DEFAULTS is created once with its default roles; after that the Permissions page owns who has it, so
 * running the seeder again (deploys) adds new permissions without resetting edited ones. Admins always hold all.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package Database\Seeders
 */
class PermissionSeeder extends Seeder
{
    /**
     * Every permission and the roles that get it by default (admins always hold them all). Changed afterwards on the
     * Permissions page (App\Livewire\Permissions\Matrix); this list only seeds a permission the first time it exists.
     */
    public const DEFAULTS = [
        'settings.manage' => [], // the Users and Permissions pages
        // App\Policies\PropertyPolicy: listings, their photos and facilities
        'properties.view' => ['manager', 'agent'],
        'properties.manage' => ['manager', 'agent'],
        // App\Policies\PersonPolicy: owners, customers and outside agents
        'people.view' => ['manager', 'agent'],
        'people.manage' => ['manager', 'agent'],
        // App\Policies\ContractPolicy: leases and sales, and printing them
        'contracts.view' => ['manager', 'agent'],
        'contracts.manage' => ['manager', 'agent'],
        // App\Policies\ActivityPolicy: who did what in the back office (sign-ins and changes, with IP and old values)
        'activity.view' => [],
    ];

    /**
     * Seed the roles and the permissions; never undoes a change made on the Permissions page
     *
     * @return void
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        foreach (self::DEFAULTS as $name => $roles) {
            $permission = Permission::findOrCreate($name);
            if ($permission->wasRecentlyCreated) {
                $permission->syncRoles($roles);
            }
        }

        Role::findByName(UserRole::Admin->value)->syncPermissions(array_keys(self::DEFAULTS));
    }
}
