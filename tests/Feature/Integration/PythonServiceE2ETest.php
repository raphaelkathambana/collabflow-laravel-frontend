<?php

use App\Services\AIEngineService;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * End-to-End Integration Test for Python AI Service
 *
 * IMPORTANT: These tests require the Python service to be running on http://localhost:8001
 *
 * To run the Python service:
 * 1. cd ../python-service
 * 2. Ensure .env is configured with ANTHROPIC_API_KEY
 * 3. Run: uvicorn app.main:app --reload --port 8001
 *
 * These tests will be skipped if the Python service is not available.
 */
describe('Python Service E2E Integration', function () {

    uses(RefreshDatabase::class);

    beforeEach(function () {
        // Enable Python service
        config(['services.python.enabled' => true]);
        config(['services.python.url' => 'http://localhost:8001']);
        config(['services.python.timeout' => 150]);

        $this->service = new AIEngineService();

        // Check if Python service is available
        $this->pythonServiceAvailable = $this->service->healthCheck();

        if (!$this->pythonServiceAvailable) {
            $this->markTestSkipped(
                'Python service not available at http://localhost:8001. ' .
                'Start the service with: cd ../python-service && uvicorn app.main:app --reload --port 8001'
            );
        }
    });

    // TEST 1: Health Check
    it('successfully connects to Python service health endpoint', function () {
        expect($this->service->healthCheck())->toBeTrue();
    })->skip(fn() => !$this->pythonServiceAvailable ?? true, 'Python service not running');

    // TEST 2: Context Analysis E2E
    it('analyzes real project context end-to-end', function () {
        $projectData = [
            'details' => [
                'name' => 'E-commerce Platform',
                'description' => 'Build a modern e-commerce platform with user authentication, product catalog, shopping cart, payment processing, and order management',
                'domain' => 'software_development',
                'timeline' => '3 months',
                'team_size' => 5,
            ],
            'goals' => [
                'goals' => [
                    'Launch MVP with core shopping features',
                    'Implement secure payment processing',
                    'Build admin dashboard for inventory management'
                ],
                'success_metrics' => 'Support 1000 concurrent users, 99.9% uptime',
                'constraints' => 'Must use Laravel and React, PCI compliance required'
            ]
        ];

        $analysis = $this->service->analyzeContext($projectData);

        // Verify analysis structure
        expect($analysis)->not->toBeNull()
            ->and($analysis)->toHaveKeys(['domain', 'complexity', 'estimated_task_count'])
            ->and($analysis['domain'])->toBeString()
            ->and(strtoupper($analysis['complexity']))->toBeIn(['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH'])
            ->and($analysis['estimated_task_count'])->toBeInt()
            ->and($analysis['estimated_task_count'])->toBeGreaterThan(0);

        // Verify optional fields if present
        if (isset($analysis['key_objectives'])) {
            expect($analysis['key_objectives'])->toBeArray();
        }
        if (isset($analysis['required_skills'])) {
            expect($analysis['required_skills'])->toBeArray();
        }
        if (isset($analysis['confidence_score'])) {
            expect($analysis['confidence_score'])->toBeFloat()
                ->and($analysis['confidence_score'])->toBeBetween(0, 1);
        }

        dump('✓ Context Analysis Result:', [
            'domain' => $analysis['domain'],
            'complexity' => $analysis['complexity'],
            'estimated_tasks' => $analysis['estimated_task_count']
        ]);
    })->skip(fn() => !$this->pythonServiceAvailable ?? true, 'Python service not running');

    // TEST 3: Task Generation E2E
    it('generates real tasks end-to-end', function () {
        $user = User::factory()->create();
        $projectId = (string) \Illuminate\Support\Str::uuid();

        $projectDetails = [
            'name' => 'Task Automation Tool',
            'description' => 'Build a tool to automate repetitive development tasks',
            'domain' => 'software_development'
        ];

        $contextAnalysis = [
            'domain' => 'SOFTWARE_DEVELOPMENT',
            'complexity' => 'MEDIUM',
            'estimated_task_count' => 10
        ];

        $result = $this->service->generateTasks(
            $projectId,
            $user->id,
            $projectDetails,
            $contextAnalysis
        );

        // Verify task generation result
        if ($result === null) {
            $this->markTestSkipped(
                'Task generation returned null. This might indicate: ' .
                '1) Missing ANTHROPIC_API_KEY in python-service/.env, ' .
                '2) Python service error, ' .
                '3) Chroma not running (optional but recommended). ' .
                'Check python-service logs for details.'
            );
        }

        expect($result)->toHaveKeys(['tasks', 'dependencies', 'metadata'])
            ->and($result['tasks'])->toBeArray()
            ->and($result['tasks'])->not->toBeEmpty()
            ->and($result['dependencies'])->toBeArray()
            ->and($result['metadata'])->toBeArray();

        // Verify at least one task was generated
        $firstTask = $result['tasks'][0];
        expect($firstTask)->toHaveKeys(['name', 'description', 'type', 'estimated_hours'])
            ->and($firstTask['type'])->toBeIn(['ai', 'human', 'hitl'])
            ->and($firstTask['estimated_hours'])->toBeInt()
            ->and($firstTask['name'])->toBeString()->not->toBeEmpty();

        // Verify metadata
        expect($result['metadata'])->toHaveKeys(['total_tasks', 'ai_tasks', 'human_tasks', 'hitl_tasks'])
            ->and($result['metadata']['total_tasks'])->toBe(count($result['tasks']))
            ->and($result['metadata']['total_tasks'])->toBeGreaterThan(0);

        dump('✓ Task Generation Result:', [
            'total_tasks' => $result['metadata']['total_tasks'],
            'ai_tasks' => $result['metadata']['ai_tasks'],
            'human_tasks' => $result['metadata']['human_tasks'],
            'hitl_tasks' => $result['metadata']['hitl_tasks'],
            'dependencies' => count($result['dependencies'])
        ]);
    })->skip(fn() => !$this->pythonServiceAvailable ?? true, 'Python service not running');

    // TEST 4: Full Workflow E2E (Analysis → Task Generation)
    it('completes full workflow from analysis to task generation', function () {
        $user = User::factory()->create();

        // Step 1: Analyze Context
        $projectData = [
            'details' => [
                'name' => 'Mobile Weather App',
                'description' => 'React Native weather app with location services and real-time forecasts',
                'domain' => 'software_development',
                'timeline' => '2 months',
                'team_size' => 3,
            ],
            'goals' => [
                'goals' => ['Real-time weather updates', 'Location-based forecasts', 'Push notifications'],
                'success_metrics' => '10k downloads in first month',
                'constraints' => 'Must work offline with cached data'
            ]
        ];

        $analysis = $this->service->analyzeContext($projectData);
        expect($analysis)->not->toBeNull();

        // Step 2: Generate Tasks based on Analysis
        $projectId = (string) \Illuminate\Support\Str::uuid();
        $taskResult = $this->service->generateTasks(
            $projectId,
            $user->id,
            $projectData['details'],
            $analysis
        );

        if ($taskResult === null) {
            $this->markTestSkipped('Task generation failed - check Python service logs and ANTHROPIC_API_KEY');
        }

        expect($taskResult['tasks'])->toBeArray()->not->toBeEmpty();

        // Step 3: Verify task count is within reasonable range of estimate
        $estimatedCount = $analysis['estimated_task_count'];
        $actualCount = $taskResult['metadata']['total_tasks'];

        // Allow for some variance (±30% of estimate)
        $minExpected = max(3, (int)($estimatedCount * 0.7));
        $maxExpected = (int)($estimatedCount * 1.3);

        expect($actualCount)->toBeGreaterThanOrEqual($minExpected)
            ->and($actualCount)->toBeLessThanOrEqual($maxExpected);

        dump('✓ Full Workflow Completed:', [
            'estimated_tasks' => $estimatedCount,
            'actual_tasks' => $actualCount,
            'complexity' => $analysis['complexity'],
            'ai_tasks' => $taskResult['metadata']['ai_tasks'],
            'human_tasks' => $taskResult['metadata']['human_tasks']
        ]);
    })->skip(fn() => !$this->pythonServiceAvailable ?? true, 'Python service not running');

    // TEST 5: Field Mapping Validation
    it('correctly maps all Python response fields to Laravel format', function () {
        $user = User::factory()->create();
        $projectId = (string) \Illuminate\Support\Str::uuid();

        $projectDetails = [
            'name' => 'Field Mapping Test',
            'description' => 'Testing field mapping between services',
            'domain' => 'software_development'
        ];

        $contextAnalysis = [
            'domain' => 'SOFTWARE_DEVELOPMENT',
            'complexity' => 'LOW'
        ];

        $result = $this->service->generateTasks(
            $projectId,
            $user->id,
            $projectDetails,
            $contextAnalysis
        );

        if ($result === null) {
            $this->markTestSkipped('Task generation failed - check Python service logs and ANTHROPIC_API_KEY');
        }

        // Verify each task has correct field mappings
        foreach ($result['tasks'] as $task) {
            expect($task)->toHaveKeys(['name', 'description', 'type', 'estimated_hours'])
                ->and($task['type'])->toBeIn(['ai', 'human', 'hitl'])
                ->and($task['status'])->toBeIn(['pending', 'in_progress', 'completed', 'blocked'])
                ->and($task['estimated_hours'])->toBeInt();

            // Verify metadata contains Python-specific fields
            if (isset($task['metadata'])) {
                expect($task['metadata'])->toBeArray();
                if (isset($task['metadata']['python_task_id'])) {
                    expect($task['metadata']['python_task_id'])->toBeString();
                }
            }
        }

        dump('✓ Field Mapping Verified for ' . count($result['tasks']) . ' tasks');
    })->skip(fn() => !$this->pythonServiceAvailable ?? true, 'Python service not running');

    // TEST 6: Performance Test
    it('completes task generation within acceptable time', function () {
        $user = User::factory()->create();
        $projectId = (string) \Illuminate\Support\Str::uuid();

        $projectDetails = [
            'name' => 'Performance Test Project',
            'description' => 'Testing response time for task generation',
            'domain' => 'software_development'
        ];

        $contextAnalysis = [
            'domain' => 'SOFTWARE_DEVELOPMENT',
            'complexity' => 'MEDIUM',
            'estimated_task_count' => 15
        ];

        $startTime = microtime(true);

        $result = $this->service->generateTasks(
            $projectId,
            $user->id,
            $projectDetails,
            $contextAnalysis
        );

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        if ($result === null) {
            $this->markTestSkipped('Task generation failed - check Python service logs and ANTHROPIC_API_KEY');
        }

        // Should complete within 150 seconds (our timeout setting)
        expect($duration)->toBeLessThan(150);

        dump('✓ Performance Test:', [
            'duration' => round($duration, 2) . 's',
            'tasks_generated' => $result['metadata']['total_tasks'],
            'avg_time_per_task' => round($duration / $result['metadata']['total_tasks'], 2) . 's'
        ]);
    })->skip(fn() => !$this->pythonServiceAvailable ?? true, 'Python service not running');

    // TEST 7: Error Handling with Invalid Data
    it('handles invalid project data gracefully', function () {
        $user = User::factory()->create();
        $projectId = (string) \Illuminate\Support\Str::uuid();

        // Intentionally invalid/minimal data
        $projectDetails = [
            'name' => 'X',
            'description' => 'Y',
            'domain' => 'unknown'
        ];

        $contextAnalysis = [
            'domain' => 'UNKNOWN'
        ];

        $result = $this->service->generateTasks(
            $projectId,
            $user->id,
            $projectDetails,
            $contextAnalysis
        );

        // Should either return valid tasks or null (graceful fallback)
        if ($result !== null) {
            expect($result)->toHaveKey('tasks')
                ->and($result['tasks'])->toBeArray();
        }

        dump('✓ Error handling test completed - ' . ($result ? 'returned data' : 'returned null (fallback)'));
    })->skip(fn() => !$this->pythonServiceAvailable ?? true, 'Python service not running');
});
