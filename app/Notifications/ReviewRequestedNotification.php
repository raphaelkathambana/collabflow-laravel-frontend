<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'hitl',
            'title' => 'Review Requested',
            'message' => "Your review is needed for: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'icon' => '👁️',
            'url' => route('tasks.show', $this->task->id),
        ];
    }
}
