<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Orchestration status tracking
            if (!Schema::hasColumn('projects', 'orchestration_status')) {
                $table->string('orchestration_status', 50)
                    ->default('not_started')
                    ->after('status')
                    ->comment('Orchestration workflow status: not_started, running, completed, failed');
            }

            if (!Schema::hasColumn('projects', 'orchestration_started_at')) {
                $table->timestamp('orchestration_started_at')
                    ->nullable()
                    ->after('orchestration_status')
                    ->comment('When orchestration workflow was started');
            }

            if (!Schema::hasColumn('projects', 'orchestration_completed_at')) {
                $table->timestamp('orchestration_completed_at')
                    ->nullable()
                    ->after('orchestration_started_at')
                    ->comment('When orchestration workflow completed');
            }

            // n8n execution tracking
            if (!Schema::hasColumn('projects', 'last_n8n_execution_id')) {
                $table->string('last_n8n_execution_id')
                    ->nullable()
                    ->after('orchestration_completed_at')
                    ->comment('Most recent n8n execution ID');
            }

            if (!Schema::hasColumn('projects', 'total_orchestration_runs')) {
                $table->integer('total_orchestration_runs')
                    ->default(0)
                    ->after('last_n8n_execution_id')
                    ->comment('How many times n8n workflow was triggered');
            }

            if (!Schema::hasColumn('projects', 'orchestration_metadata')) {
                $table->json('orchestration_metadata')
                    ->nullable()
                    ->after('total_orchestration_runs')
                    ->comment('Additional orchestration tracking data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'orchestration_status',
                'orchestration_started_at',
                'orchestration_completed_at',
                'last_n8n_execution_id',
                'total_orchestration_runs',
                'orchestration_metadata'
            ]);
        });
    }
};
