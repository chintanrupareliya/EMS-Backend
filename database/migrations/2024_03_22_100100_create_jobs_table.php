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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            
            $table->string('title');
            $table->text('description');
            $table->string('salary')->nullable(); 

            $table->string('employment_type')->nullable(); 
            $table->text('required_experience')->nullable();
            $table->text('required_skills')->nullable();

            $table->dateTime('posted_date');
            $table->dateTime('expiry_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
