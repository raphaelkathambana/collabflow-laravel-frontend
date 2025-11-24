<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TaskReadinessService
{
    /**
     * Get tasks that are ready to execute
     *
     * Returns intelligently batched tasks:
     * - Up to 2 AI tasks (parallel processing)
     * - Up to 1 Human task
     * - Up to 1 HITL task
     */
    public function getReadyTasks(Project $project): Collection
    {
        // Get all project tasks
        $allTasks = $project->tasks()
            ->orderBy('sequence')
            ->get();

        // Filter tasks that are ready (pending + dependencies met)
        $readyTasks = $allTasks->filter(function ($task) use ($allTasks) {
            return $this->isTaskReady($task, $allTasks);
        });

        Log::debug('Task readiness analysis', [
            'project_id' => $project->id,
            'total_tasks' => $allTasks->count(),
            'pending_tasks' => $allTasks->where('status', 'pending')->count(),
            'ready_tasks' => $readyTasks->count()
        ]);

        // Intelligently batch by type
        return $this->batchTasksByType($readyTasks);
    }

    /**
     * Check if a task is ready to execute
     */
    private function isTaskReady($task, Collection $allTasks): bool
    {
        // Must be pending
        if ($task->status !== 'pending') {
            return false;
        }

        // Check dependencies
        $dependencies = $task->dependencies ?? [];

        // No dependencies = ready
        if (empty($dependencies)) {
            return true;
        }

        // Check if ALL dependency tasks are completed
        foreach ($dependencies as $depTaskId) {
            $depTask = $allTasks->firstWhere('id', $depTaskId);

            if (!$depTask || $depTask->status !== 'completed') {
                Log::debug('Task blocked by dependency', [
                    'task_id' => $task->id,
                    'task_name' => $task->name,
                    'dependency_id' => $depTaskId,
                    'dependency_status' => $depTask?->status ?? 'not_found'
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Batch tasks intelligently by type
     */
    private function batchTasksByType(Collection $readyTasks): Collection
    {
        $aiTasks = $readyTasks->where('type', 'ai')->take(2);
        $humanTasks = $readyTasks->where('type', 'human')->take(1);
        $hitlTasks = $readyTasks->where('type', 'hitl')->take(1);

        // Merge and preserve order by sequence
        $selectedTasks = $aiTasks
            ->merge($humanTasks)
            ->merge($hitlTasks)
            ->sortBy('sequence');

        // Enhance HITL tasks with checkpoint info
        return $selectedTasks->map(function ($task) {
            if ($task->type === 'hitl') {
                $metadata = $task->metadata ?? [];
                $subtasks = $metadata['subtasks'] ?? [];

                $task->checkpoint_subtasks = $subtasks;
                $task->total_checkpoints = collect($subtasks)
                    ->where('is_checkpoint', true)
                    ->count();
            }
            return $task;
        });
    }
}
