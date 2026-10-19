<?php

use App\Enums\ContractStatus;
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
        // A lease or a sale of one property to one customer, printed in English and Thai (contracts/print). The
        // owner is the property's; the commission is worked out on save (App\Models\Contract::calculate).
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('people'); // the tenant / buyer
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // the agent who closed it
            $table->foreignId('witness_id')->nullable()->constrained('users')->nullOnDelete(); // signs as witness
            $table->string('code', 20)->unique(); // e.g. RE-2026-0001
            $table->string('type', 10); // App\Enums\ContractType: rent | sale
            $table->string('status', 20)->default(ContractStatus::Draft->value); // App\Enums\ContractStatus
            $table->unsignedTinyInteger('months')->nullable(); // rent: the lease term
            $table->decimal('price', 14, 2); // rent: baht per month; sale: the sale price
            $table->decimal('deposit', 14, 2)->default(0); // rent: security deposit; sale: paid on signing
            $table->decimal('advance', 14, 2)->default(0); // rent: rent paid in advance on signing
            $table->unsignedTinyInteger('payment_day')->nullable(); // rent: due day of each month
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->decimal('commission', 14, 2)->default(0); // baht, from price × months (rent) or price (sale)
            $table->text('terms')->nullable(); // extra conditions, printed after the standard clauses
            $table->text('remark')->nullable(); // internal note, never printed
            $table->date('signed_on'); // the date printed on the contract
            $table->date('start_on'); // rent: move-in; sale: the agreement starts
            $table->date('end_on')->nullable(); // rent: lease ends; sale: transfer at the Land Office
            $table->softDeletes();
            $table->timestamps();
            $table->index(['status', 'end_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
