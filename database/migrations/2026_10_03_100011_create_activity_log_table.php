<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // spatie/laravel-activitylog: who did what in the back office (App\Traits\LogActivity), e.g. "property-update"
        // on a property by a user, with the old / new values and the request's IP, browser and device.
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('subject', 'subject'); // what was acted on (a property, a contract...)
            $table->nullableMorphs('causer', 'causer'); // who did it; null = nobody signed in (a failed sign-in)
            $table->string('log_name')->nullable()->index();
            $table->string('event')->nullable();
            $table->text('description'); // the action code, e.g. "contract-update"
            $table->json('attribute_changes')->nullable(); // {"attributes": new values, "old": old values} of an update
            $table->json('properties')->nullable(); // ip, browser, device + the action's own details
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
