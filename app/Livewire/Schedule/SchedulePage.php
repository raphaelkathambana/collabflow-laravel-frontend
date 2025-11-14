<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Carbon\Carbon;

class SchedulePage extends Component
{
    public $view = 'month'; // month, week, day, timeline, list
    public $selectedDate;
    public $filters = [
        'taskTypes' => [],
    ];

    public function mount()
    {
        $this->selectedDate = Carbon::now()->toDateString();
    }

    public function changeView($view)
    {
        $this->view = $view;
    }

    public function navigateDate($direction)
    {
        $date = Carbon::parse($this->selectedDate);

        if ($this->view === 'month') {
            $date = $direction === 'next' ? $date->addMonth() : $date->subMonth();
        } elseif ($this->view === 'week') {
            $date = $direction === 'next' ? $date->addWeek() : $date->subWeek();
        } else {
            $date = $direction === 'next' ? $date->addDay() : $date->subDay();
        }

        $this->selectedDate = $date->toDateString();
    }

    public function goToToday()
    {
        $this->selectedDate = Carbon::now()->toDateString();
    }

    public function toggleTaskTypeFilter($type)
    {
        if (in_array($type, $this->filters['taskTypes'])) {
            $this->filters['taskTypes'] = array_values(array_diff($this->filters['taskTypes'], [$type]));
        } else {
            $this->filters['taskTypes'][] = $type;
        }
    }

    public function render()
    {
        return view('livewire.schedule.schedule-page');
    }
}