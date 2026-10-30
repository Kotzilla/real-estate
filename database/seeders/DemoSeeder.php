<?php

namespace Database\Seeders;

use App\Enums\ContractStatus;
use App\Enums\EnquiryStatus;
use App\Enums\PropertyType;
use App\Models\Contract;
use App\Models\District;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Demo Seeder
 *
 * Mock listings around Koh Phangan (the island the site covers), each at a real beach or village with a photo, plus
 * owners, customers, two contracts, four staff (a manager and three agents, all `letmein`) and visitors' enquiries
 * with follow-up notes, to click through in development. Never run in production (DatabaseSeeder).
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package Database\Seeders
 */
class DemoSeeder extends Seeder
{
    // Staff: name, username, phone, role
    public const STAFF = [
        ['Napat Srisuk', 'napat', '0812345678', 'agent'],
        ['Siriporn Chaiyo', 'siriporn', '0823456789', 'manager'],
        ['Kittipong Wongsa', 'kittipong', '0834567890', 'agent'],
        ['Malee Thongdee', 'malee', '0845678901', 'agent'],
    ];

    // Enquiries: name, phone, message, status, days ago, staff notes (oldest first)
    public const ENQUIRIES = [
        ['Anna Schmidt', '+491701234567', 'Could I see it this Saturday morning?', EnquiryStatus::Open, 0, []],
        ['Tom Baker', '+447700900123', null, EnquiryStatus::Open, 1, []],
        ['Somchai Rattana', '0861234567', 'สนใจเช่าระยะยาว 1 ปี ขอนัดดูวันอาทิตย์ครับ', EnquiryStatus::Open, 2, []],
        ['Julia Rossi', '+393331234567', 'Is the price negotiable for a 2-year lease?', EnquiryStatus::InProgress, 3,
            ['Called, she wants to see it Friday 10:00.', 'Owner says 5% off for 2 years. Sent her the offer on WhatsApp.']],
        ['Mark Jensen', '+4520123456', 'Looking to buy before December.', EnquiryStatus::InProgress, 5,
            ['No answer, left a voicemail.']],
        ['Lena Müller', '+4915112345678', 'Pets allowed? We have a small dog.', EnquiryStatus::InProgress, 6,
            ['Called back, dog is fine with the owner. Viewing on Tuesday.']],
        ['Pim Saetang', '0897654321', null, EnquiryStatus::Closed, 9,
            ['Called, viewing done.', 'Signed the lease, see the contract.']],
        ['David Cohen', '+972501234567', 'Can I visit with my family next week?', EnquiryStatus::Closed, 12,
            ['Visited Monday, found the house too far from the beach. Closed.']],
        ['Sophie Martin', '+33612345678', 'Wrong number? Just checking availability.', EnquiryStatus::Closed, 15,
            ['Already rented elsewhere, not interested any more.']],
    ];

    // Areas of the island: name => [lat, lng], each a little inland of its beach so the pins sit on land
    public const AREAS = [
        'Thong Sala' => [9.7155, 100.0170],
        'Ban Tai' => [9.7095, 100.0440],
        'Ban Khai' => [9.7125, 100.0590],
        'Haad Rin' => [9.6800, 100.0660],
        'Haad Yao' => [9.7640, 99.9745],
        'Srithanu' => [9.7570, 99.9830],
        'Haad Salad' => [9.7800, 99.9790],
        'Mae Haad' => [9.7900, 99.9900],
        'Chaloklum' => [9.7860, 100.0230],
        'Thong Nai Pan' => [9.7840, 100.0650],
        'Hin Kong' => [9.7430, 99.9900],
        'Wok Tum' => [9.7330, 99.9980],
    ];

