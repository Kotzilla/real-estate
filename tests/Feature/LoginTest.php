<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_from_the_back_office_to_login(): void
    {
        $this->get('/backend/dashboard')->assertRedirect('/backend/login');
        $this->get('/backend/login')->assertOk()->assertSee(__('login.title'));
    }

    public function test_the_seeded_admin_signs_in_with_username_and_letmein(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/backend/login', ['login' => 'admin', 'password' => 'letmein'])->assertRedirect('/backend/dashboard');

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->hasRole('admin'));
        $this->assertNotNull(auth()->user()->last_login_at);
    }

    public function test_an_email_signs_in_too(): void
    {
        User::factory()->create(['email' => 'mai@example.com', 'password' => 'secret-pass']);

        $this->post('/backend/login', ['login' => 'mai@example.com', 'password' => 'secret-pass'])->assertRedirect('/backend/dashboard');
        $this->assertAuthenticated();
    }

    public function test_a_wrong_password_and_an_unknown_user_get_the_same_message(): void
    {
        User::factory()->create(['username' => 'mai', 'password' => 'secret-pass']);

        $this->from('/backend/login')->post('/backend/login', ['login' => 'mai', 'password' => 'wrong'])
            ->assertRedirect('/backend/login')->assertSessionHasErrors(['login' => __('login.failed')]);
        $this->from('/backend/login')->post('/backend/login', ['login' => 'nobody', 'password' => 'wrong'])
            ->assertSessionHasErrors(['login' => __('login.failed')]);
        $this->assertGuest();
    }

    public function test_a_filled_honeypot_is_refused_even_with_the_right_password(): void
    {
        User::factory()->create(['username' => 'mai', 'password' => 'secret-pass']);

        $this->post('/backend/login', ['login' => 'mai', 'password' => 'secret-pass', 'website' => 'spam'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_an_account_without_a_password_cannot_sign_in(): void
    {
        User::factory()->create(['username' => 'nopass', 'password' => null]);

        $this->post('/backend/login', ['login' => 'nopass', 'password' => ''])->assertSessionHasErrors('password');
        $this->post('/backend/login', ['login' => 'nopass', 'password' => 'anything'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_sixth_try_in_a_minute_is_blocked(): void
    {
        foreach (range(1, 5) as $try) {
            $this->post('/backend/login', ['login' => 'admin', 'password' => 'wrong']);
        }

        $this->post('/backend/login', ['login' => 'admin', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_signing_out_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create())->post('/backend/logout')->assertRedirect('/backend/login');
        $this->assertGuest();
        $this->get('/backend/dashboard')->assertRedirect('/backend/login');
    }

    public function test_the_old_sign_in_urls_redirect_into_the_back_office(): void
    {
        $this->get('/login')->assertRedirect('/backend/login');
        $this->get('/backend')->assertRedirect('/backend/login'); // a guest: the sign-in page
    }
}
