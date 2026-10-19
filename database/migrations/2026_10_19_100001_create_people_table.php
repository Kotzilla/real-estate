<?php

use App\Enums\PersonType;
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
        // Contacts outside the company: owners (lessor / seller), customers (tenant / buyer) and outside agents.
        // Names, address and ID are kept in English and Thai, as the contracts print both.
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default(PersonType::Customer->value); // App\Enums\PersonType
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('first_name_th')->nullable();
            $table->string('last_name_th')->nullable();
            $table->string('nickname', 50)->nullable();
            $table->string('nationality', 50)->nullable(); // e.g. Thai, British
            $table->string('id_card_no', 20)->nullable(); // Thai national ID
            $table->string('passport_no', 30)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('line_id', 100)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('address_th')->nullable();
            $table->string('bank_name', 100)->nullable(); // owners: where the rent is paid
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_no', 30)->nullable();
            $table->text('remark')->nullable(); // internal note, never printed
            $table->date('birth_on')->nullable(); // the contracts print the age
            $table->softDeletes();
            $table->timestamps();
            $table->index(['type', 'first_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
