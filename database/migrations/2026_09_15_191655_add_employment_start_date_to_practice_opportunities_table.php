<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_opportunities', function (Blueprint $table) {
            $table->date('employment_start_date')
                ->nullable()
                ->after('customer_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('practice_opportunities', function (Blueprint $table) {
            $table->dropColumn('employment_start_date');
        });
    }
};