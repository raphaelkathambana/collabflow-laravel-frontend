<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrchestrationController extends Controller
{
    /**
     * Update task execution status (for progress updates during execution)
     */
    public function updateTaskStatus(Request $request, string $taskId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:assigned,in_progress,completed,failed',
            'execution_id' => 'required|string',
            'progress' => 'nullable|integer|min:0|max:100',
            'message' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid payload',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $task = Task::findOrFail($taskId);

            $metadata = $task->metadata ?? [];
            $metadata['execution_updates'] = $metadata['execution_updates'] ?? [];
            $metadata['execution_updates'][] = [
                'status' => $request->status,
                'execution_id' => $request->execution_id,
                'progress' => $request->progress,
                'message' => $request->message,
                'timestamp' => now()->toISOString()
            ];

            $task->update([
                'status' => $request->status,
                'metadata' => $metadata
            ]);

            Log::info('Task status updated', [
                'task_id' => $task->id,
                'status' => $request->status,
                'progress' => $request->progress
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated',
                'task_id' => $task->id,
                'status' => $task->status
            ]);

        } catch (\Exception $e) {
            Log::error('Task status update failed', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update task status'
            ], 500);
        }
    }

    /**
     * Handle callback from n8n after task execution
     */
    public function callback(Request $request): JsonResponse
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|uuid|exists:projects,id',
            'task_id' => 'required|uuid|exists:tasks,id',
            'task_type' => 'required|string|in:ai,human,hitl',
            'status' => 'required|string|in:completed,assigned,in_progress,failed',
            'execution_id' => 'required|string',
            'result_data' => 'required|array'
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid callback payload', [
                'errors' => $validator->errors(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid payload',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $project = Project::findOrFail($request->project_id);
            $task = Task::findOrFail($request->task_id);

            // Update task status
            $task->update([
                'status' => $request->status,
                'metadata' => array_merge(
                    $task->metadata ?? [],
                    [
                        'last_execution' => $request->result_data,
                        'last_execution_id' => $request->execution_id,
                        'last_execution_at' => now()->toISOString()
                    ]
                )
            ]);

            // Update project orchestration tracking
            $project->update([
                'last_n8n_execution_id' => $request->execution_id
            ]);

            Log::info('Task callback processed', [
                'project_id' => $project->id,
                'task_id' => $task->id,
                'task_type' => $request->task_type,
                'status' => $request->status,
                'execution_id' => $request->execution_id
            ]);

            // Only dispatch TaskCompleted event if status is actually completed
            if ($request->status === 'completed') {
                event(new \App\Events\TaskCompleted($task));
            }

            return response()->json([
                'success' => true,
                'message' => 'Callback received and processed',
                'project_id' => $project->id,
                'task_id' => $task->id
            ]);

        } catch (\Exception $e) {
            Log::error('Callback processing failed', [
                'project_id' => $request->project_id ?? null,
                'task_id' => $request->task_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process callback'
            ], 500);
        }
    }

    /**
     * Handle batch callback from n8n multi-task workflow
     */
    public function batchCallback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|uuid|exists:projects,id',
            'execution_id' => 'required|string',
            'tasks' => 'required|array',
            'tasks.*.task_id' => 'required|uuid|exists:tasks,id',
            'tasks.*.status' => 'required|string|in:completed,assigned,in_progress,failed',
            'tasks.*.result_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid batch callback payload', [
                'errors' => $validator->errors(),
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid payload',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $project = Project::findOrFail($request->project_id);
            $processed = [];
            $failed = [];

            // Process each task in the batch
            foreach ($request->tasks as $taskData) {
                try {
                    $task = Task::findOrFail($taskData['task_id']);

                    $task->update([
                        'status' => $taskData['status'],
                        'metadata' => array_merge(
                            $task->metadata ?? [],
                            [
                                'last_execution' => $taskData['result_data'] ?? [],
                                'last_execution_id' => $request->execution_id,
                                'last_execution_at' => now()->toISOString(),
                                'batch_execution' => true
                            ]
                        )
                    ]);

                    $processed[] = $task->id;

                    // Dispatch TaskCompleted event if completed
                    if ($taskData['status'] === 'completed') {
                        event(new \App\Events\TaskCompleted($task));
                    }

                } catch (\Exception $e) {
                    Log::error('Failed to process task in batch', [
                        'task_id' => $taskData['task_id'],
                        'error' => $e->getMessage()
                    ]);
                    $failed[] = $taskData['task_id'];
                }
            }

            // Update project with batch execution info
            $project->update([
                'last_n8n_execution_id' => $request->execution_id
            ]);

            Log::info('Batch callback processed', [
                'project_id' => $project->id,
                'execution_id' => $request->execution_id,
                'processed_count' => count($processed),
                'failed_count' => count($failed),
                'processed_tasks' => $processed,
                'failed_tasks' => $failed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch callback processed',
                'project_id' => $project->id,
                'processed_count' => count($processed),
                'failed_count' => count($failed),
                'processed_tasks' => $processed,
                'failed_tasks' => $failed
            ]);

        } catch (\Exception $e) {
            Log::error('Batch callback processing failed', [
                'project_id' => $request->project_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process batch callback'
            ], 500);
        }
    }

    /**
     * Log error from n8n workflow
     */
    public function logError(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'nullable|uuid|exists:projects,id',
            'task_id' => 'nullable|uuid|exists:tasks,id',
            'execution_id' => 'required|string',
            'workflow_name' => 'required|string',
            'error_type' => 'required|string',
            'error_message' => 'required|string',
            'error_details' => 'nullable|array',
            'retry_count' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid payload',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $errorData = [
                'execution_id' => $request->execution_id,
                'workflow_name' => $request->workflow_name,
                'error_type' => $request->error_type,
                'error_message' => $request->error_message,
                'error_details' => $request->error_details ?? [],
                'retry_count' => $request->retry_count ?? 0,
                'timestamp' => now()->toISOString()
            ];

            // Log to Laravel logs
            Log::error('n8n workflow error', array_merge($errorData, [
                'project_id' => $request->project_id,
                'task_id' => $request->task_id,
            ]));

            // If project_id provided, update project metadata
            if ($request->project_id) {
                $project = Project::find($request->project_id);
                if ($project) {
                    $metadata = $project->orchestration_metadata ?? [];
                    $metadata['errors'] = $metadata['errors'] ?? [];
                    $metadata['errors'][] = $errorData;

                    $project->update([
                        'orchestration_metadata' => $metadata
                    ]);
                }
            }

            // If task_id provided, update task metadata
            if ($request->task_id) {
                $task = Task::find($request->task_id);
                if ($task) {
                    $metadata = $task->metadata ?? [];
                    $metadata['execution_errors'] = $metadata['execution_errors'] ?? [];
                    $metadata['execution_errors'][] = $errorData;

                    $task->update([
                        'metadata' => $metadata
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Error logged successfully',
                'execution_id' => $request->execution_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log n8n error', [
                'error' => $e->getMessage(),
                'original_error' => $request->error_message
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to log error'
            ], 500);
        }
    }

    /**
     * Handle error recovery completion from n8n
     */
    public function errorRecoveryComplete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|uuid|exists:projects,id',
            'execution_id' => 'required|string',
            'original_execution_id' => 'required|string',
            'recovery_action' => 'required|string',
            'recovered_tasks' => 'nullable|array',
            'recovered_tasks.*' => 'uuid|exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid payload',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $project = Project::findOrFail($request->project_id);

            $metadata = $project->orchestration_metadata ?? [];
            $metadata['error_recoveries'] = $metadata['error_recoveries'] ?? [];
            $metadata['error_recoveries'][] = [
                'execution_id' => $request->execution_id,
                'original_execution_id' => $request->original_execution_id,
                'recovery_action' => $request->recovery_action,
                'recovered_tasks' => $request->recovered_tasks ?? [],
                'timestamp' => now()->toISOString()
            ];

            $project->update([
                'orchestration_metadata' => $metadata
            ]);

            Log::info('Error recovery completed', [
                'project_id' => $project->id,
                'execution_id' => $request->execution_id,
                'recovery_action' => $request->recovery_action,
                'recovered_tasks_count' => count($request->recovered_tasks ?? [])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Error recovery logged successfully',
                'project_id' => $project->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error recovery logging failed', [
                'project_id' => $request->project_id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to log error recovery'
            ], 500);
        }
    }
}
