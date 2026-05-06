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
        Schema::create('patient_verifications', function (Blueprint $table) {
            $table->id('verification_id');

            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            // what is being verified
            $table->enum('type', ['senior', 'pwd', 'pregnant']);

            // request lifecycle
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            // 🔥 PROOF FILES (this is what you were missing)
            $table->string('document_path')->nullable();
            // e.g. storage/verifications/pwd_card.jpg

            $table->text('remarks')->nullable();
            // admin/receptionist notes

            // who processed it (important for audit)
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index('patient_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_verifications');
    }
};
