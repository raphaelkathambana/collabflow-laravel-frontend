<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\TaskReadinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function __construct(
        private TaskReadinessService $taskReadinessService
    ) {}
    /**
     * Get project details
     */
    public function show(string $id): JsonResponse
    {
        try {
            $project = Project::with(['user'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $project
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Project not found', ['id' => $id]);

            return response()->json([
                'success' => false,
                'error' => 'Project not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching project', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project tasks
     */
    public function tasks(string $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            $tasks = $project->tasks()
                ->orderBy('sequence')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'count' => $tasks->count()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Project not found for tasks', ['id' => $id]);

            return response()->json([
                'success' => false,
                'error' => 'Project not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching project tasks', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tasks that are ready to execute
     *
     * Applies intelligent filtering:
     * - Only pending tasks
     * - Dependencies met
     * - Batched by type (2 AI, 1 Human, 1 HITL)
     */
    public function readyTasks(string $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            // Use service to get ready tasks
            $readyTasks = $this->taskReadinessService->getReadyTasks($project);

            Log::info('Ready tasks retrieved', [
                'project_id' => $id,
                'ready_count' => $readyTasks->count(),
                'breakdown' => [
                    'ai' => $readyTasks->where('type', 'ai')->count(),
                    'human' => $readyTasks->where('type', 'human')->count(),
                    'hitl' => $readyTasks->where('type', 'hitl')->count(),
                ]
            ]);

            return response()->json([
                'success' => true,
                'data' => $readyTasks->values(),
                'count' => $readyTasks->count(),
                'metadata' => [
                    'total_pending' => $project->tasks()->where('status', 'pending')->count(),
                    'batch_limits' => [
                        'ai_parallel' => 2,
                        'human_parallel' => 1,
                        'hitl_parallel' => 1,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching ready tasks', [
                'project_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch ready tasks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start project orchestration
     */
    public function start(string $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            // Validate project can be started
            if ($project->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Project must be in draft status to start'
                ], 400);
            }

            // Check if project has tasks
            if ($project->tasks()->count() === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Project has no tasks'
                ], 400);
            }

            // Update project status
            $project->update([
                'status' => 'active',
                'orchestration_status' => 'running',
                'orchestration_started_at' => now()
            ]);

            // Dispatch event to trigger orchestration
            event(new \App\Events\ProjectStarted($project));

            Log::info('Project started', [
                'project_id' => $project->id,
                'project_name' => $project->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Project started successfully',
                'project' => $project
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to start project', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to start project'
            ], 500);
        }
    }

    /**
     * Pause project orchestration
     */
    public function pause(string $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            if ($project->orchestration_status !== 'running') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only running orchestrations can be paused'
                ], 400);
            }

            $project->update([
                'orchestration_status' => 'paused'
            ]);

            Log::info('Project orchestration paused', [
                'project_id' => $project->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Orchestration paused successfully',
                'project' => $project
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to pause orchestration', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to pause orchestration'
            ], 500);
        }
    }

    /**
     * Resume project orchestration
     */
    public function resume(string $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            if ($project->orchestration_status !== 'paused') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only paused orchestrations can be resumed'
                ], 400);
            }

            $project->update([
                'orchestration_status' => 'running'
            ]);

            // Trigger workflow to continue
            event(new \App\Events\ProjectStarted($project));

            Log::info('Project orchestration resumed', [
                'project_id' => $project->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Orchestration resumed successfully',
                'project' => $project
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to resume orchestration', [
                'project_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to resume orchestration'
            ], 500);
        }
    }
}
