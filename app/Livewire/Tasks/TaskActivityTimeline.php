<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Livewire\Component;

class TaskActivityTimeline extends Component
{
    public $task;
    public $activities = [];
    public $filter = 'all'; // all, ai, human, system

    public function mount(Task $task)
    {
        $this->task = $task;
        $this->loadActivities();
    }

    public function loadActivities()
    {
        $query = $this->task->activityLogs()->with('user');

        // Apply filters
        if ($this->filter === 'ai') {
            $query->whereIn('action', [
                'work_submitted',
                'subtask_completed',
                'ai_executed',
            ]);
        } elseif ($this->filter === 'human') {
            $query->whereIn('action', [
                'approved',
                'changes_requested',
                'review_requested',
                'subtask_added',
                'subtask_updated',
            ]);
        } elseif ($this->filter === 'system') {
            $query->whereIn('action', [
                'task_created',
                'status_changed',
            ]);
        }

        $this->activities = $query->get()->toArray();
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->loadActivities();
    }

    public function getActivityIcon($action)
    {
        return match($action) {
            'work_submitted' => '📝',
            'approved' => '✅',
            'changes_requested' => '🔄',
            'review_requested' => '👁️',
            'status_changed' => '🔔',
            'subtask_added' => '➕',
            'subtask_updated' => '✏️',
            'subtask_deleted' => '🗑️',
            'subtask_completed' => '✔️',
            'ai_executed' => '🤖',
            'task_created' => '🎯',
            default => '📌',
        };
    }

    public function getActivityColor($action)
    {
        return match($action) {
            'work_submitted', 'ai_executed' => 'blue',
            'approved', 'subtask_completed' => 'green',
            'changes_requested' => 'orange',
            'review_requested' => 'purple',
            'status_changed' => 'gray',
            'subtask_deleted' => 'red',
            default => 'gray',
        };
    }

    public function getActivityTitle($action)
    {
        return match($action) {
            'work_submitted' => 'Work Submitted',
            'approved' => 'Task Approved',
            'changes_requested' => 'Changes Requested',
            'review_requested' => 'Review Requested',
            'status_changed' => 'Status Changed',
            'subtask_added' => 'Subtask Added',
            'subtask_updated' => 'Subtask Updated',
            'subtask_deleted' => 'Subtask Deleted',
            'subtask_completed' => 'Subtask Completed',
            'ai_executed' => 'AI Execution',
            'task_created' => 'Task Created',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    public function render()
    {
        return view('livewire.tasks.task-activity-timeline');
    }
}
