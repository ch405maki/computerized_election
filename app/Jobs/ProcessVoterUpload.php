<?php

namespace App\Jobs;

use App\Imports\VoterImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessVoterUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $importId;
    public $filePath;

    // We can increase the timeout for large files
    public $timeout = 600; 

    public function __construct($importId, $filePath)
    {
        $this->importId = $importId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        // 1. Process the entire file in chunks inside this single background worker
        Excel::import(new VoterImport(), $this->filePath);

        // 2. The worker will ONLY reach this line when every single row is inserted
        Cache::put("import_status_{$this->importId}", 'completed', now()->addHours(1));

        // 3. Clean up the temporary Excel file from the server
        if (Storage::exists($this->filePath)) {
            Storage::delete($this->filePath);
        }
    }

    public function failed(\Throwable $exception)
    {
        // Optional: Update cache if it crashes so the frontend doesn't hang forever
        Cache::put("import_status_{$this->importId}", 'failed', now()->addHours(1));
    }
}