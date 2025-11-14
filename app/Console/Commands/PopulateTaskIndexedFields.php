<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class PopulateTaskIndexedFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:populate-indexed-fields
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate indexed AI fields from task metadata for existing tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Get tasks with null indexed fields but have metadata
        $tasks = Task::whereNotNull('metadata')
            ->where(function ($query) {
                $query->whereNull('complexity')
                    ->orWhereNull('sequence')
                    ->orWhereNull('ai_suitability_score')
                    ->orWhereNull('confidence_score')
                    ->orWhereNull('validation_score');
            })
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No tasks found that need updating.');
            return Command::SUCCESS;
        }

        $this->info("Found {$tasks->count()} tasks to update.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        if (!$force && !$dryRun) {
            if (!$this->confirm('Do you want to proceed with updating these tasks?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->withProgressBar($tasks, function ($task) use ($dryRun) {
            $this->updateTask($task, $dryRun);
        });

        $this->newLine(2);
        $this->info('Task update completed!');

        return Command::SUCCESS;
    }

    private function updateTask(Task $task, bool $dryRun): void
    {
        $metadata = $task->metadata ?? [];
        $updated = false;

        // Extract complexity
        if (is_null($task->complexity) && isset($metadata['complexity'])) {
            $task->complexity = strtoupper($metadata['complexity']);
            $updated = true;
        }

        // Extract sequence
        if (is_null($task->sequence) && isset($metadata['sequence'])) {
            $task->sequence = (int) $metadata['sequence'];
            $updated = true;
        }

        // Extract AI suitability score
        if (is_null($task->ai_suitability_score) && isset($metadata['ai_suitability_score'])) {
            $task->ai_suitability_score = (float) $metadata['ai_suitability_score'];
            $updated = true;
        }

        // Extract confidence score
        if (is_null($task->confidence_score) && isset($metadata['confidence_score'])) {
            $task->confidence_score = (float) $metadata['confidence_score'];
            $updated = true;
        }

        // Extract validation score
        if (is_null($task->validation_score) && isset($metadata['validation']['score'])) {
            $task->validation_score = (int) $metadata['validation']['score'];
            $updated = true;
        }

        if ($updated && !$dryRun) {
            $task->save();
        }
    }
}
