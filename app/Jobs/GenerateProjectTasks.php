<?php

namespace App\Jobs;

use App\Services\AIEngineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateProjectTasks implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 200;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $componentId,
        public array $context,
        public ?array $aiAnalysis
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AIEngineService $aiService): void
    {
        try {
            Log::info('Background job: Starting task generation', [
                'component_id' => $this->componentId,
                'context' => $this->context
            ]);

            $tempProjectId = 'temp_' . Str::uuid();

            // Call Python service for task generation
            $result = $aiService->generateTasks(
                $tempProjectId,
                $this->context['user_id'],
                $this->context,
                $this->aiAnalysis
            );

            if ($result && isset($result['tasks']) && !empty($result['tasks'])) {
                // Store the result in cache with component ID as key
                cache()->put(
                    "task_generation_{$this->componentId}",
                    [
                        'status' => 'completed',
                        'tasks' => $result['tasks'],
                        'dependencies' => $result['dependencies'] ?? [],
                        'metadata' => $result['metadata'] ?? null,
                    ],
                    now()->addMinutes(10)
                );

                Log::info('Background job: Task generation completed', [
                    'component_id' => $this->componentId,
                    'task_count' => count($result['tasks'])
                ]);
            } else {
                // Mark as failed - will trigger fallback
                cache()->put(
                    "task_generation_{$this->componentId}",
                    [
                        'status' => 'failed',
                        'error' => 'Python service returned no tasks'
                    ],
                    now()->addMinutes(10)
                );

                Log::warning('Background job: Task generation failed - no tasks returned', [
                    'component_id' => $this->componentId
                ]);
            }

        } catch (\Exception $e) {
            // Store error in cache
            cache()->put(
                "task_generation_{$this->componentId}",
                [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ],
                now()->addMinutes(10)
            );

            Log::error('Background job: Task generation exception', [
                'component_id' => $this->componentId,
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }
}
