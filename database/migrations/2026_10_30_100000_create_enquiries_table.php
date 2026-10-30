<?php

use App\Enums\EnquiryStatus;
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
        // A visitor asking about something on the public site (a listing for now), for staff to follow up; their
        // notes are comments (App\Traits\HasComments)
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->morphs('enquirable'); // what it is about: a property
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->text('message')->nullable(); // e.g. when they would like to visit
            $table->string('status', 20)->default(EnquiryStatus::Open->value); // App\Enums\EnquiryStatus
            $table->timestamps();
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
