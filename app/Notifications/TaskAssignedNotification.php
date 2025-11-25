<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $assignedBy = 'System'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'title' => 'New Task Assigned',
            'message' => "You've been assigned to: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'assigned_by' => $this->assignedBy,
            'icon' => '📋',
            'url' => route('tasks.show', $this->task->id),
        ];
    }
}
