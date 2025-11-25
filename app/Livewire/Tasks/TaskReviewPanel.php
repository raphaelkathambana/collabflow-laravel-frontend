<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Livewire\Component;

class TaskReviewPanel extends Component
{
    public $task;
    public $reviewNotes = '';
    public $showApproveModal = false;
    public $showRequestChangesModal = false;

    protected $rules = [
        'reviewNotes' => 'required|string|min:10|max:2000',
    ];

    public function mount(Task $task)
    {
        $this->task = $task;
    }

    public function openApproveModal()
    {
        $this->reviewNotes = '';
        $this->showApproveModal = true;
        $this->resetValidation();
    }

    public function openRequestChangesModal()
    {
        $this->reviewNotes = '';
        $this->showRequestChangesModal = true;
        $this->resetValidation();
    }

    public function closeModals()
    {
        $this->showApproveModal = false;
        $this->showRequestChangesModal = false;
        $this->reviewNotes = '';
        $this->resetValidation();
    }

    public function approve()
    {
        $this->validate();

        $this->task->approve(auth()->id(), $this->reviewNotes);
        $this->task = $this->task->fresh(['reviewer', 'activityLogs']);

        session()->flash('message', 'Task approved successfully!');
        $this->closeModals();
        $this->dispatch('taskUpdated');
    }

    public function requestChanges()
    {
        $this->validate();

        $this->task->requestChanges(auth()->id(), $this->reviewNotes);
        $this->task = $this->task->fresh(['reviewer', 'activityLogs']);

        session()->flash('message', 'Changes requested successfully!');
        $this->closeModals();
        $this->dispatch('taskUpdated');
    }

    public function getConfidencePercentage()
    {
        return $this->task->confidence_score ? round($this->task->confidence_score * 100) : null;
    }

    public function getExecutionTime()
    {
        if (!$this->task->started_at || !$this->task->completed_at) {
            return null;
        }

        $diff = $this->task->started_at->diff($this->task->completed_at);

        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'Just now';
        }
    }

    public function render()
    {
        return view('livewire.tasks.task-review-panel');
    }
}
