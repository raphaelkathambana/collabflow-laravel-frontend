<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Carbon\Carbon;

class UpcomingTasks extends Component
{
    public $filters = [];

    public function mount($filters)
    {
        $this->filters = $filters;
    }

    public function getMockUpcomingTasks()
    {
        $now = Carbon::now();
        return [
            [
                'id' => '1',
                'title' => 'Design system review',
                'project' => 'Website Redesign',
                'type' => 'human',
                'dueDate' => $now->copy()->addDays(3),
                'dueTime' => '10:00 AM',
                'assignee' => 'Sarah Chen',
                'priority' => 'high',
            ],
            [
                'id' => '2',
                'title' => 'AI model training',
                'project' => 'ML Pipeline',
                'type' => 'ai',
                'dueDate' => $now->copy()->addDays(3),
                'dueTime' => '2:00 PM',
                'assignee' => 'AI Agent',
                'priority' => 'medium',
            ],
            [
                'id' => '3',
                'title' => 'Review AI outputs',
                'project' => 'ML Pipeline',
                'type' => 'hitl',
                'dueDate' => $now->copy()->addDays(5),
                'dueTime' => '11:00 AM',
                'assignee' => 'Mike Johnson',
                'priority' => 'high',
            ],
            [
                'id' => '4',
                'title' => 'Component library setup',
                'project' => 'Website Redesign',
                'type' => 'human',
                'dueDate' => $now->copy()->addDays(6),
                'dueTime' => '9:00 AM',
                'assignee' => 'Sarah Chen',
                'priority' => 'medium',
            ],
            [
                'id' => '5',
                'title' => 'Deploy to production',
                'project' => 'Website Redesign',
                'type' => 'human',
                'dueDate' => $now->copy()->addDays(7),
                'dueTime' => '3:00 PM',
                'assignee' => 'DevOps Team',
                'priority' => 'high',
            ],
        ];
    }

    public function getFilteredTasks()
    {
        $tasks = $this->getMockUpcomingTasks();

        if (empty($this->filters['taskTypes'])) {
            return $tasks;
        }

        return array_filter($tasks, function ($task) {
            return in_array($task['type'], $this->filters['taskTypes']);
        });
    }

    public function getDaysUntil($date)
    {
        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($date)->startOfDay();
        $diffDays = $today->diffInDays($dueDate, false);

        if ($diffDays === 0) return 'Today';
        if ($diffDays === 1) return 'Tomorrow';
        if ($diffDays < 0) return 'Overdue';
        return "In {$diffDays} days";
    }

    public function render()
    {
        return view('livewire.schedule.upcoming-tasks', [
            'tasks' => $this->getFilteredTasks(),
        ]);
    }
}
