<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Livewire\Component;

class TaskWorkDisplay extends Component
{
    public $task;
    public $output;
    public $showRawJson = false;

    public function mount(Task $task)
    {
        $this->task = $task;
        $this->output = $task->output;
    }

    public function toggleRawJson()
    {
        $this->showRawJson = !$this->showRawJson;
    }

    public function getOutputTypeLabel()
    {
        return match($this->output['type'] ?? 'unknown') {
            'code' => '💻 Code',
            'analysis' => '📊 Analysis',
            'documentation' => '📝 Documentation',
            'design' => '🎨 Design',
            'data' => '📈 Data',
            default => '📄 Output',
        };
    }

    public function getOutputTypeColor()
    {
        return match($this->output['type'] ?? 'unknown') {
            'code' => 'blue',
            'analysis' => 'purple',
            'documentation' => 'green',
            'design' => 'pink',
            'data' => 'orange',
            default => 'gray',
        };
    }

    public function render()
    {
        return view('livewire.tasks.task-work-display');
    }
}
