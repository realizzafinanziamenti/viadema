<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_opportunities', function (Blueprint $table) {
            $table
                ->string('acquisition_channel')
                ->nullable()
                ->after('customer_id');

            $table->index('acquisition_channel');
        });
    }

    public function down(): void
    {
        Schema::table('practice_opportunities', function (Blueprint $table) {
            $table->dropIndex(['acquisition_channel']);
            $table->dropColumn('acquisition_channel');
        });
    }
};