    // Mock listings: [area, type, name, bedrooms, for sale (฿), rent per month (฿), living m², land m², facilities]
    public const LISTINGS = [
        ['Srithanu', PropertyType::Villa, 'Jungle Pool Villa Srithanu', 3, 14900000, 120000, 220, 800, ['Private pool', 'Jungle view', 'Air conditioning']],
        ['Srithanu', PropertyType::House, 'Yoga Garden House', 2, null, 35000, 90, 400, ['Garden', 'Wi-Fi']],
        ['Haad Yao', PropertyType::Villa, 'Sunset Sea View Villa', 4, 28500000, 220000, 380, 1200, ['Private pool', 'Sea view', 'Sunset view']],
        ['Haad Yao', PropertyType::Condo, 'Haad Yao Beach Condo', 1, 3900000, 28000, 48, null, ['Shared pool', 'Beach access']],
        ['Haad Salad', PropertyType::Villa, 'Haad Salad Cliff Villa', 3, 19800000, null, 260, 900, ['Sea view', 'Private pool']],
        ['Mae Haad', PropertyType::Land, 'Mae Haad Sea View Land', 0, 12000000, null, null, 3200, ['Sea view', 'Chanote title']],
        ['Chaloklum', PropertyType::House, 'Fisherman Village House', 2, 6500000, 40000, 110, 500, ['Bay view', 'Garden']],
        ['Chaloklum', PropertyType::Land, 'Chaloklum Hillside Plot', 0, 4800000, null, null, 1600, ['Bay view', 'Road access']],
        ['Thong Nai Pan', PropertyType::Villa, 'Thong Nai Pan Beachfront Villa', 5, 45000000, 350000, 520, 1800, ['Beachfront', 'Private pool', 'Staff quarters']],
        ['Thong Nai Pan', PropertyType::Land, 'Thong Nai Pan Jungle Land', 0, 7500000, null, null, 4800, ['Jungle view', 'Stream']],
        ['Thong Sala', PropertyType::Townhouse, 'Thong Sala Town Townhouse', 3, 5200000, 30000, 140, 120, ['Near market', 'Parking']],
        ['Thong Sala', PropertyType::Commercial, 'Thong Sala Shophouse', 0, 9800000, 65000, 220, 160, ['Main road', 'Parking']],
        ['Thong Sala', PropertyType::Apartment, 'Pier View Apartment', 1, null, 18000, 40, null, ['Wi-Fi', 'Air conditioning']],
        ['Ban Tai', PropertyType::Villa, 'Ban Tai Garden Pool Villa', 2, 9900000, 75000, 150, 600, ['Private pool', 'Garden']],
        ['Ban Tai', PropertyType::Condo, 'Ban Tai Beach Studio', 1, null, 22000, 35, null, ['Beach access', 'Wi-Fi']],
        ['Ban Khai', PropertyType::Villa, 'Ban Khai Sea View Villa', 3, 16500000, 130000, 240, 750, ['Sea view', 'Private pool', 'Gym']],
        ['Ban Khai', PropertyType::Land, 'Ban Khai Beach Road Land', 0, 15000000, null, null, 2400, ['Near beach', 'Electricity']],
        ['Haad Rin', PropertyType::Apartment, 'Haad Rin Sunrise Apartment', 2, 4500000, 32000, 70, null, ['Sea view', 'Shared pool']],
        ['Haad Rin', PropertyType::Commercial, 'Haad Rin Beach Bar Lease', 0, null, 95000, 180, 300, ['Beachfront', 'Licence']],
        ['Hin Kong', PropertyType::House, 'Hin Kong Sunset House', 2, 7900000, 45000, 120, 640, ['Sunset view', 'Garden']],
        ['Hin Kong', PropertyType::Villa, 'Hin Kong Modern Villa', 3, 13500000, 98000, 210, 700, ['Private pool', 'Mountain view']],
        ['Wok Tum', PropertyType::Land, 'Wok Tum Coconut Plot', 0, 3500000, null, null, 1600, ['Coconut trees', 'Road access']],
        ['Wok Tum', PropertyType::House, 'Wok Tum Family Home', 3, null, 38000, 130, 450, ['Garden', 'Parking']],
        ['Srithanu', PropertyType::Land, 'Srithanu Lake View Land', 0, 8900000, null, null, 2000, ['Lake view', 'Electricity']],
    ];

    // Faker listings added after the hand-written ones, so the map and list look busy
    public const RANDOM_LISTINGS = 36;

