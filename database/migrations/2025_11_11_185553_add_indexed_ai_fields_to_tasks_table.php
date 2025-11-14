<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds indexed columns for AI-specific task fields that are
     * currently stored only in the metadata JSON column. Indexing these fields
     * improves query performance for:
     * - Filtering tasks by complexity
     * - Sorting tasks by sequence
     * - Finding tasks by AI suitability score
     * - Searching by confidence score
     * - Performance analytics queries
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add complexity as indexed column (currently in metadata)
            // Values: LOW, MEDIUM, HIGH, VERY_HIGH
            $table->string('complexity', 20)->nullable()->after('type')
                ->index('idx_tasks_complexity')
                ->comment('Task complexity level from AI analysis');

            // Add sequence as indexed integer (currently in metadata)
            // Used for task ordering in workflow
            $table->unsignedInteger('sequence')->nullable()->after('complexity')
                ->index('idx_tasks_sequence')
                ->comment('Task sequence/order in workflow');

            // Add AI suitability score (0.0 to 1.0)
            // Higher score = more suitable for AI automation
            $table->decimal('ai_suitability_score', 3, 2)->nullable()->after('sequence')
                ->index('idx_tasks_ai_suitability')
                ->comment('AI suitability score (0.00-1.00)');

            // Add confidence score (0.0 to 1.0)
            // AI's confidence in task definition and estimates
            $table->decimal('confidence_score', 3, 2)->nullable()->after('ai_suitability_score')
                ->index('idx_tasks_confidence')
                ->comment('AI confidence score (0.00-1.00)');

            // Add validation score (0-100)
            // Quality score from task validation process
            $table->unsignedTinyInteger('validation_score')->nullable()->after('confidence_score')
                ->index('idx_tasks_validation')
                ->comment('Task validation score (0-100)');

            // Composite indexes for common query patterns
            $table->index(['project_id', 'sequence'], 'idx_tasks_project_sequence');
            $table->index(['type', 'ai_suitability_score'], 'idx_tasks_type_ai_score');
            $table->index(['complexity', 'confidence_score'], 'idx_tasks_complexity_confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop composite indexes first
            $table->dropIndex('idx_tasks_project_sequence');
            $table->dropIndex('idx_tasks_type_ai_score');
            $table->dropIndex('idx_tasks_complexity_confidence');

            // Drop individual indexes (automatically dropped with columns)
            $table->dropColumn([
                'complexity',
                'sequence',
                'ai_suitability_score',
                'confidence_score',
                'validation_score'
            ]);
        });
    }
};
