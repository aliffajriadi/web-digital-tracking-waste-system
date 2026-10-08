<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_out_method', function (Blueprint $table) {
            $table->boolean('is_selling')->default(false)->after('description');
        });

        // Sebelumnya metode "jual" dideteksi dari id = 1 / nama. Tandai data lama berdasarkan nama.
        DB::table('waste_out_method')
            ->where('name', 'like', '%jual%')
            ->orWhere('name', 'like', '%sell%')
            ->update(['is_selling' => true]);
    }

    public function down(): void
    {
        Schema::table('waste_out_method', function (Blueprint $table) {
            $table->dropColumn('is_selling');
        });
    }
};
