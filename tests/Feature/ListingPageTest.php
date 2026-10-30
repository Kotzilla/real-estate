<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\District;
use App\Models\Property;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingPageTest extends TestCase
{
    use RefreshDatabase;

    private function listing(array $attributes = []): Property
    {
        $this->seed(LocationSeeder::class);
        $phangan = District::query()->where('code', Property::HOME_DISTRICT)->sole();

        return Property::factory()->create([
            'name' => 'Sea View Villa', 'location' => 'Srithanu', 'district_id' => $phangan->id,
            'description' => 'Pool & garden', 'lat' => 9.75, 'lng' => 100.02, ...$attributes,
        ]);
    }

    public function test_a_listing_on_the_market_has_its_own_seo_page(): void
    {
        $property = $this->listing();
        $url = $property->publicUrl();

        $this->assertStringEndsWith('/property/'.$property->code.'/sea-view-villa-srithanu', $url);
        $html = $this->get($url)->assertOk()
            ->assertSee('<link rel="canonical" href="'.$url.'">', false)
            ->assertSee('Pool &amp; garden', false)
            ->assertSee('limitedTextarea(1000)', false)->assertSee('limited-textarea', false) // the viewing form's message box
            ->getContent();
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));

        $this->assertSame([$url], $this->get('/properties')->viewData('listings')->pluck('url')->all());
    }

    public function test_an_old_or_missing_slug_moves_to_the_current_url(): void
    {
        $property = $this->listing();

        $this->get('/property/'.$property->code)->assertRedirect($property->publicUrl())->assertStatus(301);
        $this->get('/property/'.$property->code.'/old-name')->assertRedirect($property->publicUrl())->assertStatus(301);
    }

    public function test_hidden_or_taken_listings_are_not_found(): void
    {
        $hidden = $this->listing(['is_published' => false]);
        $rented = Property::factory()->create(['district_id' => $hidden->district_id, 'status' => PropertyStatus::Rented]);

        $this->get($hidden->publicUrl())->assertNotFound();
        $this->get($rented->publicUrl())->assertNotFound();
    }

    public function test_a_facility_gets_the_icon_of_the_first_word_it_contains(): void
    {
        $this->assertSame('drop', Property::facilityIcon('Private pool'));
        $this->assertSame('ocean', Property::facilityIcon('Sea view'));
        $this->assertSame('eye', Property::facilityIcon('Nice view'));
        $this->assertSame('wifi', Property::facilityIcon('Wi-Fi'));
        $this->assertSame('check', Property::facilityIcon('Something new'));
    }
}
