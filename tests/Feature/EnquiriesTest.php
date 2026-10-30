<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Enums\UserRole;
use App\Livewire\Comments;
use App\Models\District;
use App\Models\Enquiry;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\LocationSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EnquiriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, LocationSeeder::class]);
    }

    public function test_a_visitor_asks_to_see_a_listing(): void
    {
        $property = $this->listing();

        $this->post(route('listing.enquire', $property->code), ['name' => 'Anna', 'phone' => '081-234 5678', 'message' => 'Saturday morning?'])
            ->assertRedirect($property->publicUrl().'#enquiry');

        $this->assertDatabaseHas('enquiries', [
            'enquirable_type' => 'property', 'enquirable_id' => $property->id, 'name' => 'Anna', 'phone' => '0812345678', 'status' => 'open',
        ]);
        $this->followingRedirects()->get($property->publicUrl())->assertOk();
    }

    public function test_name_and_phone_are_required_and_bots_are_turned_away(): void
    {
        $property = $this->listing();
        $url = route('listing.enquire', $property->code);

        $this->post($url, [])->assertSessionHasErrors(['name', 'phone'])->assertSessionDoesntHaveErrors('message');
        $this->post($url, ['message' => str_repeat('a', 1001)])->assertSessionHasErrors('message');
        $html = $this->get($property->publicUrl())->getContent(); // the errors and the typed message come back
        $this->assertMatchesRegularExpression('/<textarea[^>]*name="message"[^>]*aria-invalid="true"[^>]*>a{1001}</', $html);
        $this->post($url, ['name' => 'Anna', 'phone' => 'call me'])->assertSessionHasErrors('phone');
        $this->post($url, ['name' => 'Bot', 'phone' => '0812345678', 'website' => 'http://spam'])->assertSessionHasErrors('website');
        $this->assertSame(0, Enquiry::count());
    }

    public function test_a_listing_off_the_market_takes_no_enquiries(): void
    {
        $property = $this->listing(['is_published' => false]);

        $this->post(route('listing.enquire', $property->code), ['name' => 'Anna', 'phone' => '0812345678'])->assertNotFound();
    }

    public function test_staff_filter_by_status_and_move_an_enquiry_along(): void
    {
        $property = $this->listing();
        $open = $property->enquiries()->create(['name' => 'Anna', 'phone' => '0812345678']);
        $property->enquiries()->create(['name' => 'Bob', 'phone' => '0899999999', 'status' => EnquiryStatus::Closed]);
        $agent = $this->agent();

        $html = $this->actingAs($agent)->get('/backend/enquiries?status=open')->assertOk()->assertSee('Anna')->assertDontSee('Bob')
            ->assertSee(__('Enquiries').' (1)')->getContent();
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));
        $html = $this->get("/backend/enquiries/{$open->id}")->assertOk()->assertSee('Sea View Villa')
            ->assertSee('limitedTextarea(2000)', false)->getContent(); // the note box grows and shows the ring
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));

        $this->put("/backend/enquiries/{$open->id}", ['status' => 'nonsense'])->assertSessionHasErrors('status');
        $this->put("/backend/enquiries/{$open->id}", ['status' => 'in_progress'])->assertRedirect();
        $this->assertSame(EnquiryStatus::InProgress, $open->fresh()->status);
        $this->assertDatabaseHas('activity_log', ['description' => 'enquiry-update', 'subject_type' => 'enquiry', 'subject_id' => $open->id]);

        $this->actingAs(User::factory()->create())->get('/backend/enquiries')->assertForbidden();
        $this->put("/backend/enquiries/{$open->id}", ['status' => 'closed'])->assertForbidden();
    }

    public function test_staff_keep_follow_up_notes_on_an_enquiry(): void
    {
        $enquiry = $this->listing()->enquiries()->create(['name' => 'Anna', 'phone' => '0812345678']);
        $agent = $this->agent();

        Livewire::actingAs($agent)->test(Comments::class, ['model' => $enquiry])
            ->call('add')->assertHasErrors(['body' => 'required'])
            ->set('body', '  Called, viewing Saturday  ')->call('add')->assertHasNoErrors()
            ->assertSee('Called, viewing Saturday')->assertSee($agent->name);

        $this->assertSame('Called, viewing Saturday', $enquiry->comments()->sole()->body);
        $this->assertSame($agent->id, $enquiry->comments()->sole()->user_id);

        Livewire::actingAs(User::factory()->create())->test(Comments::class, ['model' => $enquiry])->assertForbidden();
    }

    /**
     * A villa on Koh Phangan, on the market
     */
    private function listing(array $attributes = []): Property
    {
        $phangan = District::query()->where('code', Property::HOME_DISTRICT)->sole();

        return Property::factory()->create(['name' => 'Sea View Villa', 'district_id' => $phangan->id, ...$attributes]);
    }

    /**
     * A signed-in agent
     */
    private function agent(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::Agent->value);

        return $user;
    }
}
