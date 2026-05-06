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
    Schema::create('chatbot_system', function (Blueprint $table) {
    $table->id();
    
    // Self-referential parent with cascade delete
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->foreign('parent_id')
          ->references('id')
          ->on('chatbot_system')
          ->onDelete('cascade');
    
    // Content
    $table->string('button_text');
    $table->text('response_text');
    
    // Display rules
    $table->boolean('is_starting_option')->default(false);
    $table->integer('sort_order')->default(0);
    
    $table->timestamps();
    
    // Indexes
    $table->index('parent_id');
    $table->index('is_starting_option');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::dropIfExists('chatbot_system');
    }
};
