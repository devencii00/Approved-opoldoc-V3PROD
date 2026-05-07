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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id');

            $table->unsignedBigInteger('appointment_id');
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->cascadeOnDelete();

            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->enum('discount_type', ['none', 'senior', 'pwd'])->default('none');

            $table->enum('payment_mode', ['cash', 'gcash'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');

            $table->string('reference_number')->nullable();
            $table->string('receipt_path')->nullable();
            $table->dateTime('transaction_datetime')->nullable();
            $table->dateTime('visit_datetime')->nullable();

            $table->text('diagnosis')->nullable();
            $table->text('treatment_notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
