<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Livewire\Properties\Table;
use App\Models\District;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\LocationSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PropertiesPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, LocationSeeder::class]);
    }

    public function test_every_page_renders_with_balanced_tags(): void
    {
        $property = Property::factory()->create(['district_id' => $this->kathu()->id]);
        $this->actingAs($this->user(UserRole::Agent));

        foreach (['/backend/properties', '/backend/properties/create', "/backend/properties/{$property->id}", "/backend/properties/{$property->id}/edit"] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'), $url);
        }
    }

    public function test_adding_a_property_takes_the_province_from_the_district_and_makes_its_code(): void
    {
        Storage::fake('public');
        $owner = Person::factory()->owner()->create();

        $this->actingAs($this->user(UserRole::Agent))->post('/backend/properties', $this->form([
            'owner_id' => $owner->id,
            'facilities' => '[{"value":"Pool"},{"value":"Sea view"}]',
            'photos' => [UploadedFile::fake()->image('front.jpg', 1200, 800)],
        ]))->assertRedirect();

        $property = Property::query()->sole();
        $this->assertSame('PHU-0001', $property->code);
        $this->assertSame($this->kathu()->province_id, $property->province_id);
        $this->assertSame(['Pool', 'Sea view'], $property->tagsWithType(Property::FACILITY)->pluck('name')->all());
        $this->assertTrue($property->photos()->sole()->is_cover);
        $this->assertNull($property->sale_price); // not for sale: no sale price kept
    }

    public function test_required_fields_and_the_price_of_what_it_is_offered_as(): void
    {
        $this->actingAs($this->user(UserRole::Agent))
            ->post('/backend/properties', ['is_sale' => '1', 'is_rent' => '0'])
            ->assertSessionHasErrors(['name', 'type', 'status', 'district_id', 'bedrooms', 'bathrooms', 'sale_price']);

        $this->post('/backend/properties', $this->form(['is_rent' => '1', 'rent_price' => '']))->assertSessionHasErrors('rent_price');
        $this->post('/backend/properties', $this->form(['is_rent' => '0', 'is_sale' => '0']))->assertSessionHasErrors('is_sale');
        $this->assertSame(0, Property::query()->count());
    }

    public function test_editing_removes_photos_and_changes_the_cover(): void
    {
        Storage::fake('public');
        $property = Property::factory()->create(['district_id' => $this->kathu()->id]);
        $first = $property->addPhoto(UploadedFile::fake()->image('a.jpg'));
        $second = $property->addPhoto(UploadedFile::fake()->image('b.jpg'));
        $third = $property->addPhoto(UploadedFile::fake()->image('c.jpg'));

        $this->actingAs($this->user(UserRole::Agent))->put("/backend/properties/{$property->id}", $this->form([
            'remove_photos' => [$first->id],
            'cover_photo_id' => $third->id,
        ]))->assertRedirect("/backend/properties/{$property->id}");

        $this->assertSame([$second->id, $third->id], $property->photos()->pluck('id')->all());
        $this->assertTrue($third->fresh()->is_cover);
    }

    public function test_without_manage_the_list_opens_but_adding_does_not(): void
    {
        \Spatie\Permission\Models\Role::findByName('agent')->revokePermissionTo('properties.manage');
        $agent = $this->user(UserRole::Agent);

        $this->actingAs($agent)->get('/backend/properties')->assertOk()->assertDontSee(__('properties.table.add'));
        $this->actingAs($agent)->get('/backend/properties/create')->assertForbidden();
        $this->actingAs($agent)->post('/backend/properties', $this->form())->assertForbidden();
    }

    public function test_the_list_filters_by_offer_status_and_search(): void
    {
        Property::factory()->create(['name' => 'Kata Villa', 'is_sale' => true, 'is_rent' => false]);
        Property::factory()->create(['name' => 'Rawai Condo', 'is_sale' => false, 'is_rent' => true, 'status' => PropertyStatus::Rented]);

        $names = fn ($component) => $component->viewData('properties')->pluck('name')->sort()->values()->all();
        $table = Livewire::actingAs($this->user(UserRole::Agent))->test(Table::class);

        $this->assertSame(['Kata Villa', 'Rawai Condo'], $names($table));
        $this->assertSame(['Kata Villa'], $names($table->set('offer', 'sale')));
        $this->assertSame([], $names($table->set('status', 'rented')));
        $this->assertSame(['Rawai Condo'], $names($table->set('offer', 'all')));
        $this->assertSame([], $names($table->set('q', 'kata villa')));
    }

    /**
     * A valid form post for a villa for rent in Kathu, with overrides
     */
    private function form(array $overrides = []): array
    {
        return [
            'type' => 'villa', 'status' => 'available', 'name' => 'Kamala Sea View Villa', 'district_id' => $this->kathu()->id,
            'bedrooms' => 3, 'bathrooms' => 2, 'is_rent' => '1', 'rent_price' => 90000, 'is_sale' => '0', 'sale_price' => 15000000,
            ...$overrides,
        ];
    }

    /**
     * Kathu, Phuket (seeded)
     */
    private function kathu(): District
    {
        return District::query()->where('name', 'Kathu')->sole();
    }

    /**
     * A signed-in user with one role
     */
    private function user(UserRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }
}