    // Unsplash photos of beaches, sea and pool villas for the listings, in turn (images.unsplash.com/photo-{id})
    protected const PHOTOS = [
        '1468413253725-0d5181091126', '1499793983690-e29da59ef1c2', '1504214208698-ea1916a2195a',
        '1505881502353-a1986add3762', '1506953823976-52e1fdc0149a', '1507525428034-b723cf961d3e',
        '1510414842594-a61c69b5ae57', '1512917774080-9991f1c4c750', '1519046904884-53103b34b206',
        '1520250497591-112f2f40a3f4', '1540541338287-41700207dee6', '1545579133-99bb5ab189bd',
        '1552733407-5d5c46c3bb3b', '1559128010-7c1ad6e1b6a5', '1564013799919-ab600027ffc6',
        '1566073771259-6a8506099945', '1570213489059-0aac6626cade', '1571896349842-33c89424de2d',
        '1573843981267-be1999ff37cd', '1580587771525-78b9dba3b914', '1582719508461-905c673771fd',
        '1584132967334-10e028bd69f7', '1589394815804-964ed0be2eb5', '1596394516093-501ba68a0ba6',
        '1600585154340-be6161a56a0c', '1600596542815-ffad4c1539a9', '1602343168117-bb8ffe3e2e9f',
        '1613490493576-7fde63acd811',
    ];

    /**
     * Seed the demo data
     *
     * @return void
     */
    public function run(): void
    {
        // Staff besides the admin (DatabaseSeeder): sign in with the username and `letmein`
        $staff = collect(self::STAFF)->map(fn (array $row) => User::create([
            'name' => $row[0], 'username' => $row[1], 'email' => $row[1].'@example.com', 'phone' => $row[2], 'password' => 'letmein',
        ])->assignRole($row[3]));
        $agents = $staff->filter(fn (User $user) => $user->hasRole('agent'))->values();

        $district = District::query()->where('code', Property::HOME_DISTRICT)->firstOrFail();
        $owners = Person::factory()->owner()->count(5)->create();
        $customers = Person::factory()->count(4)->create();

        $properties = collect(self::LISTINGS)->map(function (array $row, int $i) use ($owners, $agents, $district) {
            [$area, $type, $name, $bedrooms, $sale, $rent, $areaM2, $land, $facilities] = $row;
            [$lat, $lng] = self::AREAS[$area];

            $property = Property::factory()->create([
                'owner_id' => $owners[$i % $owners->count()]->id,
                'user_id' => $agents[$i % $agents->count()]->id,
                'province_id' => $district->province_id,
                'district_id' => $district->id,
                'type' => $type,
                'name' => $name,
                'location' => $area,
                'address' => $area.', Ko Pha-ngan',
                'bedrooms' => $bedrooms,
                'bathrooms' => $bedrooms ? max(1, $bedrooms - 1) : 0,
                'area' => $areaM2,
                'land_area' => $land,
                'is_sale' => $sale !== null,
                'is_rent' => $rent !== null,
                'sale_price' => $sale,
                'rent_price' => $rent,
                'is_furnished' => $type !== PropertyType::Land,
                // spread the pins of one area a little, so they don't sit on top of each other
                'lat' => $lat + (($i * 37) % 9 - 4) * 0.0012,
                'lng' => $lng + (($i * 53) % 9 - 4) * 0.0012,
                'description' => "{$name} in {$area}, Koh Phangan. Mock listing for development.",
            ])->attachTags($facilities, Property::FACILITY);

            $this->photo($property, $i);

            return $property;
        });

        $this->randomListings($owners, $agents, $district, $properties->count());

        // A signed lease on the Ban Tai studio, a sale being prepared on the Sunset villa
        Contract::factory()->create([
            'property_id' => $properties[14]->id, 'customer_id' => $customers[0]->id, 'user_id' => $properties[14]->user_id,
            'price' => 22000, 'deposit' => 44000, 'advance' => 22000, 'commission_percent' => 8.33,
            'status' => ContractStatus::Active, 'signed_on' => now()->subMonths(10), 'start_on' => now()->subMonths(10)->startOfMonth(),
            'end_on' => now()->addMonths(2)->startOfMonth()->subDay(),
        ]);
        Contract::factory()->sale()->create([
            'property_id' => $properties[2]->id, 'customer_id' => $customers[1]->id, 'user_id' => $properties[2]->user_id,
            'price' => $properties[2]->sale_price, 'deposit' => 1000000,
        ]);

        $this->enquiries($properties, $staff);
    }

