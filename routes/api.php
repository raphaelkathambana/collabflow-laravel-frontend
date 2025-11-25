<?php

use App\Http\Controllers\Api\OrchestrationController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Project API endpoints (no auth required for n8n access)
Route::prefix('projects')->group(function () {
    Route::get('/{id}', [ProjectController::class, 'show'])->name('api.projects.show');
    Route::get('/{id}/tasks', [ProjectController::class, 'tasks'])->name('api.projects.tasks');
    Route::get('/{id}/ready-tasks', [ProjectController::class, 'readyTasks'])->name('api.projects.ready-tasks');
    Route::post('/{id}/start', [ProjectController::class, 'start'])->name('api.projects.start');
    Route::post('/{id}/pause', [ProjectController::class, 'pause'])->name('api.projects.pause');
    Route::post('/{id}/resume', [ProjectController::class, 'resume'])->name('api.projects.resume');
});

// n8n Orchestration Callbacks (no auth required)
Route::prefix('orchestration')->group(function () {
    Route::post('/callback', [OrchestrationController::class, 'callback'])
        ->name('api.orchestration.callback');
    Route::patch('/tasks/{taskId}/status', [OrchestrationController::class, 'updateTaskStatus'])
        ->name('api.orchestration.task-status');
});
