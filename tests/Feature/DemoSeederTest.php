<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Models\Comment;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dev_data_has_five_staff_and_enquiries_in_every_status(): void
    {
        $this->seed(); // DatabaseSeeder: the admin, then DemoSeeder outside production

        $this->assertSame(['admin', 'kittipong', 'malee', 'napat', 'siriporn'], User::query()->orderBy('username')->pluck('username')->all());
        $this->assertTrue(User::firstWhere('username', 'siriporn')->hasRole('manager'));
        $this->assertSame(3, User::role('agent')->count());

        foreach (EnquiryStatus::cases() as $status) {
            $this->assertGreaterThan(0, Enquiry::where('status', $status)->count(), $status->value);
        }
        $this->assertSame(0, Enquiry::where('status', EnquiryStatus::Open)->has('comments')->count());
        $this->assertGreaterThan(0, Comment::count());
    }
}
