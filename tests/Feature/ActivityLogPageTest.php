<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Activity\Table;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_admins_see_the_log_and_others_do_not(): void
    {
        $admin = $this->user(UserRole::Admin, 'boss');
        $this->seed(\Database\Seeders\LocationSeeder::class);
        $property = Property::factory()->create(['name' => 'Old Name', 'district_id' => \App\Models\District::query()->value('id')]);
        $this->actingAs($admin)->put("/backend/properties/{$property->id}", [...\Illuminate\Support\Arr::only($property->attributesToArray(), ['type', 'status', 'district_id', 'bedrooms', 'bathrooms', 'rent_price']), 'name' => 'Kata Villa', 'is_rent' => '1']);

        $html = $this->actingAs($admin)->get('/backend/activity')
            ->assertOk()
            ->assertSee(__('activity.actions.property-update'))
            ->assertSee('Kata Villa')
            ->assertSee('Old Name')
            ->assertSee(__('Activity log'))
            ->getContent();
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));

        $this->actingAs($this->user(UserRole::Manager, 'mgr'))->get('/backend/activity')->assertForbidden();
        $this->actingAs($this->user(UserRole::Agent, 'shop'))->get('/backend/dashboard')->assertDontSee(__('Activity log'));
    }

    public function test_search_by_user_and_filter_by_action(): void
    {
        $admin = $this->user(UserRole::Admin, 'boss');
        $mai = $this->user(UserRole::Agent, 'mai', 'secret-pass');
        $this->post('/backend/login', ['login' => 'mai', 'password' => 'secret-pass']);
        $this->post('/backend/logout');
        $this->post('/backend/login', ['login' => 'nobody', 'password' => 'x']);

        $codes = fn ($component) => $component->viewData('activities')->pluck('description')->all();
        $table = Livewire::actingAs($admin)->test(Table::class);

        $this->assertSame(['login-failed', 'logout', 'login'], $codes($table));
        $this->assertSame(['logout', 'login'], $codes($table->set('q', 'mai')));
        $this->assertSame(['login'], $codes($table->set('action', 'login')));
        $this->assertTrue($table->viewData('activities')->first()->causer->is($mai));
    }

    /**
     * A user with one role
     */
    private function user(UserRole $role, string $username, string $password = 'password'): User
    {
        $user = User::factory()->create(['username' => $username, 'password' => $password]);
        $user->assignRole($role->value);

        return $user;
    }
}
