<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DemoProjectSeeder;

class SeedDemoProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collabflow:seed-demo
                            {--fresh : Delete existing demo project first}
                            {--force : Force seeding without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed demo project for testing workflows';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Skip in production unless forced
        if (app()->environment('production') && !$this->option('force')) {
            $this->warn('⚠️  Demo seeding is disabled in production.');
            $this->info('Use --force flag to seed anyway (not recommended).');
            return Command::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('🗑️  Deleting existing demo project...');
            \App\Models\Project::where('name', 'Demo: Multi-Task Workflow Test')->delete();
            $this->info('✓ Existing demo project deleted');
        }

        $this->info('🌱 Seeding demo project for workflow testing...');

        try {
            $this->call('db:seed', [
                '--class' => 'DemoProjectSeeder',
                '--force' => true // Skip Laravel's confirmation prompt
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Seeding failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
