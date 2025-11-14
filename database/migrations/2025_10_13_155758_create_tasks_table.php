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
        Schema::create('tasks', function (Blueprint $table) {
            // Primary key - defined first
            $table->uuid('id')->primary();

            // Foreign keys to other tables
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');

            // Self-referencing foreign key - will be added AFTER table creation
            $table->uuid('parent_task_id')->nullable();

            // Task data
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['ai', 'human', 'hitl'])->default('human'); // Task type for CollabFlow
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->json('required_skills')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('deliverables')->nullable();
            $table->enum('status', [
                'generated',
                'pending',
                'in_progress',
                'review',
                'completed',
                'cancelled'
            ])->default('generated');
            $table->date('due_date')->nullable(); // For schedule features
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Assignee
            $table->json('metadata')->nullable();

            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes (but NOT the self-referencing foreign key yet)
            $table->index('project_id');
            $table->index('parent_task_id');
            $table->index('status');
            $table->index('created_at');
        });

        // NOW add the self-referencing foreign key constraint
        // This is done AFTER the table exists with its primary key
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('parent_task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
