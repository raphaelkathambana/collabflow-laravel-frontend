<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Carbon\Carbon;

class TimelineView extends Component
{
    public $selectedDate;
    public $filters = [];

    public function mount($selectedDate, $filters)
    {
        $this->selectedDate = $selectedDate;
        $this->filters = $filters;
    }

    public function getMockProjects()
    {
        $now = Carbon::now();
        return [
            [
                'id' => '1',
                'name' => 'Website Redesign',
                'startDate' => $now->copy()->addDays(0),
                'endDate' => $now->copy()->addDays(15),
                'progress' => 65,
                'tasks' => [
                    ['name' => 'Design system', 'type' => 'human', 'start' => 0, 'duration' => 5],
                    ['name' => 'Component library', 'type' => 'ai', 'start' => 3, 'duration' => 4],
                    ['name' => 'Review designs', 'type' => 'hitl', 'start' => 7, 'duration' => 2],
                    ['name' => 'Deploy', 'type' => 'human', 'start' => 13, 'duration' => 2],
                ],
            ],
            [
                'id' => '2',
                'name' => 'ML Pipeline',
                'startDate' => $now->copy()->subDays(3),
                'endDate' => $now->copy()->addDays(10),
                'progress' => 80,
                'tasks' => [
                    ['name' => 'Data collection', 'type' => 'ai', 'start' => -3, 'duration' => 3],
                    ['name' => 'Model training', 'type' => 'ai', 'start' => 0, 'duration' => 6],
                    ['name' => 'Validate results', 'type' => 'hitl', 'start' => 6, 'duration' => 2],
                    ['name' => 'Deploy model', 'type' => 'human', 'start' => 8, 'duration' => 2],
                ],
            ],
            [
                'id' => '3',
                'name' => 'Mobile App',
                'startDate' => $now->copy()->addDays(5),
                'endDate' => $now->copy()->addDays(31),
                'progress' => 30,
                'tasks' => [
                    ['name' => 'UI mockups', 'type' => 'human', 'start' => 5, 'duration' => 4],
                    ['name' => 'Generate screens', 'type' => 'ai', 'start' => 9, 'duration' => 5],
                    ['name' => 'Review & refine', 'type' => 'hitl', 'start' => 14, 'duration' => 3],
                ],
            ],
        ];
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
        // Generate 21 days for the timeline
        $startDate = Carbon::parse($this->selectedDate)->subDays(7);
        $days = collect(range(0, 20))->map(function ($i) use ($startDate) {
            return $startDate->copy()->addDays($i);
        });

        return view('livewire.schedule.timeline-view', [
            'projects' => $this->getMockProjects(),
            'days' => $days,
            'startDate' => $startDate,
        ]);
    }
}
