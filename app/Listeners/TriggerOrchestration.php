<?php

namespace App\Listeners;

use App\Events\ProjectStarted;
use App\Services\OrchestrationService;
use Illuminate\Support\Facades\Log;

class TriggerOrchestration
{
    public function __construct(
        private OrchestrationService $orchestrationService
    ) {}

    public function handle(ProjectStarted $event): void
    {
        Log::info('ProjectStarted event received', [
            'project_id' => $event->project->id
        ]);

        $this->orchestrationService->triggerWorkflow($event->project);
    }
}
