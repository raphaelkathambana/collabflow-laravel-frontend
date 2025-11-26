<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Services\OrchestrationService;
use App\Services\TaskReadinessService;
use Illuminate\Support\Facades\Cache;
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

        // ========================================
        // CASCADING PREVENTION #1: Check for in-progress tasks
        // ========================================
        // If any tasks are currently being processed by n8n, wait for them to complete
        // before triggering the next batch. This prevents cascading triggers when multiple
        // task completion callbacks arrive in rapid succession.
        $inProgressCount = $project->tasks()
            ->where('status', 'in_progress')
            ->count();

        if ($inProgressCount > 0) {
            Log::info('Tasks still in progress - skipping re-trigger to prevent cascading', [
                'project_id' => $project->id,
                'in_progress_count' => $inProgressCount,
                'completed_task_id' => $event->task->id
            ]);
            return;
        }

        // ========================================
        // CASCADING PREVENTION #2: Cache lock
        // ========================================
        // Use distributed locking to ensure only ONE trigger happens at a time,
        // even if multiple TaskCompleted events fire simultaneously (race condition).
        $lockKey = "orchestration-trigger:{$project->id}";
        $lock = Cache::lock($lockKey, 10); // 10 second lock timeout

        if (!$lock->get()) {
            Log::info('Another orchestration trigger already in progress - skipping to prevent race condition', [
                'project_id' => $project->id,
                'task_id' => $event->task->id
            ]);
            return;
        }

        try {
            // Check if there are more ready tasks
            $readyTasks = $this->taskReadinessService->getReadyTasks($project);

            if ($readyTasks->isNotEmpty()) {
                Log::info('Ready tasks found - triggering n8n for next batch', [
                    'project_id' => $project->id,
                    'ready_count' => $readyTasks->count(),
                    'ready_task_ids' => $readyTasks->pluck('id')->toArray()
                ]);

                $this->orchestrationService->triggerWorkflow($project);
            } else {
                Log::info('No ready tasks yet - waiting for more dependencies to complete', [
                    'project_id' => $project->id,
                    'total_tasks' => $project->tasks()->count(),
                    'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
                    'pending_tasks' => $project->tasks()->where('status', 'pending')->count()
                ]);
            }
        } finally {
            // Always release the lock, even if an exception occurs
            $lock->release();
        }
    }
}
