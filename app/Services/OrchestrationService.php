<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrchestrationService
{
    private string $n8nWebhookUrl;
    private int $timeout;

    public function __construct()
    {
        $this->n8nWebhookUrl = config('services.n8n.webhook_url');
        $this->timeout = config('services.n8n.timeout', 10);
    }

    /**
     * Trigger n8n orchestration workflow
     */
    public function triggerWorkflow(Project $project): bool
    {
        try {
            Log::info('Triggering n8n workflow', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'webhook_url' => $this->n8nWebhookUrl
            ]);

            $response = Http::timeout($this->timeout)
                ->post($this->n8nWebhookUrl, [
                    'project_id' => $project->id,
                    'trigger_source' => 'laravel_automatic'
                ]);

            if ($response->successful()) {
                // Update orchestration metadata
                $project->increment('total_orchestration_runs');

                $metadata = $project->orchestration_metadata ?? [];
                $metadata['last_trigger_at'] = now()->toISOString();
                $metadata['last_trigger_response'] = $response->json();

                $project->update([
                    'orchestration_metadata' => $metadata
                ]);

                Log::info('n8n workflow triggered successfully', [
                    'project_id' => $project->id,
                    'response' => $response->json(),
                    'total_runs' => $project->total_orchestration_runs
                ]);

                return true;
            }

            Log::error('n8n workflow trigger failed', [
                'project_id' => $project->id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception triggering n8n workflow', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
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
