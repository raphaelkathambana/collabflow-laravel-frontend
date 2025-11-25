<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Get single task details
     */
    public function show(string $id): JsonResponse
    {
        try {
            $task = Task::with(['project', 'assignee', 'reviewer', 'activityLogs'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'task' => $task,
                    'subtasks' => $task->getSubtasks(),
                    'subtask_progress' => $task->getSubtaskProgress(),
                    'completed_subtasks' => $task->getCompletedSubtasksCount(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Task not found', ['id' => $id]);

            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching task', [
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
     * Submit work output for a task (from AI or human)
     */
    public function submitWork(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'output' => 'required|array',
                'output.type' => 'required|string|in:code,analysis,documentation,design,data,other',
                'output.content' => 'required',
                'output.format' => 'nullable|string|in:markdown,json,html,text,code',
                'output.files' => 'nullable|array',
                'output.metadata' => 'nullable|array',
                'status' => 'nullable|string|in:in_progress,review,completed',
                'confidence_score' => 'nullable|numeric|min:0|max:1',
                'started_at' => 'nullable|date',
                'completed_at' => 'nullable|date',
                'subtask_id' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($id);

            // If submitting work for a specific subtask
            if ($request->has('subtask_id')) {
                $subtaskId = $request->input('subtask_id');
                $subtask = $task->getSubtask($subtaskId);

                if (!$subtask) {
                    return response()->json([
                        'success' => false,
                        'error' => "Subtask not found: {$subtaskId}"
                    ], 404);
                }

                // Complete the subtask with output
                $task->completeSubtask(
                    $subtaskId,
                    $request->input('output'),
                    $request->input('completed_by', 'ai_agent')
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Subtask work submitted successfully',
                    'data' => [
                        'task_id' => $task->id,
                        'subtask_id' => $subtaskId,
                        'subtask_progress' => $task->getSubtaskProgress(),
                    ]
                ]);
            }

            // Submit work for the main task
            $options = [
                'status' => $request->input('status', 'review'),
                'confidence_score' => $request->input('confidence_score'),
                'started_at' => $request->input('started_at'),
                'completed_at' => $request->input('completed_at'),
            ];

            $task->submitWork($request->input('output'), $options);

            return response()->json([
                'success' => true,
                'message' => 'Task work submitted successfully',
                'data' => [
                    'task' => $task->fresh(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Task not found for work submission', ['id' => $id]);

            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error submitting task work', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:generated,pending,in_progress,review,completed,cancelled,blocked',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($id);
            $oldStatus = $task->status;
            $task->status = $request->input('status');

            // Auto-set timestamps based on status
            if ($request->input('status') === 'in_progress' && !$task->started_at) {
                $task->started_at = now();
            }

            if ($request->input('status') === 'completed' && !$task->completed_at) {
                $task->completed_at = now();
            }

            $task->save();

            $task->logActivity('status_changed', [
                'old_status' => $oldStatus,
                'new_status' => $task->status,
                'notes' => $request->input('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'data' => $task->fresh()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error updating task status', [
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
     * Submit review for HITL task
     */
    public function submitReview(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|string|in:approve,request_changes',
                'notes' => 'required|string|min:3',
                'reviewer_id' => 'required|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($id);
            $action = $request->input('action');
            $notes = $request->input('notes');
            $reviewerId = $request->input('reviewer_id');

            if ($action === 'approve') {
                $task->approve($reviewerId, $notes);
                $message = 'Task approved successfully';
            } else {
                $task->requestChanges($reviewerId, $notes);
                $message = 'Changes requested successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $task->fresh(['reviewer', 'activityLogs'])
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error submitting task review', [
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
     * Get task activity timeline
     */
    public function getActivity(string $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);
            $activities = $task->activityLogs()->with('user')->get();

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching task activity', [
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
     * Log custom activity
     */
    public function logActivity(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|string',
                'details' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::findOrFail($id);
            $task->logActivity(
                $request->input('action'),
                $request->input('details', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Activity logged successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error logging task activity', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
