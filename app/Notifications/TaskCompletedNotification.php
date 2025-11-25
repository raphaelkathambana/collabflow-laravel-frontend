<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $completedBy = 'System'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'title' => 'Task Completed',
            'message' => "{$this->task->name} has been completed",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'completed_by' => $this->completedBy,
            'icon' => '✅',
            'url' => route('tasks.show', $this->task->id),
        ];
    }
}
