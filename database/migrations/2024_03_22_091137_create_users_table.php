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
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('type', ['SA', 'CA', 'E', 'C'])->default('C')->comment('Super Admin,Employee,Company Admin,Employee');
            $table->unsignedBigInteger('company_id')->nullable(); // Allow null for non-company users
            $table->text('address')->nullable(); 
            $table->string('city')->nullable(); 
            $table->date('dob')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->foreign('created_by')->references('code')->on('modules');
            $table->foreign('updated_by')->references('code')->on('modules');
            $table->softDeletes();
            $table->timestamps();
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
