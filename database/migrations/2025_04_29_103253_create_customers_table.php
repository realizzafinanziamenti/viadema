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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('customer_type_id')->nullable()->constrained('customer_types')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 24);
            $table->string('email')->unique()->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('tax_id', 16)->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('customer_status');  // LEAD or CUSTOMER
            $table->string('lead_source')->nullable(); // Example: 'Tik Tok', 'Meta', 'Search Engine', 'Referral', etc.
            $table->string('lead_status')->default('new'); // ACTIVE or LOST
            $table->string('lead_communication')->nullable(); // Example: 'Email', 'Phone', etc.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
