<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Carbon\Carbon;

class CalendarView extends Component
{
    public $view = 'month';
    public $selectedDate;
    public $filters = [];

    public function mount($view, $selectedDate, $filters)
    {
        $this->view = $view;
        $this->selectedDate = $selectedDate;
        $this->filters = $filters;
    }

    public function getDaysInMonth()
    {
        $date = Carbon::parse($this->selectedDate);
        $firstDay = $date->copy()->startOfMonth();
        $lastDay = $date->copy()->endOfMonth();
        $daysInMonth = $lastDay->day;
        $startingDayOfWeek = $firstDay->dayOfWeek;

        $days = [];
        // Add empty cells for days before the first of the month
        for ($i = 0; $i < $startingDayOfWeek; $i++) {
            $days[] = null;
        }
        // Add all days of the month
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = $date->copy()->setDay($i);
        }
        return $days;
    }

    public function getDaysInWeek()
    {
        $date = Carbon::parse($this->selectedDate);
        $startOfWeek = $date->copy()->startOfWeek();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $startOfWeek->copy()->addDays($i);
        }
        return $days;
    }

    public function getHoursInDay()
    {
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[] = sprintf('%02d:00', $i);
        }
        return $hours;
    }

    public function getMockTasks()
    {
        return [
            [
                'id' => '1',
                'title' => 'Design system review',
                'project' => 'Website Redesign',
                'type' => 'human',
                'date' => Carbon::now()->addDays(3),
                'time' => '10:00 AM',
            ],
            [
                'id' => '2',
                'title' => 'AI model training',
                'project' => 'ML Pipeline',
                'type' => 'ai',
                'date' => Carbon::now()->addDays(3),
                'time' => '2:00 PM',
            ],
            [
                'id' => '3',
                'title' => 'Review AI outputs',
                'project' => 'ML Pipeline',
                'type' => 'hitl',
                'date' => Carbon::now()->addDays(5),
                'time' => '11:00 AM',
            ],
            [
                'id' => '4',
                'title' => 'Deploy to production',
                'project' => 'Website Redesign',
                'type' => 'human',
                'date' => Carbon::now()->addDays(7),
                'time' => '3:00 PM',
            ],
        ];
    }

    public function getTasksForDate($date)
    {
        if (!$date) return [];

        $tasks = $this->getMockTasks();
        $filteredTasks = [];

        foreach ($tasks as $task) {
            $taskDate = Carbon::parse($task['date']);
            $isSameDay = $taskDate->isSameDay($date);
            $matchesFilter = empty($this->filters['taskTypes']) || in_array($task['type'], $this->filters['taskTypes']);

            if ($isSameDay && $matchesFilter) {
                $filteredTasks[] = $task;
            }
        }

        return $filteredTasks;
    }

    public function getTaskColor($type)
    {
        return match ($type) {
            'ai' => 'var(--color-glaucous)',
            'human' => 'var(--color-tea-green)',
            'hitl' => 'var(--color-orange-peel)',
            default => 'var(--color-text-400)',
        };
    }

    public function render()
    {
        $data = [
            'tasks' => $this->getMockTasks(),
        ];

        if ($this->view === 'month') {
            $data['days'] = $this->getDaysInMonth();
        } elseif ($this->view === 'week') {
            $data['days'] = $this->getDaysInWeek();
            $data['hours'] = $this->getHoursInDay();
        } elseif ($this->view === 'day') {
            $data['selectedDay'] = Carbon::parse($this->selectedDate);
            $data['hours'] = $this->getHoursInDay();
        } elseif ($this->view === 'list') {
            // List view will show all tasks in a list format
        }

        return view('livewire.schedule.calendar-view', $data);
    }
}
