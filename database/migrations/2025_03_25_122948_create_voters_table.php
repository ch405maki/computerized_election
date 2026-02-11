<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voters', function (Blueprint $table) {
            $table->id(); 
            $table->string('student_number')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->enum('sex', ['Male', 'Female', 'Other']);
            $table->string('dob');
            $table->string('student_year');
            $table->string('password');
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voters');
    }
};