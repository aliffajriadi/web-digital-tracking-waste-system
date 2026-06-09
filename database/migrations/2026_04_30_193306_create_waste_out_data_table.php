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
        Schema::create('waste_out_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_waste_out_method')->constrained('waste_out_method');
            $table->foreignId('id_waste_destination')->nullable()->constrained('waste_destinations');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_out_data');
    }
};
