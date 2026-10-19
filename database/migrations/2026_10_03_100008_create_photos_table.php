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
        // Images of anything: properties (gallery, first is the cover). On the public disk.
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->morphs('photoable'); // type is a morph-map alias: property
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // uploader
            $table->string('path'); // on the "public" disk
            $table->string('name'); // original file name
            $table->string('mime', 100);
            $table->unsignedInteger('size'); // bytes
            $table->unsignedSmallInteger('width')->nullable(); // pixels, so a page can reserve the space before it loads
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('alt')->nullable(); // image description (accessibility, SEO)
            $table->unsignedSmallInteger('position')->default(0); // gallery order, lowest first
            $table->boolean('is_cover')->default(false); // the one shown in lists; one per photoable
            $table->timestamps();
            $table->index(['photoable_type', 'photoable_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
