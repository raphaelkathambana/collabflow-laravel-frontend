<?php

namespace App\Console\Commands;

use App\Services\AIEngineService;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AIIntegrationHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:check
                            {--detailed : Show detailed statistics}
                            {--json : Output as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Laravel-Python AI integration health and display statistics';

    private AIEngineService $aiService;

    /**
     * Execute the console command.
     */
    public function handle(AIEngineService $aiService)
    {
        $this->aiService = $aiService;
        $detailed = $this->option('detailed');
        $json = $this->option('json');

        $healthData = $this->gatherHealthData();

        if ($json) {
            $this->line(json_encode($healthData, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $this->displayHealthDashboard($healthData, $detailed);

        return Command::SUCCESS;
    }

    private function gatherHealthData(): array
    {
        // 1. Python Service Health
        $pythonHealth = $this->checkPythonService();

        // 2. Database Statistics
        $dbStats = $this->getDatabaseStats();

        // 3. Integration Performance
        $performance = $this->getPerformanceMetrics();

        // 4. Data Quality
        $quality = $this->getDataQuality();

        return [
            'python_service' => $pythonHealth,
            'database' => $dbStats,
            'performance' => $performance,
            'quality' => $quality,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function checkPythonService(): array
    {
        $enabled = config('services.python.enabled');
        $url = config('services.python.url');
        $timeout = config('services.python.timeout');

        if (!$enabled) {
            return [
                'status' => 'disabled',
                'enabled' => false,
                'url' => $url,
                'message' => 'Python service is disabled in configuration'
            ];
        }

        $healthy = false;
        $responseTime = null;
        $error = null;

        try {
            $start = microtime(true);
            $healthy = $this->aiService->healthCheck();
            $responseTime = round((microtime(true) - $start) * 1000, 2);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return [
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'enabled' => true,
            'url' => $url,
            'timeout' => $timeout,
            'response_time_ms' => $responseTime,
            'error' => $error,
        ];
    }

    private function getDatabaseStats(): array
    {
        $totalProjects = Project::count();
        $aiProjects = Project::whereNotNull('ai_analysis')->count();
        $projectsWithMetadata = Project::whereNotNull('workflow_metadata')->count();

        $totalTasks = Task::count();
        $tasksByType = Task::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $tasksWithIndexedFields = Task::whereNotNull('complexity')
            ->orWhereNotNull('sequence')
            ->orWhereNotNull('ai_suitability_score')
            ->count();

        return [
            'projects' => [
                'total' => $totalProjects,
                'with_ai_analysis' => $aiProjects,
                'with_workflow_metadata' => $projectsWithMetadata,
                'ai_usage_rate' => $totalProjects > 0 ? round(($aiProjects / $totalProjects) * 100, 2) : 0,
            ],
            'tasks' => [
                'total' => $totalTasks,
                'by_type' => $tasksByType,
                'with_indexed_fields' => $tasksWithIndexedFields,
                'indexed_rate' => $totalTasks > 0 ? round(($tasksWithIndexedFields / $totalTasks) * 100, 2) : 0,
            ],
        ];
    }

    private function getPerformanceMetrics(): array
    {
        // Get recent projects with AI analysis
        $recentAiProjects = Project::whereNotNull('ai_analysis')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Average task count per AI project
        $avgTasksPerProject = Project::whereNotNull('workflow_metadata')
            ->get()
            ->avg(function ($project) {
                return $project->workflow_metadata['total_tasks'] ?? 0;
            });

        // Average confidence scores
        $avgConfidence = Task::whereNotNull('confidence_score')
            ->avg('confidence_score');

        // Average AI suitability
        $avgAiSuitability = Task::whereNotNull('ai_suitability_score')
            ->avg('ai_suitability_score');

        return [
            'recent_ai_projects_30d' => $recentAiProjects,
            'avg_tasks_per_ai_project' => round($avgTasksPerProject ?? 0, 2),
            'avg_confidence_score' => round($avgConfidence ?? 0, 2),
            'avg_ai_suitability_score' => round($avgAiSuitability ?? 0, 2),
        ];
    }

    private function getDataQuality(): array
    {
        // Tasks missing indexed fields
        $tasksMissingFields = Task::where(function ($query) {
            $query->whereNull('complexity')
                ->orWhereNull('sequence')
                ->orWhereNull('ai_suitability_score');
        })->count();

        // Projects missing workflow metadata
        $projectsMissingMetadata = Project::whereNotNull('ai_analysis')
            ->whereNull('workflow_metadata')
            ->count();

        // Tasks with validation scores
        $tasksWithValidation = Task::whereNotNull('validation_score')->count();
        $avgValidation = Task::whereNotNull('validation_score')->avg('validation_score');

        return [
            'tasks_missing_indexed_fields' => $tasksMissingFields,
            'projects_missing_metadata' => $projectsMissingMetadata,
            'tasks_with_validation' => $tasksWithValidation,
            'avg_validation_score' => round($avgValidation ?? 0, 2),
        ];
    }

    private function displayHealthDashboard(array $data, bool $detailed): void
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('   🤖 AI Integration Health Dashboard');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Python Service Status
        $this->displayPythonStatus($data['python_service']);
        $this->newLine();

        // Database Statistics
        $this->displayDatabaseStats($data['database'], $detailed);
        $this->newLine();

        // Performance Metrics
        if ($detailed) {
            $this->displayPerformanceMetrics($data['performance']);
            $this->newLine();
        }

        // Data Quality
        $this->displayDataQuality($data['quality']);
        $this->newLine();

        // Overall Health Score
        $score = $this->calculateHealthScore($data);
        $this->displayHealthScore($score);
    }

    private function displayPythonStatus(array $status): void
    {
        $this->line('<fg=cyan;options=bold>Python AI Service</>');
        $this->line('─────────────────');

        $statusColor = match ($status['status']) {
            'healthy' => 'green',
            'unhealthy' => 'red',
            'disabled' => 'yellow',
            default => 'gray'
        };

        $this->line("Status: <fg={$statusColor};options=bold>" . strtoupper($status['status']) . '</>' );
        $this->line("URL: {$status['url']}");

        if (isset($status['response_time_ms'])) {
            $timeColor = $status['response_time_ms'] < 1000 ? 'green' : 'yellow';
            $this->line("Response Time: <fg={$timeColor}>{$status['response_time_ms']}ms</>");
        }

        if (isset($status['error'])) {
            $this->error("Error: {$status['error']}");
        }

        if ($status['status'] === 'disabled') {
            $this->warn('⚠ Python service is disabled. Enable in .env: PYTHON_SERVICE_ENABLED=true');
        }
    }

    private function displayDatabaseStats(array $stats, bool $detailed): void
    {
        $this->line('<fg=cyan;options=bold>Database Statistics</>');
        $this->line('───────────────────');

        $this->line("Total Projects: <fg=white;options=bold>{$stats['projects']['total']}</>");
        $this->line("  ├─ With AI Analysis: {$stats['projects']['with_ai_analysis']} ({$stats['projects']['ai_usage_rate']}%)");
        $this->line("  └─ With Workflow Metadata: {$stats['projects']['with_workflow_metadata']}");

        $this->newLine();
        $this->line("Total Tasks: <fg=white;options=bold>{$stats['tasks']['total']}</>");

        foreach ($stats['tasks']['by_type'] as $type => $count) {
            $icon = match ($type) {
                'ai' => '🤖',
                'human' => '👤',
                'hitl' => '🤝',
                default => '📋'
            };
            $this->line("  ├─ $icon " . ucfirst($type) . ": $count");
        }

        $this->line("  └─ With Indexed Fields: {$stats['tasks']['with_indexed_fields']} ({$stats['tasks']['indexed_rate']}%)");
    }

    private function displayPerformanceMetrics(array $metrics): void
    {
        $this->line('<fg=cyan;options=bold>Performance Metrics (Last 30 Days)</>');
        $this->line('──────────────────────────────────');

        $this->line("AI Projects Created: {$metrics['recent_ai_projects_30d']}");
        $this->line("Avg Tasks per Project: {$metrics['avg_tasks_per_ai_project']}");
        $this->line("Avg Confidence Score: " . $this->formatScore($metrics['avg_confidence_score']));
        $this->line("Avg AI Suitability: " . $this->formatScore($metrics['avg_ai_suitability_score']));
    }

    private function displayDataQuality(array $quality): void
    {
        $this->line('<fg=cyan;options=bold>Data Quality</>');
        $this->line('────────────');

        if ($quality['tasks_missing_indexed_fields'] > 0) {
            $this->warn("⚠ {$quality['tasks_missing_indexed_fields']} tasks missing indexed fields");
            $this->line("  Run: php artisan tasks:populate-indexed-fields");
        } else {
            $this->info("✓ All tasks have indexed fields");
        }

        if ($quality['projects_missing_metadata'] > 0) {
            $this->warn("⚠ {$quality['projects_missing_metadata']} AI projects missing workflow metadata");
        } else {
            $this->info("✓ All AI projects have workflow metadata");
        }

        if ($quality['tasks_with_validation'] > 0) {
            $this->line("Tasks with Validation: {$quality['tasks_with_validation']}");
            $this->line("Avg Validation Score: " . $this->formatScore($quality['avg_validation_score']) . "/100");
        }
    }

    private function calculateHealthScore(array $data): int
    {
        $score = 0;

        // Python service health (40 points)
        if ($data['python_service']['status'] === 'healthy') {
            $score += 40;
        } elseif ($data['python_service']['status'] === 'disabled') {
            $score += 20; // Half credit if disabled but intentional
        }

        // Data quality (30 points)
        if ($data['quality']['tasks_missing_indexed_fields'] === 0) {
            $score += 15;
        }
        if ($data['quality']['projects_missing_metadata'] === 0) {
            $score += 15;
        }

        // AI usage (30 points)
        $aiUsageRate = $data['database']['projects']['ai_usage_rate'];
        $score += min(30, ($aiUsageRate / 100) * 30);

        return (int) $score;
    }

    private function displayHealthScore(int $score): void
    {
        $this->line('─────────────────');

        $color = match (true) {
            $score >= 80 => 'green',
            $score >= 60 => 'yellow',
            default => 'red'
        };

        $status = match (true) {
            $score >= 80 => 'EXCELLENT',
            $score >= 60 => 'GOOD',
            $score >= 40 => 'FAIR',
            default => 'NEEDS ATTENTION'
        };

        $this->line("Overall Health: <fg={$color};options=bold>{$score}/100 - {$status}</>");

        if ($score < 80) {
            $this->newLine();
            $this->warn('Recommendations:');
            if ($score < 40) {
                $this->line('  • Ensure Python service is running and healthy');
            }
            $this->line('  • Run: php artisan tasks:populate-indexed-fields');
            $this->line('  • Check integration documentation: tests/README.md');
        }
    }

    private function formatScore(float $score): string
    {
        $color = match (true) {
            $score >= 0.8 => 'green',
            $score >= 0.6 => 'yellow',
            default => 'red'
        };

        return "<fg={$color}>{$score}</>";
    }
}
