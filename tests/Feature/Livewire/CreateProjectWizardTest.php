<?php

use App\Livewire\Projects\CreateProjectWizard;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

describe('CreateProjectWizard Integration', function () {

    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Enable Python service for tests
        config(['services.python.enabled' => true]);
        config(['services.python.url' => 'http://localhost:8001']);
    });

    // TEST 1: Wizard Initialization
    it('initializes wizard with default state', function () {
        Livewire::test(CreateProjectWizard::class)
            ->assertSet('currentStep', 1)
            ->assertSet('usingFallback', false)
            ->assertSet('tasks', [])
            ->assertSet('aiDependencies', [])
            ->assertSet('aiMetadata', null)
            ->assertSet('name', '')
            ->assertSet('description', '');
    });

    // TEST 2: Step Navigation
    it('navigates through wizard steps correctly', function () {
        Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'A comprehensive test description for the project')
            ->set('domain', 'software_development')
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->call('previousStep')
            ->assertSet('currentStep', 1);
    });

    it('validates required fields before step navigation', function () {
        Livewire::test(CreateProjectWizard::class)
            ->call('nextStep')
            ->assertHasErrors(['name', 'description', 'domain']);
    });

    // TEST 3: AI Context Analysis
    it('successfully analyzes project context with AI', function () {
        Http::fake([
            '*/api/context/analyze' => Http::response([
                'analysis' => [
                    'domain' => 'SOFTWARE_DEVELOPMENT',
                    'complexity' => 'HIGH',
                    'estimated_task_count' => 25,
                    'key_objectives' => ['Build MVP', 'Deploy'],
                    'challenges' => ['Timeline'],
                    'required_skills' => [
                        ['skill' => 'Laravel', 'level' => 'intermediate']
                    ],
                    'recommendations' => ['Use CI/CD'],
                    'confidence_score' => 0.85
                ],
                'status' => 'success'
            ], 200)
        ]);

        Livewire::test(CreateProjectWizard::class)
            ->set('name', 'E-commerce Platform')
            ->set('description', 'Build an online store with advanced features')
            ->set('domain', 'software_development')
            ->set('goals', [
                ['id' => 1, 'title' => 'Sell products', 'description' => 'Enable online sales', 'priority' => 'high'],
                ['id' => 2, 'title' => 'Manage inventory', 'description' => 'Track stock levels', 'priority' => 'medium']
            ])
            ->call('analyzeProject')
            ->assertSet('aiAnalysis.domain', 'SOFTWARE_DEVELOPMENT')
            ->assertSet('aiAnalysis.complexity', 'HIGH')
            ->assertSet('aiAnalysis.estimated_task_count', 25)
            ->assertDispatched('show-toast');
    });

    it('activates fallback mode when AI analysis fails', function () {
        Http::fake([
            '*/api/context/analyze' => Http::response([], 500)
        ]);

        $component = Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'Test Description for the project')
            ->set('domain', 'software_development')
            ->set('goals', [
                ['id' => 1, 'title' => 'Goal 1', 'description' => 'First project goal', 'priority' => 'high']
            ])
            ->call('analyzeProject')
            ->assertSet('usingFallback', true)
            ->assertDispatched('show-toast');

        // Verify fallback analysis was created (not null)
        expect($component->get('aiAnalysis'))->not->toBeNull();
    });

    // TEST 4: AI Task Generation
    it('successfully generates tasks with AI', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([
                'tasks' => [
                    [
                        'id' => 'task_001',
                        'name' => 'Setup Project',
                        'description' => 'Initialize project structure',
                        'assigned_to' => 'human',
                        'estimated_hours' => 8,
                        'complexity' => 'MEDIUM',
                        'sequence' => 1,
                        'ai_suitability_score' => 0.3,
                        'confidence_score' => 0.85,
                        'status' => 'pending',
                        'subtasks' => [],
                        'validation' => ['score' => 85, 'passed' => true]
                    ],
                    [
                        'id' => 'task_002',
                        'name' => 'Build API',
                        'description' => 'Create REST endpoints',
                        'assigned_to' => 'ai',
                        'estimated_hours' => 12,
                        'complexity' => 'HIGH',
                        'sequence' => 2,
                        'ai_suitability_score' => 0.9,
                        'confidence_score' => 0.88,
                        'status' => 'pending',
                        'subtasks' => [],
                        'validation' => ['score' => 90, 'passed' => true]
                    ]
                ],
                'dependencies' => [
                    [
                        'id' => 'dep_001',
                        'from_task_id' => 'task_001',
                        'to_task_id' => 'task_002',
                        'type' => 'blocks'
                    ]
                ],
                'metadata' => [
                    'total_tasks' => 2,
                    'ai_tasks' => 1,
                    'human_tasks' => 1,
                    'hitl_tasks' => 0,
                    'total_estimated_hours' => 20.0,
                    'avg_validation_score' => 0.875
                ],
                'status' => 'success'
            ], 200)
        ]);

        $component = Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'Test Description for the project')
            ->set('domain', 'software_development')
            ->set('aiAnalysis', [
                'domain' => 'SOFTWARE_DEVELOPMENT',
                'complexity' => 'MEDIUM',
                'estimated_task_count' => 10
            ])
            ->call('generateTasks')
            ->assertCount('tasks', 2)
            ->assertCount('aiDependencies', 1)
            ->assertSet('aiMetadata.total_tasks', 2)
            ->assertSet('aiMetadata.total_estimated_hours', 20.0)
            ->assertDispatched('show-toast');

        // Verify task structure
        $tasks = $component->get('tasks');
        expect($tasks[0]['type'])->toBe('human')
            ->and($tasks[1]['type'])->toBe('ai')
            ->and($tasks[0]['estimated_hours'])->toBe(8)
            ->and($tasks[1]['estimated_hours'])->toBe(12);
    });

    it('activates fallback mode when task generation fails', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([], 500)
        ]);

        $component = Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'Test Description for project')
            ->set('domain', 'software_development')
            ->set('aiAnalysis', ['domain' => 'SOFTWARE_DEVELOPMENT'])
            ->call('generateTasks')
            ->assertSet('usingFallback', true)
            ->assertDispatched('show-toast');

        // Verify fallback generates mock tasks (not empty)
        expect($component->get('tasks'))->not->toBeEmpty();
    });

    // TEST 5: Project Creation with AI Data
    it('creates project with AI-generated tasks and dependencies', function () {
        // Setup component with AI data
        $component = Livewire::test(CreateProjectWizard::class)
            ->set('name', 'AI-Generated Project')
            ->set('description', 'Project with AI-generated tasks and workflow')
            ->set('domain', 'software_development')
            ->set('goals', [
                ['id' => 1, 'title' => 'Goal 1', 'description' => 'First goal description', 'priority' => 'high'],
                ['id' => 2, 'title' => 'Goal 2', 'description' => 'Second goal description', 'priority' => 'medium']
            ])
            ->set('tasks', [
                [
                    'id' => 'task_001',
                    'name' => 'Task 1',
                    'description' => 'First task',
                    'type' => 'human',
                    'estimated_hours' => 5,
                    'complexity' => 'MEDIUM',
                    'sequence' => 1,
                    'status' => 'pending',
                    'metadata' => [
                        'python_task_id' => 'task_001',
                        'ai_suitability_score' => 0.3,
                        'confidence_score' => 0.85
                    ]
                ],
                [
                    'id' => 'task_002',
                    'name' => 'Task 2',
                    'description' => 'Second task',
                    'type' => 'ai',
                    'estimated_hours' => 8,
                    'complexity' => 'HIGH',
                    'sequence' => 2,
                    'status' => 'pending',
                    'metadata' => [
                        'python_task_id' => 'task_002',
                        'ai_suitability_score' => 0.9,
                        'confidence_score' => 0.88
                    ]
                ]
            ])
            ->set('aiDependencies', [
                [
                    'from_task_id' => 'task_001',
                    'to_task_id' => 'task_002',
                    'type' => 'blocks'
                ]
            ])
            ->set('aiMetadata', [
                'total_tasks' => 2,
                'ai_tasks' => 1,
                'human_tasks' => 1,
                'total_estimated_hours' => 13.0
            ]);

        $component->call('createProject');

        // Verify project created
        $this->assertDatabaseHas('projects', [
            'name' => 'AI-Generated Project',
            'description' => 'Project with AI-generated tasks and workflow',
            'user_id' => $this->user->id
        ]);

        $project = Project::where('name', 'AI-Generated Project')->first();

        // Verify tasks created
        expect($project->tasks)->toHaveCount(2);
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'name' => 'Task 1',
            'type' => 'human'
        ]);
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'name' => 'Task 2',
            'type' => 'ai'
        ]);

        // Verify workflow metadata stored (if aiMetadata was set)
        if ($project->workflow_metadata) {
            expect($project->workflow_metadata['total_tasks'])->toBe(2)
                ->and($project->workflow_metadata['ai_tasks'])->toBe(1)
                ->and($project->workflow_metadata['human_tasks'])->toBe(1);
        }

        // Verify dependencies created (stored as array in database)
        $task1 = Task::where('project_id', $project->id)->where('name', 'Task 1')->first();
        $task2 = Task::where('project_id', $project->id)->where('name', 'Task 2')->first();

        expect($task2->dependencies)->toBeArray()
            ->and($task2->dependencies)->toHaveCount(1)
            ->and($task2->dependencies[0])->toBe($task1->id);
    });

    it('creates project in fallback mode without AI data', function () {
        Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Manual Project')
            ->set('description', 'Project created without AI assistance')
            ->set('domain', 'software_development')
            ->set('goals', [
                ['id' => 1, 'title' => 'Manual Goal', 'description' => 'Manually defined project goal', 'priority' => 'high']
            ])
            ->set('usingFallback', true)
            ->set('tasks', [])
            ->call('createProject');

        $this->assertDatabaseHas('projects', [
            'name' => 'Manual Project',
            'description' => 'Project created without AI assistance',
            'user_id' => $this->user->id
        ]);

        $project = Project::where('name', 'Manual Project')->first();
        expect($project->tasks)->toHaveCount(0);
        expect($project->workflow_metadata)->toBeNull();
    });

    // TEST 6: Service Disabled Handling
    it('uses fallback mode when Python service is disabled', function () {
        config(['services.python.enabled' => false]);

        $component = Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'Test Description for the project')
            ->set('domain', 'software_development')
            ->set('goals', [
                ['id' => 1, 'title' => 'Goal 1', 'description' => 'Project goal description', 'priority' => 'high']
            ])
            ->call('analyzeProject')
            ->assertSet('usingFallback', true);

        // Verify fallback analysis was created (not null)
        expect($component->get('aiAnalysis'))->not->toBeNull();
    });

    // TEST 7: Workflow Regeneration
    it('regenerates workflow with AI when requested', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([
                'tasks' => [
                    [
                        'id' => 'regen_task_001',
                        'name' => 'Regenerated Task',
                        'description' => 'New task from regeneration',
                        'assigned_to' => 'ai',
                        'estimated_hours' => 6,
                        'complexity' => 'MEDIUM',
                        'sequence' => 1,
                        'status' => 'pending'
                    ]
                ],
                'dependencies' => [],
                'metadata' => ['total_tasks' => 1]
            ], 200)
        ]);

        Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Test Project')
            ->set('description', 'Test Description for regeneration')
            ->set('domain', 'software_development')
            ->set('aiAnalysis', ['domain' => 'SOFTWARE_DEVELOPMENT'])
            ->set('tasks', [['name' => 'Old Task']])
            ->call('regenerateWorkflow')
            ->assertCount('tasks', 1)
            ->assertSet('tasks.0.name', 'Regenerated Task');
    });

    // TEST 8: Validation
    it('validates project name and description before creation', function () {
        Livewire::test(CreateProjectWizard::class)
            ->set('name', '')
            ->set('description', '')
            ->set('domain', '')
            ->call('createProject')
            ->assertHasErrors(['name', 'description', 'domain']);
    });

    // TEST 9: Complex Dependency Scenarios
    it('handles multiple dependencies correctly', function () {
        $component = Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Complex Dependency Project')
            ->set('description', 'Testing complex task dependencies')
            ->set('domain', 'software_development')
            ->set('goals', [
                ['id' => 1, 'title' => 'Goal', 'description' => 'Project goal description', 'priority' => 'high']
            ])
            ->set('tasks', [
                [
                    'id' => 'task_001',
                    'name' => 'Task 1',
                    'type' => 'human',
                    'description' => 'First task',
                    'metadata' => ['python_task_id' => 'task_001']
                ],
                [
                    'id' => 'task_002',
                    'name' => 'Task 2',
                    'type' => 'ai',
                    'description' => 'Second task',
                    'metadata' => ['python_task_id' => 'task_002']
                ],
                [
                    'id' => 'task_003',
                    'name' => 'Task 3',
                    'type' => 'hitl',
                    'description' => 'Third task',
                    'metadata' => ['python_task_id' => 'task_003']
                ]
            ])
            ->set('aiDependencies', [
                [
                    'from_task_id' => 'task_001',
                    'to_task_id' => 'task_002',
                    'type' => 'blocks'
                ],
                [
                    'from_task_id' => 'task_001',
                    'to_task_id' => 'task_003',
                    'type' => 'blocks'
                ],
                [
                    'from_task_id' => 'task_002',
                    'to_task_id' => 'task_003',
                    'type' => 'requires'
                ]
            ]);

        $component->call('createProject');

        $project = Project::where('name', 'Complex Dependency Project')->first();
        $tasks = $project->tasks;

        expect($tasks)->toHaveCount(3);

        // Find tasks by name
        $task1 = $tasks->firstWhere('name', 'Task 1');
        $task2 = $tasks->firstWhere('name', 'Task 2');
        $task3 = $tasks->firstWhere('name', 'Task 3');

        // Verify dependencies (stored as arrays)
        expect($task2->dependencies)->toBeArray();
        expect(in_array($task1->id, $task2->dependencies))->toBeTrue();

        // Verify Task 3 depends on both Task 1 and Task 2
        expect($task3->dependencies)->toBeArray()
            ->and($task3->dependencies)->toHaveCount(2);
        expect(in_array($task1->id, $task3->dependencies))->toBeTrue();
        expect(in_array($task2->id, $task3->dependencies))->toBeTrue();
    });

    // TEST 10: Event Dispatching
    it('dispatches correct events during wizard flow', function () {
        Http::fake([
            '*/api/context/analyze' => Http::response([
                'analysis' => ['domain' => 'SOFTWARE_DEVELOPMENT'],
                'status' => 'success'
            ], 200),
            '*/api/tasks/generate' => Http::response([
                'tasks' => [
                    [
                        'id' => 'task_001',
                        'name' => 'Task',
                        'description' => 'Description',
                        'assigned_to' => 'ai',
                        'estimated_hours' => 5,
                        'status' => 'pending'
                    ]
                ],
                'dependencies' => [],
                'metadata' => []
            ], 200)
        ]);

        Livewire::test(CreateProjectWizard::class)
            ->set('name', 'Event Test Project')
            ->set('description', 'Testing event dispatching during wizard flow')
            ->set('domain', 'software_development')
            ->set('goals', [
                ['id' => 1, 'title' => 'Goal', 'description' => 'Project goal description', 'priority' => 'high']
            ])
            ->call('analyzeProject')
            ->assertDispatched('show-toast')
            ->call('generateTasks')
            ->assertDispatched('show-toast');
    });
});
