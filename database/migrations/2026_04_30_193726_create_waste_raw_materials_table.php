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
        Schema::create('waste_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_processed_waste_data')->constrained('processed_waste_data');
            $table->foreignId('id_waste_sub_category')->constrained('waste_sub_category');
            $table->decimal('measured_qty', 18, 4);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_raw_materials');
    }
};
