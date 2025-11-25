<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubtaskCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $subtaskName,
        public string $completedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'title' => 'Subtask Completed',
            'message' => "Subtask '{$this->subtaskName}' completed in: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'subtask_name' => $this->subtaskName,
            'completed_by' => $this->completedBy,
            'icon' => '✔️',
            'url' => route('tasks.show', $this->task->id),
        ];
    }
}
