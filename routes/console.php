<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Election;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatically update expired elections every day at midnight
Schedule::call(function () {
    Election::where('end_date', '<', now())
        ->where('status', '!=', 'completed')
        ->update(['status' => 'completed']);
})->dailyAt('00:00');