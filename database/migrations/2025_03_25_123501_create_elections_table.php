<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name'); // Election Name
            $table->dateTime('start_date'); // Start Date
            $table->dateTime('end_date'); // End Date
            $table->enum('status', ['active', 'completed', 'upcoming'])->default('upcoming'); // Status
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
