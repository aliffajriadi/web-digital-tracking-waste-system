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
        Schema::create('waste_selling_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_waste_out_data')->constrained('waste_out_data');
            $table->decimal('total_revenue', 18, 2);
            $table->foreignId('id_buyer')->constrained('data_collector_buyer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_selling_data');
    }
};
