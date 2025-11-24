<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Services\OrchestrationService;
use App\Services\TaskReadinessService;
use Illuminate\Support\Facades\Log;

class CheckForReadyTasks
{
    public function __construct(
        private TaskReadinessService $taskReadinessService,
        private OrchestrationService $orchestrationService
    ) {}

    public function handle(TaskCompleted $event): void
    {
        $project = $event->task->project;

        Log::info('TaskCompleted event received', [
            'project_id' => $project->id,
            'task_id' => $event->task->id,
            'task_name' => $event->task->name
        ]);

        // Check if orchestration is complete
        if ($this->orchestrationService->isOrchestrationComplete($project)) {
            Log::info('Project orchestration complete - no more triggers needed', [
                'project_id' => $project->id
            ]);
            return;
        }

        // Check if there are more ready tasks
        $readyTasks = $this->taskReadinessService->getReadyTasks($project);

        if ($readyTasks->isNotEmpty()) {
            Log::info('Ready tasks found - triggering n8n again', [
                'project_id' => $project->id,
                'ready_count' => $readyTasks->count()
            ]);

            $this->orchestrationService->triggerWorkflow($project);
        } else {
            Log::info('No ready tasks - waiting for more completions', [
                'project_id' => $project->id
            ]);
        }
    }
}
