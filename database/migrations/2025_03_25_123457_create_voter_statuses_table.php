<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voter_statuses', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('voter_id')->constrained('voters')->onDelete('cascade');
            $table->boolean('voted')->default(false); // 1 = Yes, 0 = No
            $table->boolean('activated')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voter_statuses');
    }
};

