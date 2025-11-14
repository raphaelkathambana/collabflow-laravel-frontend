<?php

/**
 * CollabFlow AI Engine Service Test Script
 *
 * Features:
 * - Comprehensive error handling with detailed messages
 * - Request/response logging for debugging
 * - Performance timing for each operation
 * - Data validation at each step
 * - Optional verbose mode
 * - Results saved to JSON files for inspection
 * - Color-coded console output
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AIEngineService;
use Illuminate\Support\Facades\Log;

// ============================================================================
// Configuration
// ============================================================================

$VERBOSE = true; // Set to true for detailed output
$SAVE_RESPONSES = true; // Save API responses to files
$OUTPUT_DIR = __DIR__ . '/tests/outputs';

// Create output directory if it doesn't exist
if ($SAVE_RESPONSES && !is_dir($OUTPUT_DIR)) {
    mkdir($OUTPUT_DIR, 0755, true);
}

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Print colored output to console
 */
function colorPrint(string $message, string $color = 'white', bool $newline = true): void {
    $colors = [
        'red' => "\033[0;31m",
        'green' => "\033[0;32m",
        'yellow' => "\033[0;33m",
        'blue' => "\033[0;34m",
        'magenta' => "\033[0;35m",
        'cyan' => "\033[0;36m",
        'white' => "\033[0;37m",
        'reset' => "\033[0m",
    ];

    echo ($colors[$color] ?? $colors['white']) . $message . $colors['reset'];
    if ($newline) echo "\n";
}

/**
 * Print section header
 */
function printHeader(string $text): void {
    colorPrint("\n" . str_repeat('=', 80), 'cyan');
    colorPrint($text, 'cyan');
    colorPrint(str_repeat('=', 80), 'cyan');
}

/**
 * Print test step
 */
function printStep(string $text): void {
    colorPrint("\n▶ " . $text, 'blue');
}

/**
 * Print success message
 */
function printSuccess(string $text): void {
    colorPrint("  ✓ " . $text, 'green');
}

/**
 * Print error message
 */
function printError(string $text): void {
    colorPrint("  ✗ " . $text, 'red');
}

/**
 * Print warning message
 */
function printWarning(string $text): void {
    colorPrint("  ⚠ " . $text, 'yellow');
}

/**
 * Print info message
 */
function printInfo(string $text): void {
    colorPrint("  ℹ " . $text, 'white');
}

/**
 * Save data to JSON file
 */
function saveToFile(string $filename, $data): string {
    global $OUTPUT_DIR;
    $filepath = $OUTPUT_DIR . '/' . $filename;
    file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    return $filepath;
}

/**
 * Format duration in human-readable format
 */
function formatDuration(float $seconds): string {
    if ($seconds < 1) {
        return round($seconds * 1000) . 'ms';
    }
    return round($seconds, 2) . 's';
}

/**
 * Validate array structure
 */
function validateStructure(array $data, array $requiredKeys): array {
    $missing = [];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $data)) {
            $missing[] = $key;
        }
    }
    return $missing;
}

/**
 * Timer class for measuring performance
 */
class Timer {
    private float $start;

    public function __construct() {
        $this->start = microtime(true);
    }

    public function elapsed(): float {
        return microtime(true) - $this->start;
    }

    public function reset(): void {
        $this->start = microtime(true);
    }
}

// ============================================================================
// Test Runner
// ============================================================================

class TestRunner {
    private AIEngineService $service;
    private array $results = [];
    private bool $verbose;

    public function __construct(bool $verbose = false) {
        $this->service = new AIEngineService();
        $this->verbose = $verbose;
    }

    /**
     * Run all tests
     */
    public function runAll(): void {
        printHeader("CollabFlow AI Engine Service Tests");

        $overallTimer = new Timer();

        // Test 1: Health Check
        $this->testHealthCheck();

        // Test 2: Context Analysis
        $analysis = $this->testContextAnalysis();

        // Test 3: Task Generation
        if ($analysis) {
            $this->testTaskGeneration($analysis);
        } else {
            printWarning("Skipping task generation due to failed context analysis");
        }

        // Summary
        $this->printSummary($overallTimer->elapsed());
    }

