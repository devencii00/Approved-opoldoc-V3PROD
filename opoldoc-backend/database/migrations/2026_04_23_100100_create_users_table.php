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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');

           
            $table->unsignedBigInteger('parent_user_id')->nullable();
            $table->foreign('parent_user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('email')->unique()->nullable();
            $table->string('password_hash')->nullable();

            $table->enum('role', ['admin', 'doctor', 'receptionist', 'patient']);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');

            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('middlename')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('sex', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_number', 20)->nullable();

            $table->string('license_number')->nullable();
            $table->string('specialization')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('prof_path')->nullable();
            $table->string('employee_number')->nullable();
            $table->date('hire_date')->nullable();

            $table->boolean('is_dependent')->default(false);
            $table->boolean('account_activated')->default(false);
            $table->enum('relationship', ['mother', 'father', 'guardian'])->nullable();

            $table->boolean('is_first_login')->default(true);
            $table->string('password_reset_token')->nullable();
            $table->dateTime('password_reset_expires_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('role');
            $table->index('status');
            $table->index('parent_user_id');
            $table->index('is_dependent');
            $table->index('account_activated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
