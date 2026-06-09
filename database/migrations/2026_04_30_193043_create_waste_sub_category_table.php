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
        Schema::create('waste_sub_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_waste_category')->constrained('waste_category');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('id_waste_b3_detail')->nullable()->constrained('waste_b3_detail');
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('id_unit_measured')->constrained('unit_measured');
            $table->decimal('default_measured_qty', 18, 4);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_sub_category');
    }
};
