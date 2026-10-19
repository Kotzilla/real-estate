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
        // The 14 southern provinces (LocationSeeder, from findnest's data); a property is in one
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique(); // the Thai government code, e.g. 83 = Phuket
            $table->string('name');
            $table->string('name_th');
            $table->timestamps();
        });

        // Amphoe (districts) of those provinces, e.g. Kathu in Phuket
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('code', 6)->unique(); // e.g. 8302 = Kathu
            $table->string('name');
            $table->string('name_th');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
    }
};
