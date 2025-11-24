<?php

use App\Http\Controllers\Api\OrchestrationController;
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
