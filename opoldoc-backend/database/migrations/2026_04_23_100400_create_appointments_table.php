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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');

            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('patient_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();

            $table->dateTime('appointment_datetime')->nullable();
            $table->enum('appointment_type', ['walk_in', 'scheduled']);
            $table->enum('status', ['pending', 'confirmed', 'consulted' ,  'completed', 'cancelled', 'no_show'])->default('pending');

            $table->text('reason_for_visit')->nullable();
            $table->integer('priority_level')->default(5);
            $table->dateTime('check_in_time')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
