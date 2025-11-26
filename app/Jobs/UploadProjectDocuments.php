<?php

namespace App\Jobs;

use App\Services\AIEngineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadProjectDocuments implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $componentId,
        public string $projectId,
        public string $projectName,
        public array $fileMetadata // Array of stored file info
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AIEngineService $aiService): void
    {
        try {
            Log::info('Background job: Starting document upload', [
                'component_id' => $this->componentId,
                'project_id' => $this->projectId,
                'file_count' => count($this->fileMetadata)
            ]);

            // Reconstruct UploadedFile objects from temp storage
            $uploadedFiles = [];
            foreach ($this->fileMetadata as $fileInfo) {
                $tempPath = Storage::disk('local')->path($fileInfo['temp_path']);

                if (!file_exists($tempPath)) {
                    Log::error('Temp file not found', ['path' => $tempPath]);
                    continue;
                }

                $uploadedFiles[] = new \Illuminate\Http\UploadedFile(
                    $tempPath,
                    $fileInfo['original_name'],
                    $fileInfo['mime_type'],
                    null,
                    true // test mode
                );
            }

            if (empty($uploadedFiles)) {
                throw new \Exception('No valid files to upload');
            }

            // Upload to Python service
            $result = $aiService->uploadDocuments($this->projectId, $uploadedFiles);

            if ($result && isset($result['status']) && $result['status'] === 'success') {
                // Store success result in cache
                cache()->put(
                    "document_upload_{$this->componentId}",
                    [
                        'status' => 'completed',
                        'project_id' => $this->projectId,
                        'document_count' => $result['document_count'] ?? count($uploadedFiles),
                        'partial_success' => $result['partial_success'] ?? false,
                        'failed_files' => $result['failed_files'] ?? [],
                    ],
                    now()->addMinutes(10)
                );

                Log::info('Background job: Document upload completed', [
                    'component_id' => $this->componentId,
                    'project_id' => $this->projectId,
                    'document_count' => $result['document_count'] ?? count($uploadedFiles)
                ]);
            } else {
                throw new \Exception('Document upload returned no success status');
            }

            // Cleanup: Delete temp files
            foreach ($this->fileMetadata as $fileInfo) {
                Storage::disk('local')->delete($fileInfo['temp_path']);
            }

        } catch (\Exception $e) {
            // Store error in cache
            cache()->put(
                "document_upload_{$this->componentId}",
                [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ],
                now()->addMinutes(10)
            );

            Log::error('Background job: Document upload exception', [
                'component_id' => $this->componentId,
                'project_id' => $this->projectId,
                'error' => $e->getMessage()
            ]);

            // Cleanup temp files even on failure
            foreach ($this->fileMetadata as $fileInfo) {
                Storage::disk('local')->delete($fileInfo['temp_path']);
            }

            throw $e; // Re-throw to mark job as failed
        }
    }
}
