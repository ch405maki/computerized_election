<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Election;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $now = now();
    $currentTime = $now->format('H:i:s');

    // 1. COMPLETE: Mark elections as completed if past the overall end_date
    Election::where('end_date', '<', $now)
        ->where('status', '!=', 'completed')
        ->update(['status' => 'completed']);

    // 2. ACTIVE: Mark elections as active if they are within the start/end date 
    // AND (they have no daily time restrictions OR the current time is within the daily schedule)
    Election::where('start_date', '<=', $now)
        ->where('end_date', '>=', $now)
        ->where('status', '!=', 'active')
        ->where(function ($query) use ($currentTime) {
            $query->whereNull('voting_start_time')
                ->orWhere(function ($q) use ($currentTime) {
                    $q->whereTime('voting_start_time', '<=', $currentTime)
                    ->whereTime('voting_end_time', '>=', $currentTime);
                });
        })
        ->update(['status' => 'active']);

    // 3. CLOSE: Mark elections as close if they are within the start/end date 
    // BUT the current time is outside the daily scheduled hours
    Election::where('start_date', '<=', $now)
        ->where('end_date', '>=', $now)
        ->where('status', '!=', 'close')
        ->whereNotNull('voting_start_time')
        ->whereNotNull('voting_end_time')
        ->where(function ($query) use ($currentTime) {
            $query->whereTime('voting_start_time', '>', $currentTime)
                ->orWhereTime('voting_end_time', '<', $currentTime);
        })
        ->update(['status' => 'close']);

})->everyMinute();