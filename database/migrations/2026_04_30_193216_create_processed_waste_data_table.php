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
        Schema::create('processed_waste_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_processed_waste')->constrained('processed_waste');
            $table->foreignId('id_user')->constrained('users');
            $table->decimal('measured_qty', 18, 4);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_waste_data');
    }
};
