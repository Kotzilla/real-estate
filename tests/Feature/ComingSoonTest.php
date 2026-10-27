<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComingSoonTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_site_shows_the_coming_soon_page_while_it_is_on(): void
    {
        config(['site.coming_soon' => true]);

        foreach (['/', '/properties', '/property/PHU-0001'] as $url) {
            $html = $this->get($url)->assertOk()->assertSee(__('site.soon_title'))->getContent();
            $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));
        }
        $this->get('/privacy')->assertOk()->assertDontSee(__('site.soon_title'));
        $this->get('/backend/login')->assertOk()->assertDontSee(__('site.soon_title'));
    }

    public function test_signed_in_staff_and_an_off_switch_see_the_real_site(): void
    {
        config(['site.coming_soon' => true]);
        $this->actingAs(User::factory()->create())->get('/')->assertOk()->assertDontSee(__('site.soon_title'));

        auth()->logout();
        config(['site.coming_soon' => false]);
        $this->get('/')->assertOk()->assertDontSee(__('site.soon_title'));
    }
}
