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

         
            $table->enum('type', ['senior', 'pwd', 'pregnant'])->nullable();

          
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            
            $table->string('document_path')->nullable();
          

            $table->text('remarks')->nullable();
         

           
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
