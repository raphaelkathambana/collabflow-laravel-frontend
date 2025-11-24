<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'goals',
        'kpis',
        'success_criteria',
        'domain',
        'timeline',
        'team_size',
        'start_date',
        'end_date',
        'reference_documents',
        'success_metrics',
        'constraints',
        'complexity_score',
        'ai_analysis',
        'status',
        'n8n_execution_id',
        'progress',
        'workflow_state',
        'workflow_metadata',
        // Orchestration fields
        'orchestration_status',
        'orchestration_started_at',
        'orchestration_completed_at',
        'last_n8n_execution_id',
        'total_orchestration_runs',
        'orchestration_metadata',
    ];

    protected $casts = [
        'goals' => 'array',
        'kpis' => 'array',
        'reference_documents' => 'array',
        'success_metrics' => 'array',
        'workflow_state' => 'array',
        'workflow_metadata' => 'array',
        'orchestration_metadata' => 'array',
        'ai_analysis' => 'array',
        'complexity_score' => 'decimal:2',
        'progress' => 'integer',
        'team_size' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'orchestration_started_at' => 'datetime',
        'orchestration_completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public function getProgressPercentageAttribute()
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;

        $completed = $this->tasks()->where('status', 'completed')->count();
        return round(($completed / $total) * 100);
    }

    public function getTaskCountsAttribute()
    {
        return [
            'total' => $this->tasks()->count(),
            'ai' => $this->tasks()->where('type', 'ai')->count(),
            'human' => $this->tasks()->where('type', 'human')->count(),
            'hitl' => $this->tasks()->where('type', 'hitl')->count(),
            'completed' => $this->tasks()->where('status', 'completed')->count(),
        ];
    }

    /**
     * Check if project can be started
     */
    public function canBeStarted(): bool
    {
        return $this->status === 'draft'
            && $this->tasks()->count() > 0;
    }

    /**
     * Check if orchestration is running
     */
    public function isOrchestrating(): bool
    {
        return $this->orchestration_status === 'running';
    }
}
