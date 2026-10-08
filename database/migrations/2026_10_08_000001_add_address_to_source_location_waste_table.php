<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('source_location_waste', 'address')) {
            Schema::table('source_location_waste', function (Blueprint $table) {
                $table->text('address')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('source_location_waste', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
