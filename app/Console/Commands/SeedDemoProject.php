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
    protected $signature = 'collabflow:seed-demo {--fresh : Delete existing demo project first}';

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
        if ($this->option('fresh')) {
            $this->warn('Deleting existing demo project...');
            \App\Models\Project::where('name', 'Demo: Multi-Task Workflow Test')->delete();
            $this->info('✓ Existing demo project deleted');
        }

        $this->info('Seeding demo project...');
        $this->call('db:seed', ['--class' => 'DemoProjectSeeder', '--force' => true]);

        return Command::SUCCESS;
    }
}
