<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->enum('status', ['active', 'completed', 'upcoming', 'close'])
                ->default('upcoming')
                ->change();

            $table->dateTime('voting_start_time')->nullable()->after('end_date');
            $table->dateTime('voting_end_time')->nullable()->after('voting_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->enum('status', ['active', 'completed', 'upcoming'])
                ->default('upcoming')
                ->change();

            $table->dropColumn(['voting_start_time', 'voting_end_time']);
        });
    }
};