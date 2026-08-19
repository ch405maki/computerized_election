<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('voting_thresholds', function (Blueprint $table) {
            $table->id(); 
            
            $table->foreignId('election_id')
                ->constrained()
                ->onDelete('cascade'); 
                
            $table->decimal('required_percentage', 5, 2)->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_thresholds');
    }
};