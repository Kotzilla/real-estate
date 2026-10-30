<?php

namespace Tests\Feature;

use App\Enums\ContractType;
use App\Enums\UserRole;
use App\Livewire\Layout\NotificationBell;
use App\Livewire\Layout\NotificationDrawer;
use App\Livewire\Layout\Search;
use App\Models\Contract;
use App\Models\District;
use App\Models\Enquiry;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\LocationSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Header Test
 *
 * Global search component, notification bell, and notification drawer.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package Tests\Feature
 */
class HeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, LocationSeeder::class]);
    }

    /**
     * Send a test database notification to a user
     *
     * @param  User  $user
     * @param  string  $message
     * @param  string  $category
     * @return void
     */
    private function notify(User $user, string $message, string $category = 'enquiries'): void
    {
        $user->notify(new class($message, $category) extends Notification
        {
            public function __construct(private string $message, private string $category) {}

            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toArray(object $notifiable): array
            {
                return [
                    'category' => $this->category,
                    'message' => $this->message,
                    'url' => '/backend/dashboard',
                ];
            }
        });
    }

    /**
     * Test header renders search button, bell, and drawer directly under body
     *
     * @return void
     */
    public function test_header_renders_search_button_bell_and_drawer(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin->value);

        $html = $this->actingAs($admin)->get(route('backend.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-kt-modal-toggle="#search_modal"', $html);
        $this->assertStringContainsString('data-kt-drawer-toggle="#notifications_drawer"', $html);

        $dom = new \DOMDocument;
        @$dom->loadHTML($html);

        $drawer = $dom->getElementById('notifications_drawer');
        $this->assertNotNull($drawer, 'notifications_drawer exists in HTML');
        $this->assertSame('body', $drawer->parentNode->nodeName, 'drawer is directly under <body> for Livewire tree');

        $searchModal = $dom->getElementById('search_modal');
        $this->assertNotNull($searchModal, 'search_modal exists in HTML');
        $this->assertSame('body', $searchModal->parentNode->nodeName, 'search modal is directly under <body>');
    }

    /**
     * Test notification drawer lists items, marks as read, and filters tabs
     *
     * @return void
     */
    public function test_notification_drawer_lifecycle_and_tabs(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->notify($me, 'New enquiry from Anna', 'enquiries');
        $this->notify($me, 'Contract RE-2026-0001 signed', 'contracts');
        $this->notify($other, 'Private notification for other', 'enquiries');

        $drawer = Livewire::actingAs($me)->test(NotificationDrawer::class)
            ->assertSee('New enquiry from Anna')
            ->assertSee('Contract RE-2026-0001 signed')
            ->assertDontSee('Private notification for other');

        // Test filtering by category tab
        $drawer->call('setTab', 'enquiries')
            ->assertSee('New enquiry from Anna')
            ->assertDontSee('Contract RE-2026-0001 signed');

        $drawer->call('setTab', 'contracts')
            ->assertSee('Contract RE-2026-0001 signed')
            ->assertDontSee('New enquiry from Anna');

        // Test marking as read
        $otherNoteId = $other->notifications()->first()->id;
        $drawer->call('markAsRead', $otherNoteId);
        $this->assertSame(1, $other->unreadNotifications()->count(), 'Cannot mark others notifications as read');

        $myNoteId = $me->notifications()->first()->id;
        $drawer->call('markAsRead', $myNoteId)->assertDispatched('notifications-changed');
        $this->assertSame(1, $me->unreadNotifications()->count());

        $drawer->call('markAllAsRead')->assertDispatched('notifications-changed');
        $this->assertSame(0, $me->unreadNotifications()->count());
    }

    /**
     * Test notification bell shows unread dot and refreshes on dispatch
     *
     * @return void
     */
    public function test_notification_bell_displays_unread_badge_and_refreshes(): void
    {
        $user = User::factory()->create();

        $bell = Livewire::actingAs($user)->test(NotificationBell::class)
            ->assertDontSeeHtml('bg-destructive');

        $this->notify($user, 'New message');
        $bell->call('refresh')->assertSeeHtml('bg-destructive');

        Livewire::actingAs($user)->test(NotificationDrawer::class)
            ->call('markAllAsRead');

        $bell->dispatch('notifications-changed')->assertDontSeeHtml('bg-destructive');
    }

    /**
     * Test global search queries properties, people, contracts and enquiries
     *
     * @return void
     */
    public function test_global_search_queries_entities_and_respects_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin->value);

        $district = District::first();
        $property = Property::factory()->create([
            'district_id' => $district->id,
            'province_id' => $district->province_id,
            'code' => 'PHU-9999',
            'name' => 'Sunset Azure Villa',
        ]);

        $person = Person::factory()->create([
            'first_name' => 'Somchai',
            'last_name' => 'Azure',
        ]);

        $contract = Contract::factory()->create([
            'code' => 'RE-2026-9999',
            'property_id' => $property->id,
            'customer_id' => $person->id,
            'type' => ContractType::Rent,
            'price' => 50000,
        ]);

        $enquiry = $property->enquiries()->create([
            'name' => 'Azure Buyer',
            'phone' => '0899999999',
            'message' => 'Interested in villa',
        ]);

        // Search as admin: sees all tabs matching "Azure"
        $component = Livewire::actingAs($admin)->test(Search::class)
            ->set('keyword', 'Azure')
            ->assertSee('PHU-9999')
            ->assertSee('Properties')
            ->assertSee('People')
            ->assertSee('Contracts')
            ->assertSee('Enquiries');

        $component->call('switchTab', 'people')->assertSee('Somchai Azure');
        $component->call('switchTab', 'contracts')->assertSee('RE-2026-9999');
        $component->call('switchTab', 'enquiries')->assertSee('Azure Buyer');

        // Short keyword shows hint
        $component->set('keyword', 'A')->assertSee('Type at least 2');

        // Clear keyword
        $component->call('clear')->assertSet('keyword', '');
    }

    /**
     * Test global search hides tabs user lacks permission to view
     *
     * @return void
     */
    public function test_search_hides_forbidden_tabs(): void
    {
        $user = User::factory()->create();
        // Give only properties.view permission
        $user->givePermissionTo('properties.view');

        $district = District::first();
        Property::factory()->create([
            'district_id' => $district->id,
            'province_id' => $district->province_id,
            'name' => 'UniqueVillaSearchTarget',
        ]);
        Person::factory()->create(['first_name' => 'UniqueVillaSearchTarget']);

        $search = Livewire::actingAs($user)->test(Search::class)
            ->set('keyword', 'UniqueVillaSearchTarget')
            ->assertSee('UniqueVillaSearchTarget')
            ->assertSee('Properties')
            ->assertDontSee('People')
            ->assertDontSee('Contracts')
            ->assertDontSee('Enquiries');

        // Attempting to switch to forbidden tab falls back
        $search->call('switchTab', 'people')
            ->assertDontSee('People');
    }
}
