<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChangesRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $reviewer,
        public string $notes
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'title' => 'Changes Requested',
            'message' => "{$this->reviewer->name} requested changes on: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'reviewer_name' => $this->reviewer->name,
            'notes' => $this->notes,
            'icon' => '🔄',
            'url' => route('tasks.show', $this->task->id),
        ];
    }
}
