<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ImportCompleted implements ShouldQueue
{
    use Queueable;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $importId;

    public function __construct($importId)
    {
        $this->importId = $importId;
    }

    public function handle()
    {
        // This will only run once all import chunks are fully processed
        Cache::put("import_status_{$this->importId}", 'completed', now()->addHours(1));
    }
}
