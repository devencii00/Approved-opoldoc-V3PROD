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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id('prescription_id');

            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('doctor_id');

            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('user_id')->on('users')->cascadeOnDelete();

            $table->text('notes')->nullable();
            $table->dateTime('prescribed_datetime')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
