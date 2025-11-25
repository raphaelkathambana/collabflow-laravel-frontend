<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubtaskController extends Controller
{
    /**
     * Get all subtasks for a task
     */
    public function index(string $taskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $subtasks = $task->getSubtasks();

            return response()->json([
                'success' => true,
                'data' => [
                    'subtasks' => $subtasks,
                    'total' => count($subtasks),
                    'completed' => $task->getCompletedSubtasksCount(),
                    'progress' => $task->getSubtaskProgress(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching subtasks', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific subtask
     */
    public function show(string $taskId, string $subtaskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $subtask = $task->getSubtask($subtaskId);

            if (!$subtask) {
                return response()->json([
                    'success' => false,
                    'error' => 'Subtask not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $subtask
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching subtask', [
                'task_id' => $taskId,
                'subtask_id' => $subtaskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new subtask
     */
    public function store(Request $request, string $taskId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:255',
                'description' => 'nullable|string|max:1000',
                'type' => 'required|string|in:ai,human,hitl',
                'is_checkpoint' => 'nullable|boolean',
                'estimated_hours' => 'nullable|numeric|min:0.1|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($taskId);

            $subtaskData = [
                'name' => $request->input('name'),
                'description' => $request->input('description', ''),
                'type' => $request->input('type'),
                'is_checkpoint' => $request->input('is_checkpoint', false),
                'estimated_hours' => $request->input('estimated_hours'),
            ];

            $task->addSubtask($subtaskData);

            return response()->json([
                'success' => true,
                'message' => 'Subtask created successfully',
                'data' => [
                    'task' => $task->fresh(),
                    'subtasks' => $task->getSubtasks(),
                ]
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error creating subtask', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing subtask
     */
    public function update(Request $request, string $taskId, string $subtaskId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|min:3|max:255',
                'description' => 'nullable|string|max:1000',
                'type' => 'nullable|string|in:ai,human,hitl',
                'status' => 'nullable|string|in:pending,in_progress,completed',
                'is_checkpoint' => 'nullable|boolean',
                'estimated_hours' => 'nullable|numeric|min:0.1|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($taskId);

            // Build updates array from request
            $updates = $request->only([
                'name',
                'description',
                'type',
                'status',
                'is_checkpoint',
                'estimated_hours'
            ]);

            // Remove null values
            $updates = array_filter($updates, function($value) {
                return $value !== null;
            });

            if (empty($updates)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid fields to update'
                ], 422);
            }

            $task->updateSubtask($subtaskId, $updates);

            return response()->json([
                'success' => true,
                'message' => 'Subtask updated successfully',
                'data' => [
                    'subtask' => $task->getSubtask($subtaskId),
                    'task' => $task->fresh(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error updating subtask', [
                'task_id' => $taskId,
                'subtask_id' => $subtaskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a subtask
     */
    public function destroy(string $taskId, string $subtaskId): JsonResponse
    {
        try {
            $task = Task::findOrFail($taskId);
            $task->deleteSubtask($subtaskId);

            return response()->json([
                'success' => true,
                'message' => 'Subtask deleted successfully',
                'data' => [
                    'task' => $task->fresh(),
                    'subtasks' => $task->getSubtasks(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting subtask', [
                'task_id' => $taskId,
                'subtask_id' => $subtaskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit work for a specific subtask
     */
    public function submitWork(Request $request, string $taskId, string $subtaskId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'output' => 'required|array',
                'output.type' => 'required|string',
                'output.content' => 'required',
                'completed_by' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($taskId);
            $subtask = $task->getSubtask($subtaskId);

            if (!$subtask) {
                return response()->json([
                    'success' => false,
                    'error' => 'Subtask not found'
                ], 404);
            }

            $task->completeSubtask(
                $subtaskId,
                $request->input('output'),
                $request->input('completed_by', 'ai_agent')
            );

            return response()->json([
                'success' => true,
                'message' => 'Subtask work submitted successfully',
                'data' => [
                    'subtask' => $task->getSubtask($subtaskId),
                    'progress' => $task->getSubtaskProgress(),
                    'completed_count' => $task->getCompletedSubtasksCount(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error submitting subtask work', [
                'task_id' => $taskId,
                'subtask_id' => $subtaskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark subtask as complete
     */
    public function complete(Request $request, string $taskId, string $subtaskId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'completed_by' => 'nullable|string',
                'output' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($taskId);
            $subtask = $task->getSubtask($subtaskId);

            if (!$subtask) {
                return response()->json([
                    'success' => false,
                    'error' => 'Subtask not found'
                ], 404);
            }

            $task->completeSubtask(
                $subtaskId,
                $request->input('output'),
                $request->input('completed_by', auth()->user()->name ?? 'user')
            );

            return response()->json([
                'success' => true,
                'message' => 'Subtask marked as complete',
                'data' => [
                    'subtask' => $task->getSubtask($subtaskId),
                    'progress' => $task->getSubtaskProgress(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error completing subtask', [
                'task_id' => $taskId,
                'subtask_id' => $subtaskId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
