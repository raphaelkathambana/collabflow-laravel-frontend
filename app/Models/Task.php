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
    ];

    protected $casts = [
        'required_skills' => 'array',
        'dependencies' => 'array',
        'deliverables' => 'array',
        'metadata' => 'array',
        'estimated_hours' => 'decimal:2',
        'ai_suitability_score' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'validation_score' => 'integer',
        'sequence' => 'integer',
        'due_date' => 'date',
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
}
