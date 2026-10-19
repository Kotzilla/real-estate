<?php

use App\Enums\PropertyStatus;
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
        // A listing: for sale, for rent or both. Facilities are spatie tags (type "facility"), photos the photos table.
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('people')->nullOnDelete(); // the lessor / seller
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // the agent in charge
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20)->unique(); // e.g. PKT-0001, made from the province on create
            $table->string('type', 20); // App\Enums\PropertyType
            $table->string('status', 20)->default(PropertyStatus::Available->value); // App\Enums\PropertyStatus
            $table->string('name');
            $table->string('name_th')->nullable();
            $table->text('description')->nullable();
            $table->text('description_th')->nullable();
            $table->string('address')->nullable();
            $table->string('address_th')->nullable();
            $table->string('location')->nullable(); // the area people search by, e.g. Rawai, Kata, Ao Nang
            $table->unsignedTinyInteger('bedrooms')->default(0);
            $table->unsignedTinyInteger('bathrooms')->default(0);
            $table->unsignedTinyInteger('floor')->nullable(); // condos: which floor
            $table->decimal('area', 10, 2)->nullable(); // living area, m²
            $table->decimal('land_area', 10, 2)->nullable(); // land, m²
            $table->decimal('sale_price', 14, 2)->nullable(); // baht
            $table->decimal('rent_price', 12, 2)->nullable(); // baht per month
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('remark')->nullable(); // internal note (keys, owner's wishes), never published
            $table->boolean('is_sale')->default(false);
            $table->boolean('is_rent')->default(false);
            $table->boolean('is_furnished')->default(false);
            $table->boolean('is_pet')->default(false); // pets allowed
            $table->boolean('is_published')->default(true); // shown on the website once there is one
            $table->softDeletes();
            $table->timestamps();
            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
