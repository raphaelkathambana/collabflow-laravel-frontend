<?php

use App\Livewire\Projects\ProjectDetail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

describe('ProjectDetail Component', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create a test project with tasks
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'description' => 'Test project description',
            'status' => 'active',
            'domain' => 'software_development',
        ]);

        $this->tasks = Task::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
        ]);
    });

    // TEST 1: Component Initialization
    it('loads project and initializes correctly', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->assertSet('projectId', $this->project->id)
            ->assertSet('activeTab', 'tasks')
            ->assertSet('editingProject', false)
            ->assertSet('showTaskModal', false)
            ->assertViewHas('filteredTasks');
    });

    it('fails to load project for unauthorized user', function () {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        expect(function () {
            Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id]);
        })->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    // TEST 2: Tab Switching
    it('switches between tabs correctly', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->assertSet('activeTab', 'tasks')
            ->call('switchTab', 'overview')
            ->assertSet('activeTab', 'overview')
            ->call('switchTab', 'timeline')
            ->assertSet('activeTab', 'timeline');
    });

    // TEST 3: Task Status Management
    it('toggles task status between completed and pending', function () {
        $task = $this->tasks->first();
        expect($task->status)->toBe('pending');

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('toggleTaskStatus', $task->id);

        $task->refresh();
        expect($task->status)->toBe('completed');

        // Toggle back
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('toggleTaskStatus', $task->id);

        $task->refresh();
        expect($task->status)->toBe('pending');
    });

    it('updates task status with valid transitions', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
        ]);

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateTaskStatus', $task->id, 'in_progress');

        $task->refresh();
        expect($task->status)->toBe('in_progress');
    });

    it('prevents invalid task status transitions', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
        ]);

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateTaskStatus', $task->id, 'completed');

        $task->refresh();
        expect($task->status)->toBe('pending'); // Should not change
    });

    it('validates task status transitions from generated to pending', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'generated',
        ]);

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateTaskStatus', $task->id, 'pending');

        $task->refresh();
        expect($task->status)->toBe('pending');
    });

    // TEST 4: Task Deletion
    it('deletes task successfully', function () {
        $task = $this->tasks->first();
        expect(Task::find($task->id))->not->toBeNull();

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('deleteTask', $task->id);

        expect(Task::find($task->id))->toBeNull();
    });

    it('prevents deleting task from another users project', function () {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);
        $otherTask = Task::factory()->create(['project_id' => $otherProject->id]);

        expect(function () {
            Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
                ->call('deleteTask', $otherTask->id);
        })->toThrow(\Exception::class);

        expect(Task::find($otherTask->id))->not->toBeNull(); // Should still exist
    });

    // TEST 5: Task Modal Management
    it('opens create task modal with empty form', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('openCreateTaskModal')
            ->assertSet('showTaskModal', true)
            ->assertSet('editingTaskId', null)
            ->assertSet('taskName', '')
            ->assertSet('taskDescription', '')
            ->assertSet('taskType', 'human')
            ->assertSet('taskStatus', 'pending');
    });

    it('opens edit task modal with pre-filled data', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'Existing Task',
            'description' => 'Task description',
            'type' => 'ai',
            'status' => 'in_progress',
            'estimated_hours' => 10,
        ]);

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('openEditTaskModal', $task->id)
            ->assertSet('showTaskModal', true)
            ->assertSet('editingTaskId', $task->id)
            ->assertSet('taskName', 'Existing Task')
            ->assertSet('taskDescription', 'Task description')
            ->assertSet('taskType', 'ai')
            ->assertSet('taskStatus', 'in_progress')
            ->assertSet('taskEstimatedHours', 10);
    });

    it('closes task modal and resets form', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('showTaskModal', true)
            ->set('taskName', 'Some Task')
            ->call('closeTaskModal')
            ->assertSet('showTaskModal', false)
            ->assertSet('taskName', '')
            ->assertSet('editingTaskId', null);
    });

    // TEST 6: Task Creation
    it('creates new task with valid data', function () {
        $initialTaskCount = Task::where('project_id', $this->project->id)->count();

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskName', 'New Task')
            ->set('taskDescription', 'New task description')
            ->set('taskType', 'human')
            ->set('taskStatus', 'pending')
            ->set('taskEstimatedHours', 5)
            ->call('saveTask');

        expect(Task::where('project_id', $this->project->id)->count())->toBe($initialTaskCount + 1);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $this->project->id,
            'name' => 'New Task',
            'description' => 'New task description',
            'type' => 'human',
            'status' => 'pending',
        ]);
    });

    it('validates required fields when creating task', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskName', '')
            ->set('taskType', 'invalid')
            ->call('saveTask')
            ->assertHasErrors(['taskName', 'taskType']);
    });

    it('validates task name minimum length', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskName', 'AB')
            ->call('saveTask')
            ->assertHasErrors(['taskName' => 'min']);
    });

    it('validates due date must be in future', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskName', 'Future Task')
            ->set('taskDueDate', now()->subDays(1)->format('Y-m-d'))
            ->call('saveTask')
            ->assertHasErrors(['taskDueDate' => 'after']);
    });

    // TEST 7: Task Editing
    it('updates existing task with valid data', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'Original Name',
            'description' => 'Original description',
            'type' => 'human',
        ]);

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('editingTaskId', $task->id)
            ->set('taskName', 'Updated Name')
            ->set('taskDescription', 'Updated description')
            ->set('taskType', 'ai')
            ->call('saveTask');

        $task->refresh();
        expect($task->name)->toBe('Updated Name')
            ->and($task->description)->toBe('Updated description')
            ->and($task->type)->toBe('ai');
    });

    // TEST 8: Project Editing
    it('opens project editing mode', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('startEditingProject')
            ->assertSet('editingProject', true)
            ->assertSet('editName', $this->project->name)
            ->assertSet('editDescription', $this->project->description);
    });

    it('cancels project editing and resets fields', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('startEditingProject')
            ->set('editName', 'Changed Name')
            ->call('cancelEditingProject')
            ->assertSet('editingProject', false)
            ->assertSet('editName', $this->project->name); // Should reset to original
    });

    it('updates project with valid data', function () {
        $newStartDate = now()->format('Y-m-d');
        $newEndDate = now()->addDays(30)->format('Y-m-d');

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('editName', 'Updated Project Name')
            ->set('editDescription', 'Updated project description with more details')
            ->set('editStatus', 'planning')
            ->set('editDomain', 'technology')
            ->set('editStartDate', $newStartDate)
            ->set('editEndDate', $newEndDate)
            ->call('updateProject');

        $this->project->refresh();
        expect($this->project->name)->toBe('Updated Project Name')
            ->and($this->project->description)->toBe('Updated project description with more details')
            ->and($this->project->status)->toBe('planning')
            ->and($this->project->domain)->toBe('technology');
    });

    it('validates project update fields', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('editName', 'AB') // Too short
            ->set('editDescription', 'Short') // Too short
            ->call('updateProject')
            ->assertHasErrors(['editName' => 'min', 'editDescription' => 'min']);
    });

    it('validates end date must be after start date', function () {
        $startDate = now()->addDays(10)->format('Y-m-d');
        $endDate = now()->addDays(5)->format('Y-m-d'); // Before start

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('editName', 'Valid Name')
            ->set('editDescription', 'Valid description with enough text')
            ->set('editStatus', 'active')
            ->set('editDomain', 'software_development')
            ->set('editStartDate', $startDate)
            ->set('editEndDate', $endDate)
            ->call('updateProject')
            ->assertHasErrors(['editEndDate' => 'after']);
    });

    // TEST 9: Project Status Updates
    it('updates project status directly', function () {
        expect($this->project->status)->toBe('active');

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateProjectStatus', 'on_hold');

        $this->project->refresh();
        expect($this->project->status)->toBe('on_hold');
    });

    it('rejects invalid project status', function () {
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateProjectStatus', 'invalid_status');

        $this->project->refresh();
        expect($this->project->status)->toBe('active'); // Should not change
    });

    // TEST 10: Project Deletion
    it('deletes project and redirects', function () {
        expect(Project::find($this->project->id))->not->toBeNull();

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('deleteProject')
            ->assertRedirect(route('projects.index'));

        expect(Project::find($this->project->id))->toBeNull();
    });

    it('prevents deleting another users project', function () {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

        expect(function () {
            Livewire::test(ProjectDetail::class, ['projectId' => $otherProject->id])
                ->call('deleteProject');
        })->toThrow(\Exception::class);

        expect(Project::find($otherProject->id))->not->toBeNull(); // Should still exist
    });

    // TEST 11: Task Filtering
    it('filters tasks by search query', function () {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'Unique Search Term Task',
        ]);

        $component = Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskSearch', 'Unique Search Term');

        $filteredTasks = $component->get('filteredTasks');
        expect($filteredTasks)->toHaveCount(1)
            ->and($filteredTasks[0]->name)->toContain('Unique Search Term');
    });

    it('filters tasks by type', function () {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'ai',
        ]);
        Task::factory()->create([
            'project_id' => $this->project->id,
            'type' => 'human',
        ]);

        $component = Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskTypeFilter', 'ai');

        $filteredTasks = $component->get('filteredTasks');
        foreach ($filteredTasks as $task) {
            expect($task->type)->toBe('ai');
        }
    });

    it('filters tasks by status', function () {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed',
        ]);
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'pending',
        ]);

        $component = Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskStatusFilter', 'completed');

        $filteredTasks = $component->get('filteredTasks');
        foreach ($filteredTasks as $task) {
            expect($task->status)->toBe('completed');
        }
    });

    it('combines multiple filters', function () {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'AI Development Task',
            'type' => 'ai',
            'status' => 'in_progress',
        ]);

        $component = Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->set('taskSearch', 'Development')
            ->set('taskTypeFilter', 'ai')
            ->set('taskStatusFilter', 'in_progress');

        $filteredTasks = $component->get('filteredTasks');
        expect($filteredTasks)->toHaveCount(1)
            ->and($filteredTasks[0]->name)->toContain('Development')
            ->and($filteredTasks[0]->type)->toBe('ai')
            ->and($filteredTasks[0]->status)->toBe('in_progress');
    });

    // TEST 12: Authorization
    it('enforces project ownership for all operations', function () {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);
        $otherTask = Task::factory()->create(['project_id' => $otherProject->id]);

        // Try to access component with wrong project
        expect(function () {
            Livewire::test(ProjectDetail::class, ['projectId' => $otherProject->id]);
        })->toThrow(\Exception::class);
    });

    // TEST 13: Complex Task Status Workflow
    it('follows complete task lifecycle from generated to completed', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'generated',
        ]);

        $component = Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id]);

        // generated -> pending
        $component->call('updateTaskStatus', $task->id, 'pending');
        $task->refresh();
        expect($task->status)->toBe('pending');

        // pending -> in_progress
        $component->call('updateTaskStatus', $task->id, 'in_progress');
        $task->refresh();
        expect($task->status)->toBe('in_progress');

        // in_progress -> review
        $component->call('updateTaskStatus', $task->id, 'review');
        $task->refresh();
        expect($task->status)->toBe('review');

        // review -> completed
        $component->call('updateTaskStatus', $task->id, 'completed');
        $task->refresh();
        expect($task->status)->toBe('completed');
    });

    it('allows reopening completed tasks', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'completed',
        ]);

        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateTaskStatus', $task->id, 'in_progress');

        $task->refresh();
        expect($task->status)->toBe('in_progress');
    });

    it('handles blocked task state transitions', function () {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'in_progress',
        ]);

        // Mark as blocked
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateTaskStatus', $task->id, 'blocked');

        $task->refresh();
        expect($task->status)->toBe('blocked');

        // Unblock back to pending
        Livewire::test(ProjectDetail::class, ['projectId' => $this->project->id])
            ->call('updateTaskStatus', $task->id, 'pending');

        $task->refresh();
        expect($task->status)->toBe('pending');
    });
});
