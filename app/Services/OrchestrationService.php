<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrchestrationService
{
    private string $n8nWebhookUrl;
    private int $timeout;
    private int $maxRetries;
    private int $retryDelay;

    public function __construct(
        private TaskReadinessService $taskReadinessService
    ) {
        $this->n8nWebhookUrl = config('services.n8n.webhook_url');
        $this->timeout = config('services.n8n.timeout', 10);
        $this->maxRetries = config('services.n8n.max_retries', 3);
        $this->retryDelay = config('services.n8n.retry_delay', 2);
    }

    /**
     * Trigger n8n orchestration workflow with retry logic
     */
    public function triggerWorkflow(Project $project, int $attempt = 1): bool
    {
        // Check if project is paused
        if ($project->orchestration_status === 'paused') {
            Log::info('Orchestration paused, skipping trigger', [
                'project_id' => $project->id
            ]);
            return false;
        }

        // ========================================
        // PRE-MARKING: Get ready tasks and mark as in_progress BEFORE triggering n8n
        // ========================================
        // This is critical for cascading prevention - tasks must be marked in_progress
        // before n8n processes them, so CheckForReadyTasks can detect them
        $readyTasks = $this->taskReadinessService->getReadyTasks($project);

        if ($readyTasks->isEmpty()) {
            Log::info('No ready tasks to trigger', [
                'project_id' => $project->id
            ]);
            return false;
        }

        $taskIds = $readyTasks->pluck('id')->toArray();
        $now = now();

        Log::info('Pre-marking tasks as in_progress before n8n trigger', [
            'project_id' => $project->id,
            'task_count' => $readyTasks->count(),
            'task_ids' => $taskIds
        ]);

        // Mark all ready tasks as in_progress
        foreach ($readyTasks as $task) {
            $task->update([
                'status' => 'in_progress',
                'started_at' => $now
            ]);
        }

        try {
            Log::info('Triggering n8n workflow', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'webhook_url' => $this->n8nWebhookUrl,
                'attempt' => $attempt,
                'max_retries' => $this->maxRetries,
                'pre_marked_tasks' => $taskIds
            ]);

            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, $this->retryDelay * 1000, function ($exception, $request) {
                    // Only retry on connection errors, not on 4xx responses
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->post($this->n8nWebhookUrl, [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'user_id' => $project->user_id,
                    'trigger_source' => 'laravel_automatic',
                    'attempt' => $attempt,
                    // Send full task data (tasks already marked in_progress)
                    'tasks' => $readyTasks->map(function($task) {
                        return [
                            'id' => $task->id,
                            'name' => $task->name,
                            'description' => $task->description,
                            'type' => $task->type,
                            'status' => 'in_progress',
                            'estimated_hours' => $task->estimated_hours,
                            'dependencies' => $task->metadata['dependencies'] ?? [],
                            'metadata' => $task->metadata,
                        ];
                    })->values()->toArray(),
                    'callback_url' => url('/api/orchestration/batch-callback'),
                ]);

            if ($response->successful()) {
                // Update orchestration metadata
                $project->increment('total_orchestration_runs');

                $metadata = $project->orchestration_metadata ?? [];
                $metadata['last_trigger_at'] = $now->toISOString();
                $metadata['last_trigger_response'] = $response->json();
                $metadata['last_trigger_attempt'] = $attempt;
                $metadata['last_batch_task_ids'] = $taskIds;

                $project->update([
                    'orchestration_metadata' => $metadata
                ]);

                Log::info('n8n workflow triggered successfully', [
                    'project_id' => $project->id,
                    'response' => $response->json(),
                    'total_runs' => $project->total_orchestration_runs,
                    'attempt' => $attempt,
                    'batch_task_ids' => $taskIds
                ]);

                return true;
            }

            // ========================================
            // REVERT ON FAILURE: If n8n webhook fails, revert tasks back to pending
            // ========================================
            Log::warning('n8n trigger failed - reverting tasks to pending status', [
                'project_id' => $project->id,
                'task_ids' => $taskIds,
                'status_code' => $response->status()
            ]);

            foreach ($readyTasks as $task) {
                $task->update([
                    'status' => 'pending',
                    'started_at' => null
                ]);
            }

            // Handle different HTTP error codes
            $this->handleTriggerFailure($project, $response->status(), $response->body(), $attempt);

            return false;

        } catch (\Exception $e) {
            // ========================================
            // REVERT ON EXCEPTION: If exception occurs, revert tasks back to pending
            // ========================================
            Log::error('Exception triggering n8n workflow - reverting tasks to pending', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $attempt,
                'task_ids' => $taskIds
            ]);

            foreach ($readyTasks as $task) {
                $task->update([
                    'status' => 'pending',
                    'started_at' => null
                ]);
            }

            // Update metadata with failure info
            $metadata = $project->orchestration_metadata ?? [];
            $metadata['last_error'] = [
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
                'attempt' => $attempt,
                'reverted_task_ids' => $taskIds
            ];
            $project->update(['orchestration_metadata' => $metadata]);

            return false;
        }
    }

    /**
     * Handle workflow trigger failures
     */
    private function handleTriggerFailure(Project $project, int $statusCode, string $body, int $attempt): void
    {
        Log::error('n8n workflow trigger failed', [
            'project_id' => $project->id,
            'status' => $statusCode,
            'body' => $body,
            'attempt' => $attempt
        ]);

        $metadata = $project->orchestration_metadata ?? [];
        $metadata['last_trigger_failure'] = [
            'status_code' => $statusCode,
            'body' => $body,
            'timestamp' => now()->toISOString(),
            'attempt' => $attempt
        ];

        // If we've hit max retries, mark orchestration as failed
        if ($attempt >= $this->maxRetries) {
            $project->update([
                'orchestration_status' => 'failed',
                'orchestration_metadata' => $metadata
            ]);

            Log::critical('Project orchestration failed after max retries', [
                'project_id' => $project->id,
                'max_retries' => $this->maxRetries
            ]);
        } else {
            $project->update(['orchestration_metadata' => $metadata]);
        }
    }

    /**
     * Check if project orchestration is complete
     */
    public function isOrchestrationComplete(Project $project): bool
    {
        $pendingTasks = $project->tasks()
            ->where('status', 'pending')
            ->count();

        $inProgressTasks = $project->tasks()
            ->where('status', 'in_progress')
            ->count();

        $isComplete = ($pendingTasks === 0 && $inProgressTasks === 0);

        if ($isComplete) {
            Log::info('Project orchestration completed', [
                'project_id' => $project->id,
                'total_tasks' => $project->tasks()->count(),
                'completed_tasks' => $project->tasks()->where('status', 'completed')->count()
            ]);

            $project->update([
                'orchestration_status' => 'completed',
                'orchestration_completed_at' => now()
            ]);
        }

        return $isComplete;
    }
}
