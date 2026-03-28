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
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessVoterImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $importId;
    public $filePath;
    public $timeout = 600; // 10 minutes

    public function __construct($importId, $filePath)
    {
        $this->importId = $importId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            $fullPath = Storage::path($this->filePath);

            // Configure Reader for maximum performance
            $reader = IOFactory::createReaderForFile($fullPath);
            $reader->setReadDataOnly(true); // Crucial for memory management

            $spreadsheet = $reader->load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();

            $headerMap = [];
            $votersBatch = [];
            $studentNumbersBatch = [];
            $isFirstRow = true;
            $chunkSize = 1000;

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $columnLetter => $cell) {
                    $rowData[$columnLetter] = $cell->getValue();
                }

                // Map headers dynamically
                if ($isFirstRow) {
                    foreach ($rowData as $colLetter => $value) {
                        if (!empty($value)) {
                            $headerMap[strtolower(trim($value))] = $colLetter;
                        }
                    }
                    $isFirstRow = false;
                    continue;
                }

                if (!isset($headerMap['student_number'])) {
                    throw new \Exception("Missing 'student_number' column header in Excel file.");
                }

                $studentNumber = trim($rowData[$headerMap['student_number']] ?? '');
                $firstName = trim($rowData[$headerMap['first_name'] ?? ''] ?? '');
                $lastName = trim($rowData[$headerMap['last_name'] ?? ''] ?? '');

                if (empty($studentNumber) || empty($firstName) || empty($lastName)) {
                    continue;
                }

                $rawPassword = $rowData[$headerMap['password'] ?? ''] ?? $studentNumber;

                $votersBatch[] = [
                    'student_number' => $studentNumber,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'middle_name' => !empty($rowData[$headerMap['middle_name'] ?? '']) ? trim($rowData[$headerMap['middle_name'] ?? '']) : null,
                    'sex' => trim($rowData[$headerMap['sex'] ?? ''] ?? 'Other'),
                    'dob' => !empty($rowData[$headerMap['dob'] ?? '']) ? trim($rowData[$headerMap['dob'] ?? '']) : null,
                    'student_year' => trim($rowData[$headerMap['student_year'] ?? ''] ?? ''),


                    'password' => Hash::make($rawPassword, ['rounds' => 8]),

                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $studentNumbersBatch[] = $studentNumber;

                // Process Chunk
                if (count($votersBatch) >= $chunkSize) {
                    $this->processChunk($votersBatch, $studentNumbersBatch);
                    $votersBatch = [];
                    $studentNumbersBatch = [];
                }
            }

            // Process remaining rows
            if (!empty($votersBatch)) {
                $this->processChunk($votersBatch, $studentNumbersBatch);
            }

            Cache::put("import_status_{$this->importId}", 'completed', now()->addHours(1));

        } catch (\Exception $e) {
            Log::error("Voter Import Failed [{$this->importId}]: " . $e->getMessage());
            Cache::put("import_status_{$this->importId}", 'failed', now()->addHours(1));
        } finally {
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }

    /**
     * Helper method to handle database insertions safely
     */
    private function processChunk(array $votersBatch, array $studentNumbersBatch)
    {
        // Wrap the entire chunk process in a single Database Transaction
        DB::transaction(function () use ($votersBatch, $studentNumbersBatch) {

            Voter::upsert(
                $votersBatch,
                ['student_number'],
                ['first_name', 'last_name', 'middle_name', 'sex', 'dob', 'student_year', 'password', 'updated_at']
            );

            $insertedVoters = Voter::whereIn('student_number', $studentNumbersBatch)->pluck('id');

            $statusesBatch = [];
            foreach ($insertedVoters as $id) {
                $statusesBatch[] = [
                    'voter_id' => $id,
                    'activated' => true,
                    'voted' => false,
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
}