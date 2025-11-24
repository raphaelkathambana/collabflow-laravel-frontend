<?php

use App\Http\Controllers\Api\OrchestrationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\SubtaskController;
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

// n8n Orchestration Callbacks (no auth required)
Route::post('/orchestration/callback', [OrchestrationController::class, 'callback'])
    ->name('api.orchestration.callback');

// Project API endpoints (no auth required for n8n access)
Route::get('/projects/{id}', [ProjectController::class, 'show'])
    ->name('api.projects.show');

Route::get('/projects/{id}/tasks', [ProjectController::class, 'tasks'])
    ->name('api.projects.tasks');

// Task API endpoints (no auth required for n8n/Python service access)
Route::prefix('tasks')->group(function () {
    // Task Management
    Route::get('/{id}', [TaskController::class, 'show'])
        ->name('api.tasks.show');

    Route::post('/{id}/work', [TaskController::class, 'submitWork'])
        ->name('api.tasks.submit-work');

    Route::put('/{id}/status', [TaskController::class, 'updateStatus'])
        ->name('api.tasks.update-status');

    Route::post('/{id}/review', [TaskController::class, 'submitReview'])
        ->name('api.tasks.submit-review');

    // Activity Logging
    Route::get('/{id}/activity', [TaskController::class, 'getActivity'])
        ->name('api.tasks.activity');

    Route::post('/{id}/activity', [TaskController::class, 'logActivity'])
        ->name('api.tasks.log-activity');

    // Subtask Management
    Route::get('/{taskId}/subtasks', [SubtaskController::class, 'index'])
        ->name('api.tasks.subtasks.index');

    Route::post('/{taskId}/subtasks', [SubtaskController::class, 'store'])
        ->name('api.tasks.subtasks.store');

    Route::get('/{taskId}/subtasks/{subtaskId}', [SubtaskController::class, 'show'])
        ->name('api.tasks.subtasks.show');

    Route::put('/{taskId}/subtasks/{subtaskId}', [SubtaskController::class, 'update'])
        ->name('api.tasks.subtasks.update');

    Route::delete('/{taskId}/subtasks/{subtaskId}', [SubtaskController::class, 'destroy'])
        ->name('api.tasks.subtasks.destroy');

    Route::post('/{taskId}/subtasks/{subtaskId}/work', [SubtaskController::class, 'submitWork'])
        ->name('api.tasks.subtasks.submit-work');

    Route::put('/{taskId}/subtasks/{subtaskId}/complete', [SubtaskController::class, 'complete'])
        ->name('api.tasks.subtasks.complete');
});
