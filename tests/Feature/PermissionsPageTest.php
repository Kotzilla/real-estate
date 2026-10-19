<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Permissions\Matrix;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_only_users_with_settings_manage_open_it(): void
    {
        $this->actingAs($this->user(UserRole::Admin))->get('/backend/permissions')
            ->assertOk()
            ->assertSee('Add and edit contracts')
            ->assertDontSee('permissions.names');
        $this->actingAs($this->user(UserRole::Manager))->get('/backend/permissions')->assertForbidden();
        $this->actingAs($this->user(UserRole::Agent))->get('/backend/permissions')->assertForbidden();
    }

    public function test_a_switch_takes_and_gives_a_permission_and_it_applies_at_once(): void
    {
        $agent = $this->user(UserRole::Agent);
        $this->actingAs($agent)->get('/backend/contracts/create')->assertOk();

        Livewire::actingAs($this->user(UserRole::Admin))->test(Matrix::class)
            ->call('toggle', 'agent', 'contracts.manage')
            ->assertDispatched('toast');
        $this->actingAs($agent->fresh())->get('/backend/contracts/create')->assertForbidden();

        Livewire::actingAs($this->user(UserRole::Admin))->test(Matrix::class)->call('toggle', 'agent', 'contracts.manage');
        $this->assertTrue(Role::findByName('agent')->hasPermissionTo('contracts.manage'));
    }

    public function test_the_admin_column_is_locked_and_others_cannot_toggle(): void
    {
        Livewire::actingAs($this->user(UserRole::Admin))->test(Matrix::class)->call('toggle', 'admin', 'settings.manage');
        $this->assertTrue(Role::findByName('admin')->hasPermissionTo('settings.manage'));

        // without settings.manage the matrix doesn't even render, so there is nothing to toggle
        Livewire::actingAs($this->user(UserRole::Manager))->test(Matrix::class)->assertForbidden();
        $this->assertFalse(Role::findByName('manager')->hasPermissionTo('settings.manage'));
    }

    public function test_seeding_again_keeps_changes_made_on_the_page(): void
    {
        Role::findByName('agent')->revokePermissionTo('people.manage');
        Role::findByName('manager')->givePermissionTo('settings.manage');

        $this->seed(PermissionSeeder::class);

        $this->assertFalse(Role::findByName('agent')->hasPermissionTo('people.manage'));
        $this->assertTrue(Role::findByName('manager')->hasPermissionTo('settings.manage'));
        $this->assertSame(count(PermissionSeeder::DEFAULTS), Role::findByName('admin')->permissions()->count());
    }

    public function test_every_permission_has_a_label_and_hint_in_both_languages(): void
    {
        foreach (['en', 'th'] as $locale) {
            foreach (array_keys(PermissionSeeder::DEFAULTS) as $permission) {
                foreach (['label', 'hint'] as $part) {
                    $this->assertTrue(Lang::has("permissions.names.$permission.$part", $locale), "$locale: $permission $part");
                }
                $this->assertTrue(Lang::has('permissions.modules.'.strtok($permission, '.').'.label', $locale), "$locale: module of $permission");
            }
        }
    }

    /**
     * A signed-in user with one role
     */
    private function user(UserRole $role): User
    {
        $user = User::factory()->create(['username' => $role->value.uniqid()]);
        $user->assignRole($role->value);

        return $user;
    }
}
