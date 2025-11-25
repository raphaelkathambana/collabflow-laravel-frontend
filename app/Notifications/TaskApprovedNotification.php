<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $reviewer
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'title' => 'Task Approved',
            'message' => "{$this->reviewer->name} approved: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'reviewer_name' => $this->reviewer->name,
            'icon' => '✅',
            'url' => route('tasks.show', $this->task->id),
        ];
    }
}
