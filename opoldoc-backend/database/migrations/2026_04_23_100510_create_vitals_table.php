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
          Schema::create('vitals', function (Blueprint $table) {
            $table->id('vital_id');

           
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('appointment_id')->nullable();

       
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('pulse_rate')->nullable();

         
            $table->dateTime('recorded_at')->useCurrent();

          
            $table->index('patient_id');
            $table->index('appointment_id');

           
            $table->foreign('patient_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');

            $table->foreign('appointment_id')
                ->references('appointment_id')->on('appointments')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vitals');
    }
};
