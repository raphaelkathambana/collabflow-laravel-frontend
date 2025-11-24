<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_task_id',
        'name',
        'description',
        'type',
        'complexity',
        'sequence',
        'ai_suitability_score',
        'confidence_score',
        'validation_score',
        'estimated_hours',
        'required_skills',
        'dependencies',
        'deliverables',
        'status',
        'due_date',
        'assigned_to',
        'metadata',
        'output',
        'started_at',
        'completed_at',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'dependencies' => 'array',
        'deliverables' => 'array',
        'metadata' => 'array',
        'output' => 'array',
        'estimated_hours' => 'decimal:2',
        'ai_suitability_score' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'validation_score' => 'integer',
        'sequence' => 'integer',
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeAiTasks($query)
    {
        return $query->where('type', 'ai');
    }

    public function scopeHumanTasks($query)
    {
        return $query->where('type', 'human');
    }

    public function scopeHitlTasks($query)
    {
        return $query->where('type', 'hitl');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'review']);
    }

    public function scopeByComplexity($query, string $complexity)
    {
        return $query->where('complexity', strtoupper($complexity));
    }

    public function scopeHighlySuitable($query, float $threshold = 0.7)
    {
        return $query->where('ai_suitability_score', '>=', $threshold);
    }

    public function scopeHighConfidence($query, float $threshold = 0.8)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    public function scopeOrderedBySequence($query)
    {
        return $query->orderBy('sequence');
    }

    // Helper methods
    public function getSubtasksFromMetadataAttribute()
    {
        return $this->metadata['subtasks'] ?? [];
    }

    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'ai' => 'glaucous',
            'human' => 'tea-green',
            'hitl' => 'orange-peel',
            default => 'text-600',
        };
    }

    public function getTypeBackgroundAttribute()
    {
        return match($this->type) {
            'ai' => 'accent-100',
            'human' => 'success-100',
            'hitl' => 'secondary-100',
            default => 'background-200',
        };
    }

    /**
     * Check if this task has checkpoint subtasks
     */
    public function hasCheckpoints(): bool
    {
        $subtasks = $this->metadata['subtasks'] ?? [];

        foreach ($subtasks as $subtask) {
            if (isset($subtask['is_checkpoint']) && $subtask['is_checkpoint'] === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get count of checkpoint subtasks
     */
    public function getCheckpointCount(): int
    {
        $subtasks = $this->metadata['subtasks'] ?? [];
        $count = 0;

        foreach ($subtasks as $subtask) {
            if (isset($subtask['is_checkpoint']) && $subtask['is_checkpoint'] === true) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if this is a checkpoint task (for subtasks stored as individual tasks)
     */
    public function isCheckpointTask(): bool
    {
        return isset($this->metadata['is_checkpoint']) && $this->metadata['is_checkpoint'] === true;
    }

    /**
     * Get checkpoint subtasks
     */
    public function getCheckpointSubtasks(): array
    {
        $subtasks = $this->metadata['subtasks'] ?? [];

        return array_filter($subtasks, function($subtask) {
            return isset($subtask['is_checkpoint']) && $subtask['is_checkpoint'] === true;
        });
    }

    // ========================================
    // Work Submission Methods
    // ========================================

    /**
     * Submit work output for this task
     */
    public function submitWork(array $output, array $options = []): self
    {
        $this->output = $output;

        if (isset($options['status'])) {
            $this->status = $options['status'];
        }

        if (isset($options['confidence_score'])) {
            $this->confidence_score = $options['confidence_score'];
        }

        if (isset($options['started_at'])) {
            $this->started_at = $options['started_at'];
        }

        if (isset($options['completed_at'])) {
            $this->completed_at = $options['completed_at'];
        }

        // Auto-set completed_at if status is completed
        if ($this->status === 'completed' && !$this->completed_at) {
            $this->completed_at = now();
        }

        $this->save();

        // Log activity
        $this->logActivity('work_submitted', [
            'output_type' => $output['type'] ?? 'unknown',
            'confidence_score' => $this->confidence_score,
        ]);

        return $this;
    }

    /**
     * Request review for this task (HITL workflow)
     */
    public function requestReview(string $notes = null): self
    {
        $this->status = 'review';
        if ($notes) {
            $this->review_notes = $notes;
        }
        $this->save();

        $this->logActivity('review_requested', [
            'notes' => $notes,
        ]);

        return $this;
    }

    /**
     * Approve task after review
     */
    public function approve(int $reviewerId, string $notes = null): self
    {
        $this->status = 'completed';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->completed_at = now();

        if ($notes) {
            $this->review_notes = $notes;
        }

        $this->save();

        $this->logActivity('approved', [
            'reviewer_id' => $reviewerId,
            'notes' => $notes,
        ]);

        return $this;
    }

    /**
     * Request changes after review
     */
    public function requestChanges(int $reviewerId, string $notes): self
    {
        $this->status = 'in_progress';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->review_notes = $notes;
        $this->save();

        $this->logActivity('changes_requested', [
            'reviewer_id' => $reviewerId,
            'notes' => $notes,
        ]);

        return $this;
    }

    // ========================================
    // Subtask Management Methods
    // ========================================

    /**
     * Get all subtasks
     */
    public function getSubtasks(): array
    {
        return $this->metadata['subtasks'] ?? [];
    }

    /**
     * Add a new subtask
     */
    public function addSubtask(array $subtaskData): self
    {
        $metadata = $this->metadata ?? [];
        $subtasks = $metadata['subtasks'] ?? [];

        // Generate unique ID if not provided
        if (!isset($subtaskData['id'])) {
            $subtaskData['id'] = \Illuminate\Support\Str::uuid()->toString();
        }

        // Set defaults
        $subtask = array_merge([
            'name' => '',
            'description' => '',
            'type' => 'human',
            'status' => 'pending',
            'is_checkpoint' => false,
            'estimated_hours' => null,
            'output' => null,
            'completed_at' => null,
            'completed_by' => null,
        ], $subtaskData);

        $subtasks[] = $subtask;
        $metadata['subtasks'] = $subtasks;
        $this->metadata = $metadata;
        $this->save();

        $this->logActivity('subtask_added', [
            'subtask_id' => $subtask['id'],
            'subtask_name' => $subtask['name'],
        ]);

        return $this;
    }

    /**
     * Update an existing subtask
     */
    public function updateSubtask(string $subtaskId, array $updates): self
    {
        $metadata = $this->metadata ?? [];
        $subtasks = $metadata['subtasks'] ?? [];

        $found = false;
        foreach ($subtasks as $index => $subtask) {
            if ($subtask['id'] === $subtaskId) {
                $subtasks[$index] = array_merge($subtask, $updates);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new \Exception("Subtask not found: {$subtaskId}");
        }

        $metadata['subtasks'] = $subtasks;
        $this->metadata = $metadata;
        $this->save();

        $this->logActivity('subtask_updated', [
            'subtask_id' => $subtaskId,
            'updates' => array_keys($updates),
        ]);

        return $this;
    }

    /**
     * Delete a subtask
     */
    public function deleteSubtask(string $subtaskId): self
    {
        $metadata = $this->metadata ?? [];
        $subtasks = $metadata['subtasks'] ?? [];

        $subtasks = array_filter($subtasks, function($subtask) use ($subtaskId) {
            return $subtask['id'] !== $subtaskId;
        });

        // Re-index array
        $metadata['subtasks'] = array_values($subtasks);
        $this->metadata = $metadata;
        $this->save();

        $this->logActivity('subtask_deleted', [
            'subtask_id' => $subtaskId,
        ]);

        return $this;
    }

    /**
     * Complete a subtask
     */
    public function completeSubtask(string $subtaskId, array $output = null, string $completedBy = null): self
    {
        $updates = [
            'status' => 'completed',
            'completed_at' => now()->toIso8601String(),
            'completed_by' => $completedBy ?? 'unknown',
        ];

        if ($output) {
            $updates['output'] = $output;
        }

        $this->updateSubtask($subtaskId, $updates);

        $this->logActivity('subtask_completed', [
            'subtask_id' => $subtaskId,
            'completed_by' => $completedBy,
        ]);

        return $this;
    }

    /**
     * Get subtask by ID
     */
    public function getSubtask(string $subtaskId): ?array
    {
        $subtasks = $this->getSubtasks();

        foreach ($subtasks as $subtask) {
            if ($subtask['id'] === $subtaskId) {
                return $subtask;
            }
        }

        return null;
    }

    /**
     * Get completed subtasks count
     */
    public function getCompletedSubtasksCount(): int
    {
        $subtasks = $this->getSubtasks();
        return count(array_filter($subtasks, function($subtask) {
            return ($subtask['status'] ?? 'pending') === 'completed';
        }));
    }

    /**
     * Get subtask completion progress (0-100)
     */
    public function getSubtaskProgress(): float
    {
        $subtasks = $this->getSubtasks();
        $total = count($subtasks);

        if ($total === 0) {
            return 0;
        }

        $completed = $this->getCompletedSubtasksCount();
        return round(($completed / $total) * 100, 2);
    }

    // ========================================
    // Activity Logging
    // ========================================

    /**
     * Log an activity for this task
     */
    public function logActivity(string $action, array $details = []): void
    {
        ActivityLog::create([
            'project_id' => $this->project_id,
            'task_id' => $this->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
