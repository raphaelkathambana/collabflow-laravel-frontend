<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProjectSeeder extends Seeder
{
    /**
     * Seed a demo project with tasks for testing n8n workflows
     */
    public function run(): void
    {
        // Get the first user or create a demo user
        $user = User::first() ?? User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@collabflow.test',
        ]);

        // Delete existing demo project if it exists
        Project::where('name', 'Demo: Multi-Task Workflow Test')->delete();

        // Create demo project
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Demo: Multi-Task Workflow Test',
            'description' => 'A demo project to test the n8n orchestration workflow with multiple task types',
            'goals' => [
                'Test AI task generation',
                'Validate orchestration callback integration',
                'Verify task status updates',
            ],
            'kpis' => [
                'All tasks successfully generated',
                'Orchestration completes without errors',
                'Status updates reflected in Laravel',
            ],
            'success_criteria' => 'Project completes with all tasks generated and orchestration callback received',
            'domain' => 'Testing & QA',
            'timeline' => '1 day',
            'team_size' => 1,
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'reference_documents' => [],
            'success_metrics' => ['100% task generation success', 'Zero errors in orchestration'],
            'constraints' => 'Demo environment only',
            'complexity_score' => 2.5,
            'ai_analysis' => [
                'confidence' => 0.95,
                'recommended_approach' => 'Automated workflow testing',
                'risk_level' => 'low',
            ],
            'status' => 'draft',
            'progress' => 0,
            'workflow_state' => [
                'current_step' => 'initialization',
                'steps_completed' => [],
            ],
            'workflow_metadata' => [
                'created_for' => 'n8n_testing',
                'test_type' => 'orchestration_callback',
            ],
        ]);

        // Create demo tasks with different types
        $tasks = [
            [
                'name' => 'Setup Development Environment',
                'description' => 'Configure local development environment with required dependencies',
                'type' => 'human',
                'complexity' => 'M',
                'sequence' => 1,
                'ai_suitability_score' => 0.3,
                'confidence_score' => 0.85,
                'validation_score' => 8,
                'estimated_hours' => 2.0,
                'required_skills' => ['DevOps', 'System Configuration'],
                'dependencies' => [],
                'deliverables' => ['Configured development environment', 'Dependencies installed'],
                'status' => 'pending',
                'metadata' => [
                    'priority' => 'high',
                    'subtasks' => [
                        ['name' => 'Install dependencies', 'is_checkpoint' => true],
                        ['name' => 'Configure environment variables', 'is_checkpoint' => false],
                        ['name' => 'Verify setup', 'is_checkpoint' => true],
                    ],
                ],
            ],
            [
                'name' => 'Generate API Documentation',
                'description' => 'Automatically generate API documentation from code annotations',
                'type' => 'ai',
                'complexity' => 'L',
                'sequence' => 2,
                'ai_suitability_score' => 0.95,
                'confidence_score' => 0.92,
                'validation_score' => 9,
                'estimated_hours' => 1.0,
                'required_skills' => ['Documentation', 'API Design'],
                'dependencies' => [],
                'deliverables' => ['API documentation in Markdown', 'OpenAPI specification'],
                'status' => 'pending',
                'metadata' => [
                    'priority' => 'medium',
                    'automation_type' => 'documentation_generation',
                ],
            ],
            [
                'name' => 'Code Review & Quality Check',
                'description' => 'Review code changes with AI assistance and human validation',
                'type' => 'hitl',
                'complexity' => 'H',
                'sequence' => 3,
                'ai_suitability_score' => 0.75,
                'confidence_score' => 0.88,
                'validation_score' => 7,
                'estimated_hours' => 3.0,
                'required_skills' => ['Code Review', 'Quality Assurance', 'AI Collaboration'],
                'dependencies' => [],
                'deliverables' => ['Code review report', 'Quality metrics', 'Approved changes'],
                'status' => 'pending',
                'metadata' => [
                    'priority' => 'high',
                    'requires_human_validation' => true,
                    'ai_assists_with' => ['Pattern detection', 'Best practice suggestions', 'Security scan'],
                    'subtasks' => [
                        ['name' => 'AI security scan', 'is_checkpoint' => true],
                        ['name' => 'Human code review', 'is_checkpoint' => true],
                        ['name' => 'Final approval', 'is_checkpoint' => true],
                    ],
                ],
            ],
            [
                'name' => 'Database Migration Script',
                'description' => 'Create database migration scripts for new features',
                'type' => 'ai',
                'complexity' => 'M',
                'sequence' => 4,
                'ai_suitability_score' => 0.88,
                'confidence_score' => 0.90,
                'validation_score' => 8,
                'estimated_hours' => 1.5,
                'required_skills' => ['Database Design', 'SQL', 'Laravel Migrations'],
                'dependencies' => [],
                'deliverables' => ['Migration files', 'Rollback scripts'],
                'status' => 'pending',
                'metadata' => [
                    'priority' => 'medium',
                    'automation_type' => 'code_generation',
                ],
            ],
            [
                'name' => 'User Acceptance Testing',
                'description' => 'Conduct final user acceptance testing with stakeholders',
                'type' => 'human',
                'complexity' => 'H',
                'sequence' => 5,
                'ai_suitability_score' => 0.2,
                'confidence_score' => 0.80,
                'validation_score' => 9,
                'estimated_hours' => 4.0,
                'required_skills' => ['Testing', 'User Experience', 'Stakeholder Communication'],
                'dependencies' => [],
                'deliverables' => ['UAT report', 'Sign-off document', 'Issue list'],
                'status' => 'pending',
                'metadata' => [
                    'priority' => 'critical',
                    'requires_stakeholder' => true,
                    'subtasks' => [
                        ['name' => 'Prepare test scenarios', 'is_checkpoint' => false],
                        ['name' => 'Conduct testing session', 'is_checkpoint' => true],
                        ['name' => 'Collect feedback', 'is_checkpoint' => true],
                        ['name' => 'Get sign-off', 'is_checkpoint' => true],
                    ],
                ],
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::create(array_merge($taskData, ['project_id' => $project->id]));
        }

        $this->command->info("✓ Demo project created: {$project->name}");
        $this->command->info("  - Project ID: {$project->id}");
        $this->command->info("  - Total Tasks: " . count($tasks));
        $this->command->info("  - AI Tasks: " . collect($tasks)->where('type', 'ai')->count());
        $this->command->info("  - Human Tasks: " . collect($tasks)->where('type', 'human')->count());
        $this->command->info("  - HITL Tasks: " . collect($tasks)->where('type', 'hitl')->count());
    }
}
