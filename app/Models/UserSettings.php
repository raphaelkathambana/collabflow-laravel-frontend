<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'weekly_digest',
        'task_reminders',
        'project_updates',
        'profile_visibility',
        'data_collection',
        'timezone',
        'language',
        'date_format',
        'time_format',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'weekly_digest' => 'boolean',
        'task_reminders' => 'boolean',
        'project_updates' => 'boolean',
        'profile_visibility' => 'boolean',
        'data_collection' => 'boolean',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
