<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voters', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('student_number')->unique();
            $table->string('full_name');
            $table->string('student_year'); // Example: '1st Year', '2nd Year'
            $table->string('class_type'); // Example: 'Regular', 'Irregular'
            $table->enum('sex', ['Male', 'Female', 'Other']);
            $table->string('password'); // Hashed password
            $table->timestamps(); // Created_at & Updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voters');
    }
};

