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
        Schema::create('medical_backgrounds', function (Blueprint $table) {
            $table->id('medical_background_id');

            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->enum('category', ['allergy_food', 'allergy_drug', 'condition']);
            $table->string('name'); // e.g. Asthma, Penicillin allergy

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('patient_id');
            $table->index('category');
            $table->index(['patient_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_backgrounds');
    }
};