    /**
     * Visitors' viewing requests in every status, the ones being followed up with staff notes
     *
     * @param  Collection  $properties
     * @param  Collection  $staff
     * @return void
     */
    protected function enquiries(Collection $properties, Collection $staff): void
    {
        foreach (self::ENQUIRIES as $i => [$name, $phone, $message, $status, $daysAgo, $notes]) {
            $at = now()->subDays($daysAgo)->setTime(9 + $i % 9, ($i * 17) % 60);
            $enquiry = $properties[($i * 5) % $properties->count()]->enquiries()->create([
                'name' => $name, 'phone' => $phone, 'message' => $message, 'status' => $status,
            ]);
            $enquiry->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

            foreach ($notes as $n => $body) {
                $noteAt = $at->copy()->addHours(3 + $n * 20);
                $comment = $enquiry->comments()->create(['user_id' => $staff[($i + $n) % $staff->count()]->id, 'body' => $body]);
                $comment->forceFill(['created_at' => $noteAt, 'updated_at' => $noteAt])->save();
            }
        }
    }

    /**
     * Faker listings scattered over the island's areas
     *
     * @param  Collection  $owners
     * @param  Collection  $agents
     * @param  District  $district
     * @param  int  $offset  photo seed offset, after the hand-written listings
     * @return void
     */
    protected function randomListings(Collection $owners, Collection $agents, District $district, int $offset): void
    {
        $views = ['Sea View', 'Sunset', 'Jungle', 'Palm', 'Coconut', 'Hillside', 'Lagoon', 'Tropical', 'Bay', 'Garden'];
        $facilities = ['Private pool', 'Shared pool', 'Sea view', 'Jungle view', 'Garden', 'Wi-Fi', 'Air conditioning', 'Parking', 'Beach access', 'Gym'];

        for ($i = 0; $i < self::RANDOM_LISTINGS; $i++) {
            $area = fake()->randomKey(self::AREAS);
            [$lat, $lng] = self::AREAS[$area];
            $type = fake()->randomElement(PropertyType::cases());
            $land = $type === PropertyType::Land;
            $bedrooms = $land || $type === PropertyType::Commercial ? 0 : fake()->numberBetween(1, 5);
            // land is only sold; everything else is for sale, for rent or both
            $mode = $land ? 'sale' : fake()->randomElement(['sale', 'rent', 'both']);
            $name = fake()->randomElement($views).' '.$type->label().' '.$area;

            $property = Property::factory()->create([
                'owner_id' => $owners->random()->id,
                'user_id' => $agents->random()->id,
                'province_id' => $district->province_id,
                'district_id' => $district->id,
                'type' => $type,
                'name' => $name,
                'location' => $area,
                'address' => $area.', Ko Pha-ngan',
                'bedrooms' => $bedrooms,
                'bathrooms' => $bedrooms ? fake()->numberBetween(1, $bedrooms) : 0,
                'area' => $land ? null : fake()->numberBetween(35, 450),
                'land_area' => in_array($type, [PropertyType::Condo, PropertyType::Apartment]) ? null : fake()->numberBetween(2, 40) * 100,
                'is_sale' => $mode !== 'rent',
                'is_rent' => $mode !== 'sale',
                'sale_price' => $mode !== 'rent' ? fake()->numberBetween(25, 400) * 100000 : null,
                'rent_price' => $mode !== 'sale' ? fake()->numberBetween(15, 250) * 1000 : null,
                'is_furnished' => ! $land && fake()->boolean(70),
                'lat' => $lat + fake()->randomFloat(4, -0.004, 0.004),
                'lng' => $lng + fake()->randomFloat(4, -0.004, 0.004),
                'description' => fake()->paragraph(),
            ])->attachTags(fake()->randomElements($facilities, fake()->numberBetween(1, 3)), Property::FACILITY);

            $this->photo($property, $offset + $i);
        }
    }

    /**
     * A stock photo for the listing (Unsplash); skipped quietly when offline
     *
     * @param  Property  $property
     * @param  int  $index
     * @return void
     */
    protected function photo(Property $property, int $index): void
    {
        if (app()->runningUnitTests()) {
            return; // no network calls from the test suite
        }

        try {
            $image = Http::timeout(8)->get('https://images.unsplash.com/photo-'.self::PHOTOS[$index % count(self::PHOTOS)], [
                'w' => 1200, 'h' => 800, 'fit' => 'crop', 'q' => 75, 'fm' => 'jpg',
            ])->throw()->body();
            $path = tempnam(sys_get_temp_dir(), 'demo').'.jpg';
            file_put_contents($path, $image);
            $property->addPhoto(new UploadedFile($path, 'demo-'.$index.'.jpg', 'image/jpeg', null, true));
        } catch (Throwable) {
            // no network: the listing just has no photo
        }
    }
}
