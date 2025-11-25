<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds fields for storing AI/human work outputs and review data.
     * These fields enable the complete AI-Human collaboration workflow.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Work output storage
            $table->json('output')->nullable()->after('metadata')
                ->comment('JSON storage for AI/human work results');

            // Execution timestamps
            $table->timestamp('started_at')->nullable()->after('output')
                ->index('idx_tasks_started_at')
                ->comment('When work on this task began');

            $table->timestamp('completed_at')->nullable()->after('started_at')
                ->index('idx_tasks_completed_at')
                ->comment('When work on this task was completed');

            // HITL Review fields
            $table->text('review_notes')->nullable()->after('completed_at')
                ->comment('Human feedback and review comments for HITL tasks');

            $table->foreignId('reviewed_by')->nullable()->after('review_notes')
                ->constrained('users')->nullOnDelete()
                ->comment('User who reviewed this task (for HITL)');

            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by')
                ->comment('When the task was reviewed by human');

            // Composite indexes for common queries
            $table->index(['status', 'completed_at'], 'idx_tasks_status_completed');
            $table->index(['type', 'status', 'started_at'], 'idx_tasks_type_status_started');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_tasks_status_completed');
            $table->dropIndex('idx_tasks_type_status_started');
            $table->dropIndex('idx_tasks_started_at');
            $table->dropIndex('idx_tasks_completed_at');

            // Drop foreign key
            $table->dropForeign(['reviewed_by']);

            // Drop columns
            $table->dropColumn([
                'output',
                'started_at',
                'completed_at',
                'review_notes',
                'reviewed_by',
                'reviewed_at'
            ]);
        });
    }
};
