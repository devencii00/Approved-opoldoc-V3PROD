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
        Schema::create('queues', function (Blueprint $table) {
            $table->id('queue_id');

            $table->unsignedBigInteger('appointment_id');
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->cascadeOnDelete();

            $table->integer('queue_number')->nullable();
            $table->string('queue_code', 20)->nullable()->unique();
            $table->dateTime('queue_datetime')->nullable();
            $table->enum('status', ['waiting', 'serving', 'done', 'cancelled','no_show'])->default('waiting');

            $table->integer('priority_level')->default(5);

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
        Schema::dropIfExists('queues');
    }
};
