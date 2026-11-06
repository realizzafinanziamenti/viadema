<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->string('atc')->nullable();
            $table->string('atc_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('practices', fn (Blueprint $table) => $table->dropColumn(['atc', 'atc_type']));
    }
};
