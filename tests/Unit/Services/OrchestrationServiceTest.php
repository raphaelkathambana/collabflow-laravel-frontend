<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\OrchestrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrchestrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrchestrationService $service;
    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrchestrationService();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'orchestration_status' => 'running'
        ]);
    }

    public function test_trigger_workflow_sends_http_request(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200)
        ]);

        $result = $this->service->triggerWorkflow($this->project);

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return $request->url() === config('services.n8n.webhook_url')
                && $request['project_id'] === $this->project->id
                && $request['trigger_source'] === 'laravel_automatic';
        });
    }

    public function test_trigger_workflow_updates_project_metadata(): void
    {
        Http::fake([
            '*' => Http::response(['workflow_id' => '123'], 200)
        ]);

        $this->service->triggerWorkflow($this->project);

        $this->project->refresh();

        $this->assertEquals(1, $this->project->total_orchestration_runs);
        $this->assertArrayHasKey('last_trigger_at', $this->project->orchestration_metadata);
        $this->assertArrayHasKey('last_trigger_response', $this->project->orchestration_metadata);
    }

    public function test_trigger_workflow_respects_paused_status(): void
    {
        $this->project->update(['orchestration_status' => 'paused']);

        Http::fake();

        $result = $this->service->triggerWorkflow($this->project);

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_trigger_workflow_handles_http_errors(): void
    {
        Http::fake([
            '*' => Http::response('Error', 500)
        ]);

        $result = $this->service->triggerWorkflow($this->project);

        $this->assertFalse($result);
    }

    public function test_is_orchestration_complete_when_all_tasks_done(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed'
        ]);

        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed'
        ]);

        $result = $this->service->isOrchestrationComplete($this->project);

        $this->assertTrue($result);
        $this->project->refresh();
        $this->assertEquals('completed', $this->project->orchestration_status);
    }

    public function test_is_orchestration_incomplete_with_pending_tasks(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed'
        ]);

        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending'
        ]);

        $result = $this->service->isOrchestrationComplete($this->project);

        $this->assertFalse($result);
        $this->project->refresh();
        $this->assertEquals('running', $this->project->orchestration_status);
    }

    public function test_is_orchestration_incomplete_with_in_progress_tasks(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed'
        ]);

        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'in_progress'
        ]);

        $result = $this->service->isOrchestrationComplete($this->project);

        $this->assertFalse($result);
    }
}
