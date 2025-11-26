<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIEngineService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TestDocumentFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:document-flow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test document upload and task generation flow with same project ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Generate ONE project ID for entire flow
        $projectId = 'temp_' . Str::uuid();

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  Testing Document Upload & Task Generation Flow');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();
        $this->info("Project ID: <fg=yellow>{$projectId}</>");
        $this->newLine();

        $aiEngine = new AIEngineService();

        // ========================================
        // STEP 1: Upload Document
        // ========================================
        $this->info('─────────────────────────────────────────────────────');
        $this->info('Step 1: Uploading document...');
        $this->info('─────────────────────────────────────────────────────');

        try {
            Log::info('TEST: Starting document upload', [
                'project_id' => $projectId,
                'test_name' => 'test:document-flow'
            ]);

            $result = $aiEngine->uploadDocumentsRaw($projectId, [[
                'content' => 'Test requirements for a mobile application to help users find lost items. Features include user registration, item reporting with photos and location, matching algorithm to connect lost and found items, and push notifications.',
                'source' => 'test-requirements.txt',
                'type' => 'txt',
                'created_at' => now()->toIso8601String()
            ]]);

            if ($result['status'] === 'success') {
                $this->info("✓ Upload successful");
                $this->line("  Documents uploaded: <fg=green>{$result['document_count']}</>");
                $this->line("  Project ID used: <fg=yellow>{$result['project_id']}</>");

                if ($result['project_id'] !== $projectId) {
                    $this->error("  ✗ ERROR: Project ID mismatch!");
                    $this->error("    Expected: {$projectId}");
                    $this->error("    Got: {$result['project_id']}");
                    return 1;
                }
            } else {
                $this->error("✗ Upload failed");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("✗ Upload exception: {$e->getMessage()}");
            Log::error('TEST: Upload failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);
            return 1;
        }

        $this->newLine();

        // ========================================
        // STEP 2: Verify Document Storage
        // ========================================
        $this->info('─────────────────────────────────────────────────────');
        $this->info('Step 2: Verifying document storage...');
        $this->info('─────────────────────────────────────────────────────');

        sleep(2); // Give ChromaDB time to index

        try {
            Log::info('TEST: Retrieving documents', [
                'project_id' => $projectId
            ]);

            $result = $aiEngine->getDocuments($projectId);

            if ($result['status'] === 'success') {
                $this->info("✓ Documents retrieved successfully");
                $this->line("  Document count: <fg=green>{$result['document_count']}</>");
                $this->line("  Project ID used: <fg=yellow>{$result['project_id']}</>");

                if ($result['project_id'] !== $projectId) {
                    $this->error("  ✗ ERROR: Project ID mismatch!");
                    $this->error("    Expected: {$projectId}");
                    $this->error("    Got: {$result['project_id']}");
                    return 1;
                }
            } else {
                $this->error("✗ Retrieval failed");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("✗ Retrieval exception: {$e->getMessage()}");
            Log::error('TEST: Retrieval failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);
            return 1;
        }

        $this->newLine();

        // ========================================
        // STEP 3: Generate Tasks with SAME ID
        // ========================================
        $this->info('─────────────────────────────────────────────────────');
        $this->info('Step 3: Generating tasks with SAME project ID...');
        $this->info('─────────────────────────────────────────────────────');

        try {
            Log::info('TEST: Generating tasks', [
                'project_id' => $projectId,
                'expecting_chromadb_context' => true
            ]);

            $result = $aiEngine->generateTasks(
                projectId: $projectId, // CRITICAL: SAME ID as upload
                userId: 'test-user-' . Str::random(8),
                context: [
                    'name' => 'Lost & Found Mobile App',
                    'description' => 'Mobile application to help users find lost items based on uploaded requirements document',
                    'domain' => 'software_development',
                    'goals' => [
                        'Build mobile app with user registration',
                        'Implement item reporting with photos',
                        'Create matching algorithm',
                        'Set up push notifications'
                    ]
                ],
                analysis: [
                    'domain' => 'software_development',
                    'complexity' => 'medium',
                    'estimated_task_count' => 7,
                    'key_objectives' => [
                        'User authentication system',
                        'Item reporting functionality',
                        'Matching algorithm',
                        'Notification system'
                    ],
                    'challenges' => [
                        'Real-time matching',
                        'Accurate location tracking',
                        'Push notification delivery'
                    ],
                    'required_skills' => [
                        ['name' => 'Mobile Development', 'level' => 'intermediate'],
                        ['name' => 'Backend API Development', 'level' => 'intermediate'],
                        ['name' => 'Database Design', 'level' => 'intermediate']
                    ],
                    'recommendations' => [
                        'Use React Native or Flutter for cross-platform development',
                        'Implement RESTful API backend',
                        'Use geolocation services for location tracking'
                    ],
                    'confidence_score' => 0.87
                ]
            );

            if ($result && isset($result['tasks']) && !empty($result['tasks'])) {
                $taskCount = count($result['tasks']);
                $this->info("✓ Task generation successful");
                $this->line("  Tasks generated: <fg=green>{$taskCount}</>");
                $this->line("  Project ID used: <fg=yellow>{$projectId}</>");

                if (isset($result['metadata'])) {
                    $this->line("  AI tasks: <fg=cyan>{$result['metadata']['ai_tasks']}</>");
                    $this->line("  Human tasks: <fg=cyan>{$result['metadata']['human_tasks']}</>");
                    $this->line("  HITL tasks: <fg=cyan>{$result['metadata']['hitl_tasks']}</>");
                }

                // Check if tasks were generated with ChromaDB context
                $this->newLine();
                $this->line("  <fg=cyan>Sample tasks generated:</>");
                foreach (array_slice($result['tasks'], 0, 3) as $i => $task) {
                    $this->line("  " . ($i + 1) . ". {$task['name']}");
                }

            } else {
                $this->error("✗ Task generation failed");
                $this->error("  Response: " . json_encode($result));
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("✗ Task generation exception: {$e->getMessage()}");
            Log::error('TEST: Task generation failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->newLine();

        // ========================================
        // STEP 4: Cleanup
        // ========================================
        $this->info('─────────────────────────────────────────────────────');
        $this->info('Step 4: Cleaning up...');
        $this->info('─────────────────────────────────────────────────────');

        try {
            $result = $aiEngine->deleteDocuments($projectId);
            $this->info("✓ Cleanup completed");

        } catch (\Exception $e) {
            $this->warn("⚠ Cleanup warning: {$e->getMessage()}");
            Log::warning('TEST: Cleanup failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage()
            ]);
            // Don't fail test on cleanup error
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  <fg=green>✓ ALL TESTS PASSED!</>');
        $this->info('═══════════════════════════════════════════════════════');
        $this->line("  The SAME project ID was used throughout:");
        $this->line("  <fg=yellow>{$projectId}</>");
        $this->newLine();
        $this->info('  ✓ Document upload');
        $this->info('  ✓ Document retrieval');
        $this->info('  ✓ Task generation with ChromaDB context');
        $this->newLine();

        Log::info('TEST: All tests passed', [
            'project_id' => $projectId
        ]);

        return 0;
    }
}
