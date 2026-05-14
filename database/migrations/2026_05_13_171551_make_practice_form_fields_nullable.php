<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->foreignId('product_type_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->change();

            $table->decimal('renewability_percentage', 5, 2)->nullable()->change();
            $table->decimal('percentage_alert', 5, 2)->nullable()->change();

            $table->boolean('is_renewal')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->foreignId('product_type_id')->nullable(false)->change();
            $table->foreignId('user_id')->nullable(false)->change();

            $table->decimal('renewability_percentage', 5, 2)->default(40.00)->nullable(false)->change();
            $table->decimal('percentage_alert', 5, 2)->default(35.00)->nullable(false)->change();

            $table->boolean('is_renewal')->default(false)->nullable(false)->change();
        });
    }
};