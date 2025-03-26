<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('candidate_code')->unique();
            $table->foreignId('position_id')->constrained('positions')->onDelete('cascade'); // FK to positions table
            $table->string('candidate_name');
            $table->string('candidate_party')->nullable(); // Example: 'Independent', 'Party A'
            $table->string('candidate_picture')->nullable(); // Image file path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};

