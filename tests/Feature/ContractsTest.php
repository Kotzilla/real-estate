<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\District;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\LocationSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, LocationSeeder::class]);
    }

    public function test_every_page_and_print_renders_with_balanced_tags(): void
    {
        $lease = Contract::factory()->create(['property_id' => $this->property()->id]);
        $sale = Contract::factory()->sale()->create(['property_id' => $this->property()->id]);
        $this->actingAs($this->agent());

        foreach (['/backend/dashboard', '/backend/contracts', '/backend/contracts/create', "/backend/contracts/create?property={$lease->property_id}"] as $url) {
            $this->assertBalanced($this->get($url)->assertOk()->getContent(), $url);
        }
        foreach ([$lease, $sale] as $contract) {
            foreach (["/backend/contracts/{$contract->id}", "/backend/contracts/{$contract->id}/edit"] as $url) {
                $this->assertBalanced($this->get($url)->assertOk()->getContent(), $url);
            }
        }

        $this->get("/backend/contracts/{$lease->id}/print/en")->assertOk()->assertSee('Lease Agreement')
            ->assertSee('Fifty Thousand Baht Only')->assertSee('Phuket Province');
        $this->get("/backend/contracts/{$lease->id}/print/th")->assertOk()->assertSee('สัญญาเช่า')
            ->assertSee('ห้าหมื่นบาทถ้วน')->assertSee((string) ($lease->signed_on->year + 543));
        $this->get("/backend/contracts/{$sale->id}/print/en")->assertOk()->assertSee('Agreement to Sell and Purchase')
            ->assertSee('10,800,000'); // the balance due at transfer
        $this->get("/backend/contracts/{$sale->id}/print/th")->assertOk()->assertSee('สัญญาจะซื้อจะขาย');
        $this->get("/backend/contracts/{$sale->id}/print/fr")->assertNotFound();
    }

    public function test_a_lease_works_out_its_commission_end_date_and_code(): void
    {
        $property = $this->property();
        $customer = Person::factory()->create();

        $this->actingAs($this->agent())->post('/backend/contracts', [
            'property_id' => $property->id, 'customer_id' => $customer->id, 'type' => 'rent', 'status' => 'draft',
            'months' => 12, 'price' => 50000, 'deposit' => 100000, 'advance' => 50000, 'payment_day' => 5,
            'commission_percent' => 8.33, 'signed_on' => '2026-10-19', 'start_on' => '2026-11-01',
        ])->assertRedirect();

        $contract = Contract::query()->sole();
        $this->assertSame('RE-2026-0001', $contract->code);
        $this->assertSame('2027-10-31', $contract->end_on->toDateString());
        $this->assertEquals(49980, (float) $contract->commission); // 50,000 × 12 × 8.33%
        $this->assertEquals(150000, $contract->dueOnSigning());
        $this->assertSame(PropertyStatus::Available, $property->fresh()->status); // a draft holds nothing
    }

    public function test_required_fields_and_the_lease_only_ones(): void
    {
        $this->actingAs($this->agent())->post('/backend/contracts', ['type' => 'rent'])
            ->assertSessionHasErrors(['property_id', 'customer_id', 'status', 'price', 'commission_percent', 'signed_on', 'start_on', 'months', 'payment_day']);

        $this->post('/backend/contracts', ['type' => 'sale'])->assertSessionDoesntHaveErrors(['months', 'payment_day']);
        $this->post('/backend/contracts', [])->assertSessionHasErrors('type');
    }

    public function test_a_sale_has_no_term_and_its_commission_is_on_the_price(): void
    {
        $contract = Contract::factory()->sale()->create(['property_id' => $this->property()->id, 'months' => 12, 'advance' => 5000]);

        $this->assertNull($contract->months);
        $this->assertEquals(0, (float) $contract->advance);
        $this->assertEquals(360000, (float) $contract->commission); // 12,000,000 × 3%
    }

    public function test_signing_ending_and_cancelling_move_the_property_status(): void
    {
        $property = $this->property();
        $lease = Contract::factory()->create(['property_id' => $property->id]);

        $lease->update(['status' => ContractStatus::Active]);
        $this->assertSame(PropertyStatus::Rented, $property->fresh()->status);

        // a second deal on the same place falls through: the signed lease still holds it
        Contract::factory()->create(['property_id' => $property->id])->update(['status' => ContractStatus::Cancelled]);
        $this->assertSame(PropertyStatus::Rented, $property->fresh()->status);

        $lease->update(['status' => ContractStatus::Completed]);
        $this->assertSame(PropertyStatus::Available, $property->fresh()->status);

        Contract::factory()->sale()->create(['property_id' => $property->id, 'status' => ContractStatus::Active]);
        $this->assertSame(PropertyStatus::Sold, $property->fresh()->status);
    }

    public function test_the_contract_page_lists_what_the_print_is_missing(): void
    {
        $owner = Person::factory()->owner()->create(['bank_account_no' => null]);
        $contract = Contract::factory()->create([
            'property_id' => $this->property(['owner_id' => $owner->id])->id,
            'customer_id' => Person::factory()->create(['passport_no' => null])->id,
        ]);

        $this->actingAs($this->agent())->get("/backend/contracts/{$contract->id}")
            ->assertSee(__('contracts.missing.owner_bank'), false)
            ->assertSee(__('contracts.missing.customer_id'), false)
            ->assertDontSee(__('contracts.missing.owner_id'), false);
    }

    public function test_without_view_permission_contracts_are_closed(): void
    {
        \Spatie\Permission\Models\Role::findByName('agent')->revokePermissionTo(['contracts.view', 'contracts.manage']);
        $contract = Contract::factory()->create(['property_id' => $this->property()->id]);
        $agent = $this->agent();

        $this->actingAs($agent)->get('/backend/contracts')->assertForbidden();
        $this->actingAs($agent)->get("/backend/contracts/{$contract->id}/print/en")->assertForbidden();
        $this->actingAs($agent)->get('/backend/dashboard')->assertOk()->assertDontSee(__('dashboard.drafts'));
    }

    /**
     * Same number of opening and closing divs
     */
    private function assertBalanced(string $html, string $url): void
    {
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'), $url);
    }

    /**
     * A villa in Kathu, Phuket
     */
    private function property(array $attributes = []): Property
    {
        $kathu = District::query()->where('name', 'Kathu')->sole();

        return Property::factory()->create(['district_id' => $kathu->id, 'province_id' => $kathu->province_id, ...$attributes]);
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
