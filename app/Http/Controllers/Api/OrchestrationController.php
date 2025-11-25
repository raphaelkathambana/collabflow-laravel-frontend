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
}
