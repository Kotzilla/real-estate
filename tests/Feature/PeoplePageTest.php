<?php

namespace Tests\Feature;

use App\Enums\PersonType;
use App\Enums\UserRole;
use App\Livewire\People\Table;
use App\Models\Person;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PeoplePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_every_page_renders_with_balanced_tags(): void
    {
        $person = Person::factory()->owner()->create();
        $this->actingAs($this->agent());

        foreach (['/backend/people', '/backend/people?type=owner', '/backend/people/create?type=owner', "/backend/people/{$person->id}", "/backend/people/{$person->id}/edit"] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'), $url);
        }
    }

    public function test_adding_a_contact_needs_a_first_name_and_type_and_cleans_the_phone(): void
    {
        $this->actingAs($this->agent())->post('/backend/people', [])->assertSessionHasErrors(['first_name', 'type']);

        $this->post('/backend/people', ['type' => 'owner', 'first_name' => 'Somchai', 'first_name_th' => 'สมชาย', 'phone' => '081-234 5678'])
            ->assertRedirect();

        $person = Person::query()->sole();
        $this->assertSame(PersonType::Owner, $person->type);
        $this->assertSame('0812345678', $person->phone);
        $this->assertSame('สมชาย', $person->fullName('th'));
    }

    public function test_the_list_filters_by_type_and_searches_both_languages(): void
    {
        Person::factory()->owner()->create(['first_name' => 'Somchai']);
        Person::factory()->create(['first_name' => 'John', 'first_name_th' => null]);

        $names = fn ($component) => $component->viewData('people')->pluck('first_name')->all();
        $table = Livewire::actingAs($this->agent())->test(Table::class);

        $this->assertSame(['John', 'Somchai'], $names($table));
        $this->assertSame(['Somchai'], $names($table->set('type', 'owner')));
        $this->assertSame(['Somchai'], $names($table->set('type', 'all')->set('q', 'สมชาย')));
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
