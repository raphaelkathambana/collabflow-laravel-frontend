<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    // Disable updated_at timestamp (only need created_at)
    const UPDATED_AT = null;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'action',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
