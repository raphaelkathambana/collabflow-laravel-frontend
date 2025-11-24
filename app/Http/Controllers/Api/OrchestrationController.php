<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrchestrationController extends Controller
{
    /**
     * Handle n8n orchestration callback
     *
     * Receives completion notifications from n8n workflows
     * and updates project status accordingly
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            // Validate incoming request
            $validated = $request->validate([
                'project_id' => 'required|integer|exists:projects,id',
                'status' => 'required|string',
                'execution_id' => 'required|string',
                'workflow_name' => 'sometimes|string',
                'total_tasks_processed' => 'sometimes|integer',
                'completed_at' => 'sometimes|date'
            ]);

            Log::info('Orchestration callback received', [
                'project_id' => $validated['project_id'],
                'status' => $validated['status'],
                'execution_id' => $validated['execution_id']
            ]);

            // Find project
            $project = Project::findOrFail($validated['project_id']);

            // Map n8n status to project status
            $projectStatus = $this->mapN8nStatus($validated['status']);

            // Update project
            $project->update([
                'status' => $projectStatus,
                'n8n_execution_id' => $validated['execution_id'],
            ]);

            // Log the activity
            ActivityLog::create([
                'project_id' => $project->id,
                'action' => 'orchestration_callback',
                'details' => $validated
            ]);

            Log::info('Project updated successfully', [
                'project_id' => $project->id,
                'new_status' => $projectStatus
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Callback received and processed',
                'project_id' => $project->id
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Orchestration callback validation failed', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Project not found in orchestration callback', [
                'project_id' => $request->input('project_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);

        } catch (\Exception $e) {
            Log::error('Orchestration callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map n8n workflow status to project status
     */
    private function mapN8nStatus(string $n8nStatus): string
    {
        return match($n8nStatus) {
            'completed' => 'active',
            'failed' => 'failed',
            'running' => 'processing',
            'partial_success' => 'active', // Consider partial success as active
            default => 'draft'
        };
    }
}
