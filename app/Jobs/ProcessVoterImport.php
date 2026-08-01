<?php

namespace App\Jobs;

use App\Models\Voter;
use App\Models\VoterStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class ProcessVoterImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $importId;
    public $filePath;
    public $timeout = 600;

    public function __construct($importId, $filePath)
    {
        $this->importId = $importId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            $fullPath = Storage::path($this->filePath);
            $chunkSize = 1000;
            
            $votersBatch = [];
            $studentNumbersBatch = [];

            SimpleExcelReader::create($fullPath, 'xlsx')
                ->formatHeadersUsing(fn($header) => str_replace(' ', '_', strtolower(trim((string) $header)))) 
                ->getRows()
                ->each(function (array $rowProperties) use (&$votersBatch, &$studentNumbersBatch, $chunkSize) {
                    
                    $studentNumber = trim((string) ($rowProperties['student_number'] ?? ''));
                    
                    if (empty($studentNumber)) {
                        return;
                    }

                    $firstName = trim((string) ($rowProperties['first_name'] ?? ''));
                    $lastName  = trim((string) ($rowProperties['last_name'] ?? ''));

                    if (empty($firstName) || empty($lastName)) {
                        return; 
                    }

                    $middleName = trim((string) ($rowProperties['middle_name'] ?? ''));

                    $rawPassword = !empty($rowProperties['password']) 
                        ? trim((string) $rowProperties['password']) 
                        : $studentNumber;

                    $votersBatch[] = [
                        'student_number' => $studentNumber,
                        'first_name'     => $firstName,
                        'last_name'      => $lastName,
                        'middle_name'    => $middleName !== '' ? $middleName : null,
                        'sex'            => trim((string) ($rowProperties['sex'] ?? 'Other')),
                        'dob'            => !empty($rowProperties['dob']) ? trim((string) $rowProperties['dob']) : null,
                        'student_year'   => trim((string) ($rowProperties['student_year'] ?? '')),                        
                        'password'       => Hash::make($rawPassword, ['rounds' => 8]), 
                        'created_at'     => now(),
                        'updated_at'     => now(),
                        'deleted_at'     => null,
                    ];

                    $studentNumbersBatch[] = $studentNumber;

                    if (count($votersBatch) >= $chunkSize) {
                        $this->processChunk($votersBatch, $studentNumbersBatch);
                        
                        $votersBatch = [];
                        $studentNumbersBatch = [];
                    }
                });

            if (!empty($votersBatch)) {
                $this->processChunk($votersBatch, $studentNumbersBatch);
            }

            Cache::put("import_status_{$this->importId}", 'completed', now()->addHours(1));

            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

        } catch (\Throwable $e) {
            Log::error("Voter Import Failed [{$this->importId}]: " . $e->getMessage());
            Cache::put("import_status_{$this->importId}", 'failed', now()->addHours(1));
            
            throw $e; 
        } 
    }

    private function processChunk(array $votersBatch, array $studentNumbersBatch)
    {
        DB::transaction(function () use ($votersBatch, $studentNumbersBatch) {
            Voter::upsert(
                $votersBatch,
                ['student_number'], 
                ['first_name', 'last_name', 'middle_name', 'sex', 'dob', 'student_year', 'password', 'updated_at', 'deleted_at'] 
            );

            $insertedVoters = Voter::whereIn('student_number', $studentNumbersBatch)->pluck('id');

            $statusesBatch = [];
            foreach ($insertedVoters as $id) {
                $statusesBatch[] = [
                    'voter_id'   => $id,
                    'activated'  => true,
                    'voted'      => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($statusesBatch)) {
                VoterStatus::upsert(
                    $statusesBatch, 
                    ['voter_id'], 
                    ['activated', 'voted', 'updated_at']
                );
            }
        });
    }

    public function failed(\Throwable $exception)
    {
        if (Storage::exists($this->filePath)) {
            Storage::delete($this->filePath);
        }
    }
}