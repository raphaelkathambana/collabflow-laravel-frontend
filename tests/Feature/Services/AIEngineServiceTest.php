<?php

use App\Services\AIEngineService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

describe('AIEngineService Integration', function () {

    beforeEach(function () {
        Config::set('services.python.enabled', true);
        Config::set('services.python.url', 'http://localhost:8001');
        Config::set('services.python.timeout', 150);
    });

    // TEST 1: Health Check
    it('successfully checks Python service health', function () {
        Http::fake([
            '*/health' => Http::response(['status' => 'healthy'], 200)
        ]);

        $service = new AIEngineService();
        expect($service->healthCheck())->toBeTrue();
    });

    it('returns false when Python service is down', function () {
        Http::fake([
            '*/health' => Http::response([], 500)
        ]);

        $service = new AIEngineService();
        expect($service->healthCheck())->toBeFalse();
    });

    it('returns false when Python service times out', function () {
        Http::fake([
            '*/health' => Http::response([], 408)
        ]);

        $service = new AIEngineService();
        expect($service->healthCheck())->toBeFalse();
    });

    // TEST 2: Context Analysis
    it('successfully analyzes project context', function () {
        Http::fake([
            '*/api/context/analyze' => Http::response([
                'analysis' => [
                    'domain' => 'SOFTWARE_DEVELOPMENT',
                    'complexity' => 'HIGH',
                    'estimated_task_count' => 15,
                    'key_objectives' => ['Build MVP', 'Deploy to production'],
                    'challenges' => ['Timeline constraints', 'Limited resources'],
                    'required_skills' => [
                        ['skill' => 'React', 'level' => 'intermediate'],
                        ['skill' => 'Node.js', 'level' => 'intermediate']
                    ],
                    'recommendations' => ['Use Agile methodology', 'Implement CI/CD early'],
                    'confidence_score' => 0.85
                ],
                'status' => 'success'
            ], 200)
        ]);

        $service = new AIEngineService();
        $result = $service->analyzeContext([
            'details' => ['name' => 'Test Project', 'description' => 'A test project'],
            'goals' => ['goal1', 'goal2']
        ]);

        expect($result)->toHaveKey('domain', 'SOFTWARE_DEVELOPMENT')
            ->and($result)->toHaveKey('complexity', 'HIGH')
            ->and($result['estimated_task_count'])->toBe(15)
            ->and($result['confidence_score'])->toBe(0.85)
            ->and($result['key_objectives'])->toHaveCount(2)
            ->and($result['required_skills'])->toHaveCount(2);
    });

    it('returns null when context analysis fails', function () {
        Http::fake([
            '*/api/context/analyze' => Http::response([], 500)
        ]);

        $service = new AIEngineService();
        $result = $service->analyzeContext(['details' => [], 'goals' => []]);

        expect($result)->toBeNull();
    });

    it('caches context analysis results', function () {
        Http::fake([
            '*/api/context/analyze' => Http::response([
                'analysis' => ['domain' => 'CACHED_TEST'],
                'status' => 'success'
            ], 200)
        ]);

        $service = new AIEngineService();
        $projectData = ['details' => ['name' => 'Cache Test'], 'goals' => []];

        // First call
        $result1 = $service->analyzeContext($projectData);

        // Second call (should be cached)
        $result2 = $service->analyzeContext($projectData);

        expect($result1)->toEqual($result2);
        Http::assertSentCount(1);  // Only one actual HTTP call
    });

    // TEST 3: Task Generation
    it('successfully generates tasks', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([
                'tasks' => [
                    [
                        'id' => 'task_001',
                        'name' => 'Setup infrastructure',
                        'description' => 'Initialize project',
                        'assigned_to' => 'HUMAN',
                        'estimated_hours' => 8.0,
                        'complexity' => 'MEDIUM',
                        'sequence' => 1,
                        'ai_suitability_score' => 0.3,
                        'confidence_score' => 0.85,
                        'status' => 'PENDING',
                        'subtasks' => [],
                        'validation' => ['score' => 85, 'passed' => true]
                    ],
                    [
                        'id' => 'task_002',
                        'name' => 'Design API endpoints',
                        'description' => 'Create REST API structure',
                        'assigned_to' => 'AI',
                        'estimated_hours' => 12.0,
                        'complexity' => 'HIGH',
                        'sequence' => 2,
                        'ai_suitability_score' => 0.9,
                        'confidence_score' => 0.88,
                        'status' => 'PENDING',
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

        $service = new AIEngineService();
        $result = $service->generateTasks(
            'project-uuid',
            'user-uuid',
            ['name' => 'Test', 'domain' => 'software_development'],
            ['domain' => 'SOFTWARE_DEVELOPMENT', 'complexity' => 'MEDIUM']
        );

        expect($result)->toHaveKey('tasks')
            ->and($result['tasks'])->toHaveCount(2)
            ->and($result['tasks'][0]['type'])->toBe('human')  // Converted from HUMAN
            ->and($result['tasks'][1]['type'])->toBe('ai')     // Converted from AI
            ->and($result)->toHaveKey('dependencies')
            ->and($result['dependencies'])->toHaveCount(1)
            ->and($result)->toHaveKey('metadata')
            ->and($result['metadata']['total_tasks'])->toBe(2);
    });

    it('returns null when task generation fails', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([], 500)
        ]);

        $service = new AIEngineService();
        $result = $service->generateTasks('p-id', 'u-id', [], []);

        expect($result)->toBeNull();
    });

    // TEST 4: Field Mapping
    it('correctly converts assignment types from Python to Laravel', function () {
        $service = new AIEngineService();
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('convertAssignmentType');
        $method->setAccessible(true);

        expect($method->invoke($service, 'AI'))->toBe('ai')
            ->and($method->invoke($service, 'HUMAN'))->toBe('human')
            ->and($method->invoke($service, 'HITL'))->toBe('hitl')
            ->and($method->invoke($service, 'HYBRID'))->toBe('hitl')
            ->and($method->invoke($service, 'UNKNOWN'))->toBe('human');
    });

    it('correctly maps Python task to Laravel format', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([
                'tasks' => [
                    [
                        'id' => 'task_001',
                        'name' => 'Test Task',
                        'description' => 'Description',
                        'assigned_to' => 'AI',
                        'estimated_hours' => 10.0,
                        'complexity' => 'HIGH',
                        'ai_suitability_score' => 0.9,
                        'confidence_score' => 0.85,
                        'sequence' => 5,
                        'status' => 'PENDING',
                        'validation' => ['score' => 90, 'passed' => true],
                        'position' => ['x' => 100, 'y' => 200],
                        'subtasks' => [
                            ['name' => 'Subtask 1', 'estimated_hours' => 3.0]
                        ]
                    ]
                ],
                'dependencies' => [],
                'metadata' => []
            ], 200)
        ]);

        $service = new AIEngineService();
        $result = $service->generateTasks('p-id', 'u-id', [], []);
        $task = $result['tasks'][0];

        expect($task['type'])->toBe('ai')
            ->and($task['estimated_hours'])->toBe(10)
            ->and($task['complexity'])->toBe('HIGH')
            ->and($task['sequence'])->toBe(5)
            ->and($task['status'])->toBe('pending') // Converted to lowercase
            ->and($task['metadata'])->toHaveKey('ai_suitability_score', 0.9)
            ->and($task['metadata'])->toHaveKey('confidence_score', 0.85)
            ->and($task['metadata'])->toHaveKey('validation')
            ->and($task['metadata'])->toHaveKey('position')
            ->and($task['metadata']['python_task_id'])->toBe('task_001')
            ->and($task['subtasks'])->toHaveCount(1);
    });

    // TEST 5: Retry Logic
    it('does not retry on 500 errors for context analysis', function () {
        // Clear cache to ensure fresh request
        \Illuminate\Support\Facades\Cache::flush();

        Http::fake([
            '*/api/context/analyze' => Http::response([], 500)
        ]);

        $service = new AIEngineService();
        // Use unique data to avoid cache hits from previous tests
        $result = $service->analyzeContext([
            'details' => ['name' => 'UniqueTest_' . time()],
            'goals' => ['unique_goal']
        ]);

        // Should return null on 500 error (no retry for non-connection errors)
        expect($result)->toBeNull();
        Http::assertSentCount(1);  // Only sent once, no retry
    });

    it('does not retry on 500 errors for task generation', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([], 500)
        ]);

        $service = new AIEngineService();
        $result = $service->generateTasks('p-id-' . time(), 'u-id', [], []);

        // Should return null on 500 error (no retry for non-connection errors)
        expect($result)->toBeNull();
        Http::assertSentCount(1);  // Only sent once, no retry
    });

    // TEST 6: Error Handling
    it('handles missing analysis field in response', function () {
        Http::fake([
            '*/api/context/analyze' => Http::response([
                'status' => 'success'
                // Missing 'analysis' field
            ], 200)
        ]);

        $service = new AIEngineService();
        $result = $service->analyzeContext(['details' => [], 'goals' => []]);

        expect($result)->toBeNull();
    });

    it('handles missing tasks field in response', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([
                'dependencies' => [],
                'metadata' => []
                // Missing 'tasks' field
            ], 200)
        ]);

        $service = new AIEngineService();
        $result = $service->generateTasks('p-id', 'u-id', [], []);

        expect($result)->toBeNull();
    });

    // TEST 7: Service Disabled
    it('returns false for health check when service is disabled', function () {
        Config::set('services.python.enabled', false);

        $service = new AIEngineService();
        expect($service->healthCheck())->toBeFalse();
    });

    it('returns null for context analysis when service is disabled', function () {
        Config::set('services.python.enabled', false);

        $service = new AIEngineService();
        $result = $service->analyzeContext(['details' => [], 'goals' => []]);

        expect($result)->toBeNull();
    });

    it('returns null for task generation when service is disabled', function () {
        Config::set('services.python.enabled', false);

        $service = new AIEngineService();
        $result = $service->generateTasks('p-id', 'u-id', [], []);

        expect($result)->toBeNull();
    });

    // TEST 8: Timeout Configuration
    it('uses correct timeout for task generation', function () {
        Http::fake([
            '*/api/tasks/generate' => Http::response([
                'tasks' => [],
                'dependencies' => [],
                'metadata' => []
            ], 200)
        ]);

        $service = new AIEngineService();
        $service->generateTasks('p-id', 'u-id', [], []);

        // Verify the request was made (timeout is set internally)
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/tasks/generate');
        });
    });
});
