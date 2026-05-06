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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id('medicine_id');

            $table->string('generic_name'); // e.g. Amoxicillin
            $table->string('brand_name')->nullable(); // e.g. Amoxil

            $table->text('indications')->nullable(); // what it treats
            $table->text('contraindications')->nullable(); // who should NOT take it

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('generic_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