    /**
     * Test 1: Health Check
     */
    private function testHealthCheck(): void {
        printHeader("Test 1: Health Check");

        $timer = new Timer();

        try {
            printStep("Checking Python service availability...");

            $healthy = $this->service->healthCheck();

            $duration = $timer->elapsed();

            if ($healthy) {
                printSuccess("Service is healthy (took " . formatDuration($duration) . ")");
                $this->results['health_check'] = [
                    'status' => 'passed',
                    'duration' => $duration,
                ];
            } else {
                printError("Service is not healthy");
                printInfo("URL: " . config('services.python.url', 'http://localhost:8001'));
                printInfo("Please ensure the Python service is running");

                $this->results['health_check'] = [
                    'status' => 'failed',
                    'duration' => $duration,
                    'error' => 'Service not healthy',
                ];

                // Exit if service is not healthy
                exit(1);
            }

        } catch (\Exception $e) {
            printError("Exception occurred: " . $e->getMessage());

            if ($this->verbose) {
                printInfo("Stack trace:");
                printInfo($e->getTraceAsString());
            }

            $this->results['health_check'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];

            exit(1);
        }
    }

    /**
     * Test 2: Context Analysis
     */
    private function testContextAnalysis(): ?array {
        printHeader("Test 2: Context Analysis");

        $timer = new Timer();

        // Prepare test data
        $projectData = [
            'details' => [
                'name' => 'Test E-commerce Platform',
                'description' => 'Building a modern e-commerce platform with product catalog, shopping cart, and checkout functionality. The system should support multiple payment gateways, inventory management, and provide analytics for business insights.',
                'domain' => 'software_development',
                'timeline' => '3 months',
                'team_size' => 3,
            ],
            'goals' => [
                'goals' => [
                    'Develop user-friendly product browsing experience with advanced search and filtering',
                    'Implement secure payment processing with multiple payment providers',
                    'Create admin dashboard for inventory management and order tracking',
                    'Build responsive mobile-first design',
                    'Integrate with existing CRM system',
                ],
                'success_metrics' => 'Page load time under 2 seconds, 99.9% uptime, conversion rate > 3%',
                'constraints' => 'Budget: $50,000, Must support mobile devices, GDPR compliant',
            ]
        ];

        if ($this->verbose) {
            printInfo("Project details:");
            printInfo("  Name: " . $projectData['details']['name']);
            printInfo("  Timeline: " . $projectData['details']['timeline']);
            printInfo("  Team size: " . $projectData['details']['team_size']);
            printInfo("  Goals: " . count($projectData['goals']['goals']));
        }

        try {
            printStep("Sending context analysis request...");

            $analysis = $this->service->analyzeContext($projectData);

            $duration = $timer->elapsed();

            if ($analysis) {
                printSuccess("Analysis completed (took " . formatDuration($duration) . ")");

                // Validate response structure
                $requiredKeys = ['domain', 'complexity', 'estimated_task_count'];
                $missing = validateStructure($analysis, $requiredKeys);

                if (!empty($missing)) {
                    printWarning("Response missing keys: " . implode(', ', $missing));
                }

                // Display results
                printInfo("Results:");
                printInfo("  Domain: " . ($analysis['domain'] ?? 'unknown'));
                printInfo("  Complexity: " . ($analysis['complexity'] ?? 'unknown'));
                printInfo("  Estimated tasks: " . ($analysis['estimated_task_count'] ?? 'unknown'));
                printInfo("  Confidence: " . ($analysis['confidence_score'] ?? 'unknown'));

                if (isset($analysis['key_objectives'])) {
                    printInfo("  Key objectives: " . count($analysis['key_objectives']));
                    if ($this->verbose && !empty($analysis['key_objectives'])) {
                        foreach (array_slice($analysis['key_objectives'], 0, 3) as $i => $objective) {
                            printInfo("    " . ($i + 1) . ". " . $objective);
                        }
                    }
                }

                if (isset($analysis['recommendations'])) {
                    printInfo("  Recommendations: " . count($analysis['recommendations']));
                    if ($this->verbose && !empty($analysis['recommendations'])) {
                        foreach (array_slice($analysis['recommendations'], 0, 3) as $i => $rec) {
                            printInfo("    " . ($i + 1) . ". " . $rec);
                        }
                    }
                }

                if (isset($analysis['challenges'])) {
                    printInfo("  Identified challenges: " . count($analysis['challenges']));
                }

                // Save response
                global $SAVE_RESPONSES;
                if ($SAVE_RESPONSES) {
                    $filepath = saveToFile('context_analysis_response.json', $analysis);
                    printInfo("Response saved to: " . $filepath);
                }

                $this->results['context_analysis'] = [
                    'status' => 'passed',
                    'duration' => $duration,
                    'data' => $analysis,
                ];

                return $analysis;

            } else {
                printError("Analysis returned null");
                printInfo("This could indicate:");
                printInfo("  - Python service is disabled");
                printInfo("  - API request failed");
                printInfo("  - Invalid response format");

                $this->results['context_analysis'] = [
                    'status' => 'failed',
                    'duration' => $duration,
                    'error' => 'Analysis returned null',
                ];

                return null;
            }

        } catch (\Exception $e) {
            printError("Exception occurred: " . $e->getMessage());

            if ($this->verbose) {
                printInfo("Stack trace:");
                printInfo($e->getTraceAsString());
            }

            $this->results['context_analysis'] = [
                'status' => 'error',
                'duration' => $timer->elapsed(),
                'error' => $e->getMessage(),
            ];

            return null;
        }
    }

