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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id('conversation_id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');

            $table->unsignedBigInteger('conversation_id');
            $table->foreign('conversation_id')->references('conversation_id')->on('conversations')->cascadeOnDelete();

            $table->enum('sender', ['user', 'bot']);
            $table->text('message_text');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
