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
        Schema::create('data_waste_out', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_waste_out_data')->constrained('waste_out_data');
            $table->boolean('is_processed_waste');
            $table->foreignId('id_waste_sub_category')->nullable()->constrained('waste_sub_category');
            $table->foreignId('id_processed_waste')->nullable()->constrained('processed_waste');
            $table->decimal('measured_qty', 18, 4);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_waste_out');
    }
};
