<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskReadinessServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskReadinessService $service;
    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskReadinessService();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);
    }

    public function test_returns_tasks_with_no_dependencies(): void
    {
        $task1 = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
            'type' => 'ai',
            'dependencies' => null,
            'sequence' => 1
        ]);

        $task2 = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
            'type' => 'ai',
            'dependencies' => null,
            'sequence' => 2
        ]);

        $readyTasks = $this->service->getReadyTasks($this->project);

        $this->assertCount(2, $readyTasks);
        $this->assertTrue($readyTasks->contains($task1));
        $this->assertTrue($readyTasks->contains($task2));
    }

    public function test_excludes_tasks_with_pending_dependencies(): void
    {
        $task1 = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
            'type' => 'ai',
            'dependencies' => null,
            'sequence' => 1
        ]);

        $task2 = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
            'type' => 'ai',
            'dependencies' => [$task1->id],
            'sequence' => 2
        ]);

        $readyTasks = $this->service->getReadyTasks($this->project);

        $this->assertCount(1, $readyTasks);
        $this->assertTrue($readyTasks->contains($task1));
        $this->assertFalse($readyTasks->contains($task2));
    }

    public function test_includes_tasks_when_dependencies_completed(): void
    {
        $task1 = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed',
            'type' => 'ai',
            'dependencies' => null,
            'sequence' => 1
        ]);

        $task2 = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
            'type' => 'ai',
            'dependencies' => [$task1->id],
            'sequence' => 2
        ]);

        $readyTasks = $this->service->getReadyTasks($this->project);

        $this->assertCount(1, $readyTasks);
        $this->assertTrue($readyTasks->contains($task2));
    }

    public function test_batches_tasks_by_type(): void
    {
        // Create more than batch limit for each type
        for ($i = 0; $i < 5; $i++) {
            Task::factory()->create([
                'project_id' => $this->project->id,
                'status' => 'pending',
                'type' => 'ai',
                'dependencies' => null,
                'sequence' => $i + 1
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            Task::factory()->create([
                'project_id' => $this->project->id,
                'status' => 'pending',
                'type' => 'human',
                'dependencies' => null,
                'sequence' => $i + 10
            ]);
        }

        for ($i = 0; $i < 2; $i++) {
            Task::factory()->create([
                'project_id' => $this->project->id,
                'status' => 'pending',
                'type' => 'hitl',
                'dependencies' => null,
                'sequence' => $i + 20
            ]);
        }

        $readyTasks = $this->service->getReadyTasks($this->project);

        $aiCount = $readyTasks->where('type', 'ai')->count();
        $humanCount = $readyTasks->where('type', 'human')->count();
        $hitlCount = $readyTasks->where('type', 'hitl')->count();

        // Verify batch limits: 2 AI, 1 Human, 1 HITL
        $this->assertEquals(2, $aiCount);
        $this->assertEquals(1, $humanCount);
        $this->assertEquals(1, $hitlCount);
    }

    public function test_excludes_non_pending_tasks(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed',
            'type' => 'ai',
            'dependencies' => null,
            'sequence' => 1
        ]);

        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'in_progress',
            'type' => 'ai',
            'dependencies' => null,
            'sequence' => 2
        ]);

        $readyTasks = $this->service->getReadyTasks($this->project);

        $this->assertCount(0, $readyTasks);
    }
}
