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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id('item_id');

            $table->unsignedBigInteger('prescription_id');
            $table->foreign('prescription_id')
                ->references('prescription_id')
                ->on('prescriptions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('medicine_id')->nullable();
            $table->foreign('medicine_id')
                ->references('medicine_id')
                ->on('medicines')
                ->nullOnDelete();

            $table->string('medicine_name')->nullable();

            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->text('instructions')->nullable();

            $table->timestamps();

            $table->index('prescription_id');
            $table->index('medicine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
