<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $submittedBy = 'AI Agent'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'title' => 'Work Submitted',
            'message' => "{$this->submittedBy} submitted work for: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'submitted_by' => $this->submittedBy,
            'icon' => '📝',
            'url' => route('tasks.show', $this->task->id),
        ];
    }
}