    /**
     * Test 3: Task Generation
     */
    private function testTaskGeneration(array $analysis): void {
        printHeader("Test 3: Task Generation");

        $timer = new Timer();

        // Prepare test data
        $context = [
            'name' => 'Test E-commerce Platform',
            'description' => 'Building a modern e-commerce platform with product catalog, shopping cart, and checkout functionality',
            'domain' => 'software_development',
            'goals' => [
                'Develop user-friendly product browsing experience',
                'Implement secure payment processing',
                'Create admin dashboard for inventory management',
            ],
        ];

        $projectId = 'test-project-' . uniqid();
        $userId = 'test-user-' . uniqid();

        if ($this->verbose) {
            printInfo("Request details:");
            printInfo("  Project ID: " . $projectId);
            printInfo("  User ID: " . $userId);
            printInfo("  Using analysis from previous step");
        }

        try {
            printStep("Sending task generation request...");
            printInfo("This may take 30-60 seconds...");

            $result = $this->service->generateTasks(
                $projectId,
                $userId,
                $context,
                $analysis
            );

            $duration = $timer->elapsed();

            if ($result && isset($result['tasks'])) {
                printSuccess("Task generation completed (took " . formatDuration($duration) . ")");

                // Validate response structure
                $requiredKeys = ['tasks', 'dependencies', 'metadata'];
                $missing = validateStructure($result, $requiredKeys);

                if (!empty($missing)) {
                    printWarning("Response missing keys: " . implode(', ', $missing));
                }

                // Display summary
                $taskCount = count($result['tasks']);
                printInfo("Generated {$taskCount} tasks");

                if (isset($result['metadata'])) {
                    $meta = $result['metadata'];
                    printInfo("Task breakdown:");
                    printInfo("  AI tasks: " . ($meta['ai_tasks'] ?? 0));
                    printInfo("  Human tasks: " . ($meta['human_tasks'] ?? 0));
                    printInfo("  HITL tasks: " . ($meta['hitl_tasks'] ?? 0));

                    if (isset($meta['processing_time'])) {
                        printInfo("  Processing time: " . formatDuration($meta['processing_time']));
                    }
                }

                if (isset($result['dependencies'])) {
                    printInfo("Dependencies: " . count($result['dependencies']));
                }

                // Display sample tasks
                printStep("Sample tasks:");
                foreach (array_slice($result['tasks'], 0, 5) as $i => $task) {
                    $type = strtoupper($task['type'] ?? 'unknown');
                    $name = $task['name'] ?? 'Unnamed task';
                    $hours = $task['estimated_hours'] ?? '?';
                    $complexity = $task['complexity'] ?? 'N/A';

                    printInfo(($i + 1) . ". [{$type}] {$name}");
                    printInfo("   Hours: {$hours}, Complexity: {$complexity}");

                    if ($this->verbose && isset($task['description'])) {
                        $desc = substr($task['description'], 0, 100);
                        printInfo("   Description: {$desc}...");
                    }
                }

                if ($taskCount > 5) {
                    printInfo("... and " . ($taskCount - 5) . " more tasks");
                }

                // Validate task structure
                printStep("Validating task data...");
                $validTasks = 0;
                $invalidTasks = [];

                foreach ($result['tasks'] as $i => $task) {
                    $taskRequired = ['id', 'name', 'type', 'estimated_hours'];
                    $taskMissing = validateStructure($task, $taskRequired);

                    if (empty($taskMissing)) {
                        $validTasks++;
                    } else {
                        $invalidTasks[] = "Task #{$i}: missing " . implode(', ', $taskMissing);
                    }
                }

                if ($validTasks === $taskCount) {
                    printSuccess("All tasks have valid structure");
                } else {
                    printWarning("{$validTasks}/{$taskCount} tasks are valid");
                    if ($this->verbose && !empty($invalidTasks)) {
                        foreach (array_slice($invalidTasks, 0, 3) as $invalid) {
                            printInfo("  " . $invalid);
                        }
                    }
                }

                // Save response
                global $SAVE_RESPONSES;
                if ($SAVE_RESPONSES) {
                    $filepath = saveToFile('task_generation_response.json', $result);
                    printInfo("Response saved to: " . $filepath);
                }

                $this->results['task_generation'] = [
                    'status' => 'passed',
                    'duration' => $duration,
                    'task_count' => $taskCount,
                    'valid_tasks' => $validTasks,
                    'data' => $result,
                ];

            } else {
                printError("Task generation failed or returned invalid data");

                if ($result === null) {
                    printInfo("Result is null - Python service may be disabled");
                } elseif (!isset($result['tasks'])) {
                    printInfo("Result missing 'tasks' key");
                    if ($this->verbose) {
                        printInfo("Actual keys: " . implode(', ', array_keys($result)));
                    }
                }

                $this->results['task_generation'] = [
                    'status' => 'failed',
                    'duration' => $duration,
                    'error' => 'Invalid or null result',
                ];
            }

        } catch (\Exception $e) {
            printError("Exception occurred: " . $e->getMessage());

            if ($this->verbose) {
                printInfo("Stack trace:");
                printInfo($e->getTraceAsString());
            }

            $this->results['task_generation'] = [
                'status' => 'error',
                'duration' => $timer->elapsed(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Print test summary
     */
    private function printSummary(float $totalDuration): void {
        printHeader("Test Summary");

        $passed = 0;
        $failed = 0;
        $errors = 0;

        foreach ($this->results as $test => $result) {
            $status = $result['status'] ?? 'unknown';

            switch ($status) {
                case 'passed':
                    $passed++;
                    printSuccess(ucwords(str_replace('_', ' ', $test)) . " - PASSED");
                    break;
                case 'failed':
                    $failed++;
                    printError(ucwords(str_replace('_', ' ', $test)) . " - FAILED");
                    break;
                case 'error':
                    $errors++;
                    printError(ucwords(str_replace('_', ' ', $test)) . " - ERROR");
                    break;
            }

            if (isset($result['duration'])) {
                printInfo("  Duration: " . formatDuration($result['duration']));
            }
        }

        printInfo("\nTotal tests: " . count($this->results));
        printSuccess("Passed: {$passed}");

        if ($failed > 0) {
            printError("Failed: {$failed}");
        }

        if ($errors > 0) {
            printError("Errors: {$errors}");
        }

        printInfo("Total duration: " . formatDuration($totalDuration));

        // Save full results
        global $SAVE_RESPONSES;
        if ($SAVE_RESPONSES) {
            $filepath = saveToFile('test_results_summary.json', [
                'timestamp' => date('Y-m-d H:i:s'),
                'results' => $this->results,
                'summary' => [
                    'total' => count($this->results),
                    'passed' => $passed,
                    'failed' => $failed,
                    'errors' => $errors,
                    'duration' => $totalDuration,
                ],
            ]);
            printInfo("\nFull results saved to: " . $filepath);
        }

        // Exit code
        $exitCode = ($failed + $errors) > 0 ? 1 : 0;

        if ($exitCode === 0) {
            printSuccess("\n✓ All tests passed!");
        } else {
            printError("\n✗ Some tests failed. Check the logs above for details.");
        }

        exit($exitCode);
    }
}

// ============================================================================
// Run Tests
// ============================================================================

try {
    $runner = new TestRunner($VERBOSE);
    $runner->runAll();
} catch (\Exception $e) {
    colorPrint("\n\nFATAL ERROR: " . $e->getMessage(), 'red');
    if ($VERBOSE) {
        colorPrint("\nStack trace:", 'red');
        colorPrint($e->getTraceAsString(), 'red');
    }
    exit(1);
}
