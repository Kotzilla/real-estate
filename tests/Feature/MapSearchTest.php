<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\District;
use App\Models\Property;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_map_lists_only_koh_phangan_listings_still_on_the_market(): void
    {
        $this->seed(LocationSeeder::class);
        config(['services.mapbox.token' => 'pk.test']);
        $phangan = District::query()->where('code', Property::HOME_DISTRICT)->sole();
        $kathu = District::query()->where('name', 'Kathu')->sole();
        $pin = ['lat' => 9.75, 'lng' => 100.02];

        Property::factory()->create(['name' => 'Srithanu Villa', 'district_id' => $phangan->id, ...$pin]);
        Property::factory()->create(['name' => 'Phuket Villa', 'district_id' => $kathu->id, ...$pin]);
        Property::factory()->create(['name' => 'Rented Villa', 'district_id' => $phangan->id, 'status' => PropertyStatus::Rented, ...$pin]);
        Property::factory()->create(['name' => 'Hidden Villa', 'district_id' => $phangan->id, 'is_published' => false, ...$pin]);
        Property::factory()->create(['name' => 'No Pin Villa', 'district_id' => $phangan->id]);

        $response = $this->get('/properties')->assertOk()->assertSee('mapbox-gl.js', false)->assertDontSee(__('home.no_map'));

        $this->assertSame(['Srithanu Villa'], $response->viewData('listings')->pluck('title')->all());
        $html = $response->getContent();
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));
    }

    public function test_without_a_token_it_says_so_instead_of_a_blank_map(): void
    {
        config(['services.mapbox.token' => null]);

        $this->get('/properties')->assertOk()->assertSee(__('home.no_map'));
    }
}
