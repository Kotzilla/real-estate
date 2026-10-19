<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_dashboard_renders_the_metronic_layout(): void
    {
        $this->actingAs($this->admin())
            ->get('/backend/dashboard')
            ->assertOk()
            ->assertSee('id="sidebar"', false)
            ->assertSee('id="header"', false)
            ->assertSee('Dashboard')
            ->assertSee('Properties')
            ->assertSee('Contracts')
            ->assertSee('Contacts')
            ->assertSee('Settings')
            ->assertSee('Users')
            ->assertSee('Active')
            ->assertSee('Archive');
    }

    public function test_agents_do_not_see_settings_in_sidebar(): void
    {
        $agent = User::factory()->create();
        $agent->assignRole(UserRole::Agent->value);

        $this->actingAs($agent)
            ->get('/backend/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Properties')
            ->assertSee('Contracts')
            ->assertSee('Contacts')
            ->assertDontSee('Settings')
            ->assertDontSee('Users');
    }

    public function test_language_switch_sets_the_locale(): void
    {
        $this->actingAs($this->admin());

        $this->from('/backend/dashboard')->get('/lang/th')->assertRedirect('/backend/dashboard')->assertSessionHas('locale', 'th');
        $this->get('/backend/dashboard')
            ->assertSee('lang="th"', false)
            ->assertSee('แดชบอร์ด')
            ->assertSee('ทรัพย์สิน')
            ->assertSee('สัญญา')
            ->assertSee('ตั้งค่า')
            ->assertSee('ผู้ใช้งาน');
    }

    /**
     * A signed-in admin (settings.manage comes with the role)
     */
    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin->value);

        return $admin;
    }
}