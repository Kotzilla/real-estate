<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Users\Table;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class UsersPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_guests_are_sent_to_login_and_agents_get_no_page(): void
    {
        $this->get('/backend/users')->assertRedirect('/backend/login');

        $agent = $this->user(['username' => 'nok'], UserRole::Agent);
        $this->actingAs($agent)->get('/backend/users')->assertForbidden();
        $this->actingAs($agent)->get('/backend/users/archived')->assertForbidden();
    }

    public function test_an_admin_sees_the_list_with_role_phone_and_last_sign_in(): void
    {
        $agent = $this->user(
            ['name' => 'SOMCHAI JAIDEE', 'username' => 'sikeen', 'email' => 'sikeen@example.com', 'phone' => '0812345678', 'last_login_at' => now()],
            UserRole::Agent
        );

        $this->actingAs($this->admin())->get('/backend/users')
            ->assertOk()
            ->assertSee('SOMCHAI JAIDEE')
            ->assertSee('sikeen@example.com')
            ->assertSee('0812345678')
            ->assertSee(__('users.roles.agent'))
            ->assertSee(__('users.table.last_seen'));
    }

    public function test_the_page_keeps_its_tags_balanced(): void
    {
        $response = $this->actingAs($this->admin())->get('/backend/users')->assertOk();

        // One </div> too many pops <main> out of the wrapper and squeezes the footer into a column beside the content.
        $html = $response->getContent();
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));
    }

    public function test_search_and_role_filter_narrow_the_list(): void
    {
        $this->user(['name' => 'SOMCHAI JAIDEE', 'username' => 'sikeen'], UserRole::Agent);
        $this->user(['name' => 'MAI SRISUK', 'username' => 'mai'], UserRole::Manager);

        Livewire::actingAs($this->admin())->test(Table::class)
            ->assertSee('SOMCHAI JAIDEE')
            ->assertSee('MAI SRISUK')
            ->set('q', 'sikeen')
            ->assertSee('SOMCHAI JAIDEE')
            ->assertDontSee('MAI SRISUK')
            ->set('q', '')
            ->set('roleFilter', UserRole::Manager->value)
            ->assertSee('MAI SRISUK')
            ->assertDontSee('SOMCHAI JAIDEE');
    }

    public function test_the_archive_tab_holds_the_archived_accounts(): void
    {
        $admin = $this->admin();
        $gone = $this->user(['name' => 'OLD AGENT'], UserRole::Agent);
        $gone->delete();

        Livewire::actingAs($admin)->test(Table::class, ['status' => 'archived'])
            ->assertSee('OLD AGENT')
            ->assertSee(__('users.table.restore_user'));

        Livewire::actingAs($admin)->test(Table::class)
            ->assertDontSee('OLD AGENT');
    }

    public function test_you_cannot_archive_yourself(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(Table::class)->call('archiveUser', $admin->id);

        $this->assertNotSoftDeleted($admin);
    }

    public function test_the_last_admin_stays_put(): void
    {
        $admin = $this->admin();
        // settings.manage is a permission, so a manager can be given it on purpose (the Users page is not roles-only)
        $manager = $this->user(['name' => 'MAI SRISUK'], UserRole::Manager);
        $manager->givePermissionTo('settings.manage');

        // One admin left: archiving him would lock everyone out of the back office
        Livewire::actingAs($manager)->test(Table::class)->call('archiveUser', $admin->id);
        $this->assertNotSoftDeleted($admin);

        // A second admin, and the same manager may archive the first one
        $this->user(['name' => 'NOK ADMIN'], UserRole::Admin);
        Livewire::actingAs($manager)->test(Table::class)->call('archiveUser', $admin->id);
        $this->assertSoftDeleted($admin);
    }

    public function test_archiving_keeps_the_name_on_old_contracts_and_can_be_undone(): void
    {
        $admin = $this->admin();
        $agent = $this->user(['name' => 'SOMCHAI JAIDEE'], UserRole::Agent);

        Livewire::actingAs($admin)->test(Table::class)->call('archiveUser', $agent->id);
        $this->assertSoftDeleted($agent);
        $this->assertSame('SOMCHAI JAIDEE', User::withTrashed()->find($agent->id)->name);

        Livewire::actingAs($admin)->test(Table::class, ['status' => 'archived'])->call('restoreUser', $agent->id);
        $this->assertNotSoftDeleted($agent->fresh());
    }

    public function test_editing_changes_the_role_and_phone(): void
    {
        $agent = $this->user(['name' => 'SOMCHAI JAIDEE', 'username' => 'sikeen', 'email' => 'sikeen@example.com'], UserRole::Agent);

        Livewire::actingAs($this->admin())->test(Table::class)
            ->call('openEditUser', $agent->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editForm.role', UserRole::Agent->value)
            ->set('editForm.role', UserRole::Manager->value)
            ->set('editForm.phone', '0899999999')
            ->call('saveUser')
            ->assertSet('showEditModal', false);

        $agent->refresh();
        $this->assertTrue($agent->hasRole(UserRole::Manager->value));
        $this->assertSame('0899999999', $agent->phone);
    }

    public function test_the_last_admin_cannot_be_demoted(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(Table::class)
            ->call('openEditUser', $admin->id)
            ->set('editForm.role', UserRole::Agent->value)
            ->call('saveUser')
            ->assertHasErrors(['editForm.role' => __('users.edit.cannot_demote_last_admin')]);

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_an_invite_creates_the_account_and_emails_the_set_password_link(): void
    {
        Notification::fake();

        Livewire::actingAs($this->admin())->test(Table::class)
            ->call('openInviteModal')
            ->assertSet('showInviteModal', true)
            ->set('inviteForm.name', 'NEW AGENT')
            ->set('inviteForm.username', 'new')
            ->set('inviteForm.email', 'new@example.com')
            ->set('inviteForm.role', UserRole::Agent->value)
            ->set('inviteForm.phone', '0811111111')
            ->call('sendInvite')
            ->assertSet('showInviteModal', false)
            ->assertHasNoErrors();

        $invited = User::where('username', 'new')->firstOrFail();
        $this->assertTrue($invited->hasRole(UserRole::Agent->value));
        $this->assertSame('0811111111', $invited->phone);
        $this->assertNotEmpty($invited->password); // a random one, so only the emailed link opens the account
        Notification::assertSentTo($invited, ResetPassword::class);
    }

    public function test_an_invite_needs_a_free_username_and_email(): void
    {
        $this->user(['username' => 'mai', 'email' => 'mai@example.com'], UserRole::Agent);
        $admin = $this->admin();
        $before = User::count();

        Livewire::actingAs($admin)->test(Table::class)
            ->set('inviteForm.name', 'NEW AGENT')
            ->set('inviteForm.username', 'mai')
            ->set('inviteForm.email', 'mai@example.com')
            ->set('inviteForm.role', UserRole::Agent->value)
            ->call('sendInvite')
            ->assertHasErrors(['inviteForm.username', 'inviteForm.email']);

        $this->assertSame($before, User::count());
    }

    public function test_the_invitation_link_sets_the_password(): void
    {
        $invited = $this->user(['username' => 'new', 'email' => 'new@example.com'], UserRole::Agent);
        $invited->forceFill(['password' => 'a-random-password'])->save();

        $token = Password::createToken($invited);

        $this->get("/backend/set-password/$token?email=new@example.com")
            ->assertOk()
            ->assertSee(__('set_password.title'))
            ->assertSee(__('set_password.hint'));

        // A short password is refused in the visitor's language, and the link still works
        $this->post('/backend/set-password', [
            'token' => $token,
            'email' => 'new@example.com',
            'password' => 'too-short',
            'password_confirmation' => 'too-short',
        ])->assertSessionHasErrors(['password' => __('set_password.hint')]);

        $this->post('/backend/set-password', [
            'token' => $token,
            'email' => 'new@example.com',
            'password' => 'a-long-enough-password',
            'password_confirmation' => 'a-long-enough-password',
        ])->assertRedirect('/backend/login')->assertSessionHas('status');

        $this->assertTrue(Hash::check('a-long-enough-password', $invited->fresh()->password));
    }

    public function test_a_signed_in_user_cannot_reuse_someone_elses_set_password_link(): void
    {
        $invited = $this->user(['username' => 'new', 'email' => 'new@example.com'], UserRole::Agent);
        $token = Password::createToken($invited);

        // An expired or already used link: the same page, back with the reason
        $this->post('/backend/set-password', [
            'token' => $token,
            'email' => 'new@example.com',
            'password' => 'a-long-enough-password',
            'password_confirmation' => 'a-long-enough-password',
        ])->assertRedirect();

        $this->post('/backend/set-password', [
            'token' => $token,
            'email' => 'new@example.com',
            'password' => 'another-long-password',
            'password_confirmation' => 'another-long-password',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('a-long-enough-password', $invited->fresh()->password));
    }


    /**
     * A signed-in user with one role
     *
     * @param  array<string, mixed>  $attributes
     * @return User
     */
    private function user(array $attributes, UserRole $role): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role->value);

        return $user;
    }

    /**
     * A signed-in admin (settings.manage comes with the role)
     */
    private function admin(): User
    {
        return $this->user(['username' => 'admin'], UserRole::Admin);
    }
}
