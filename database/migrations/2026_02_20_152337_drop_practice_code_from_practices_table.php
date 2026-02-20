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
        Schema::table('practices', function (Blueprint $table) {
            $table->dropUnique('practices_practice_code_unique');
            $table->dropColumn('practice_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->string('practice_code')->nullable()->unique();
        });
    }
};
