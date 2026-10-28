<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\District;
use App\Models\Property;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_links_the_newest_listings_and_the_areas(): void
    {
        $this->seed(LocationSeeder::class);
        $phangan = District::query()->where('code', Property::HOME_DISTRICT)->sole();
        $villa = Property::factory()->create(['name' => 'Sea View Villa', 'location' => 'Srithanu', 'district_id' => $phangan->id, 'is_sale' => true, 'is_rent' => false]);
        Property::factory()->create(['name' => 'Rented Villa', 'location' => 'Haad Rin', 'district_id' => $phangan->id, 'status' => PropertyStatus::Rented]);

        $response = $this->get('/')->assertOk()
            ->assertSee($villa->publicUrl(), false)
            ->assertDontSee('Rented Villa')
            ->assertSee(route('listings', ['area' => 'Srithanu']), false)
            ->assertSee(route('privacy'), false)
            ->assertSee(['id="about"', 'id="contact"', 'class="site-footer"'], false)
            ->assertSee([__('site.who_title'), __('site.contact_title'), config('site.phone'), config('site.email')]);

        $this->assertSame(['Srithanu' => 1], $response->viewData('areas')->all());
        $this->assertSame(1, $response->viewData('forSale'));
        $html = $response->getContent();
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));
    }

    public function test_the_header_menu_marks_the_page_you_are_on(): void
    {
        $current = fn (string $url) => preg_match_all('/aria-current="page"\s*><i class="ki-filled ([\w-]+)"/', $this->get($url)->getContent(), $m) ? $m[1] : [];

        $this->assertSame(['ki-element-11'], $current('/'));
        $this->assertSame(['ki-key'], $current('/properties?offer=rent'));
        $this->assertSame([], $current('/privacy'));
    }

    public function test_the_header_menu_folds_behind_a_burger_on_small_screens(): void
    {
        $this->get('/')->assertOk()
            ->assertSee(['class="site-burger"', 'aria-controls="site-nav"', 'id="site-nav"'], false);
    }

    public function test_the_privacy_policy_is_in_both_languages(): void
    {
        $html = $this->get('/privacy')->assertOk()->assertSee(__('privacy.sections.0.0'))->assertSee(config('site.email'))->getContent();
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));

        $this->withSession(['locale' => 'th'])->get('/privacy')->assertOk()->assertSee('นโยบายความเป็นส่วนตัว');
    }
}
