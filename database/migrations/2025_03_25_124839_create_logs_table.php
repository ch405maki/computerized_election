<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Nullable FK to users
            $table->foreignId('voter_id')->nullable()->constrained('voters')->onDelete('set null'); // Nullable FK to voters
            $table->string('action', 255); // Example: 'Voted', 'Updated Candidate', 'Logged In'
            $table->timestamp('timestamp'); // Default to current time
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
