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

    public function __construct()
    {
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

        try {
            Log::info('Triggering n8n workflow', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'webhook_url' => $this->n8nWebhookUrl,
                'attempt' => $attempt,
                'max_retries' => $this->maxRetries
            ]);

            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, $this->retryDelay * 1000, function ($exception, $request) {
                    // Only retry on connection errors, not on 4xx responses
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->post($this->n8nWebhookUrl, [
                    'project_id' => $project->id,
                    'trigger_source' => 'laravel_automatic',
                    'attempt' => $attempt
                ]);

            if ($response->successful()) {
                // Update orchestration metadata
                $project->increment('total_orchestration_runs');

                $metadata = $project->orchestration_metadata ?? [];
                $metadata['last_trigger_at'] = now()->toISOString();
                $metadata['last_trigger_response'] = $response->json();
                $metadata['last_trigger_attempt'] = $attempt;

                $project->update([
                    'orchestration_metadata' => $metadata
                ]);

                Log::info('n8n workflow triggered successfully', [
                    'project_id' => $project->id,
                    'response' => $response->json(),
                    'total_runs' => $project->total_orchestration_runs,
                    'attempt' => $attempt
                ]);

                return true;
            }

            // Handle different HTTP error codes
            $this->handleTriggerFailure($project, $response->status(), $response->body(), $attempt);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception triggering n8n workflow', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $attempt
            ]);

            // Update metadata with failure info
            $metadata = $project->orchestration_metadata ?? [];
            $metadata['last_error'] = [
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
                'attempt' => $attempt
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
