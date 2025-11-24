<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
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
}
