<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_update_logs_who_what_old_and_new_values_and_the_request(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(\Database\Seeders\LocationSeeder::class);
        $property = Property::factory()->create(['name' => 'Old Name', 'district_id' => \App\Models\District::query()->value('id')]);
        $admin = User::factory()->create(['username' => 'boss']);
        $admin->assignRole(UserRole::Admin->value);

        $this->actingAs($admin)
            ->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) Mobile/15E148')
            ->put("/backend/properties/{$property->id}", [...\Illuminate\Support\Arr::only($property->attributesToArray(), ['type', 'status', 'district_id', 'bedrooms', 'bathrooms', 'rent_price']), 'name' => 'Kata Villa', 'is_rent' => '1'])
            ->assertRedirect();

        $log = Activity::query()->sole();
        $this->assertSame('property-update', $log->description);
        $this->assertTrue($log->causer->is($admin));
        $this->assertTrue($log->subject->is($property));
        $this->assertSame('Kata Villa', $log->attribute_changes['attributes']['name']);
        $this->assertSame('Old Name', $log->attribute_changes['old']['name']);
        $this->assertArrayNotHasKey('updated_at', $log->attribute_changes['attributes']);
        $this->assertSame('127.0.0.1', $log->properties['ip']);
        $this->assertSame('mobile', $log->properties['agent']);
    }

    public function test_sign_in_out_and_failed_attempts_are_logged(): void
    {
        $user = User::factory()->create(['username' => 'mai', 'password' => 'secret-pass']);

        $this->post('/backend/login', ['login' => 'mai', 'password' => 'wrong']);
        $this->post('/backend/login', ['login' => 'mai', 'password' => 'secret-pass']);
        $this->post('/backend/logout');

        $this->assertSame(['login-failed', 'login', 'logout'], Activity::query()->orderBy('id')->pluck('description')->all());
        $failed = Activity::query()->where('description', 'login-failed')->sole();
        $this->assertNull($failed->causer_id);
        $this->assertSame('mai', $failed->properties['login']);
        $this->assertTrue(Activity::query()->where('description', 'login')->sole()->causer->is($user));
    }
}
