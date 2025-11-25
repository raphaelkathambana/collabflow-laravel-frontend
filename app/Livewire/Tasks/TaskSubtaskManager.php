<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Livewire\Component;

class TaskSubtaskManager extends Component
{
    public $task;
    public $subtasks = [];
    public $showAddModal = false;
    public $editingSubtaskId = null;

    // Form fields
    public $subtaskName = '';
    public $subtaskDescription = '';
    public $subtaskType = 'human';
    public $subtaskIsCheckpoint = false;
    public $subtaskEstimatedHours = '';

    protected $rules = [
        'subtaskName' => 'required|min:3|max:255',
        'subtaskDescription' => 'nullable|max:1000',
        'subtaskType' => 'required|in:ai,human,hitl',
        'subtaskIsCheckpoint' => 'boolean',
        'subtaskEstimatedHours' => 'nullable|numeric|min:0.1|max:1000',
    ];

    public function mount(Task $task)
    {
        $this->task = $task;
        $this->refreshSubtasks();
    }

    public function refreshSubtasks()
    {
        $this->task = $this->task->fresh();
        $this->subtasks = $this->task->getSubtasks();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->subtaskName = '';
        $this->subtaskDescription = '';
        $this->subtaskType = 'human';
        $this->subtaskIsCheckpoint = false;
        $this->subtaskEstimatedHours = '';
        $this->editingSubtaskId = null;
    }

    public function saveSubtask()
    {
        $this->validate();

        $subtaskData = [
            'name' => $this->subtaskName,
            'description' => $this->subtaskDescription,
            'type' => $this->subtaskType,
            'is_checkpoint' => $this->subtaskIsCheckpoint,
            'estimated_hours' => $this->subtaskEstimatedHours ?: null,
        ];

        if ($this->editingSubtaskId) {
            // Update existing subtask
            $this->task->updateSubtask($this->editingSubtaskId, $subtaskData);
            session()->flash('message', 'Subtask updated successfully!');
        } else {
            // Add new subtask
            $this->task->addSubtask($subtaskData);
            session()->flash('message', 'Subtask added successfully!');
        }

        $this->refreshSubtasks();
        $this->closeAddModal();
        $this->dispatch('subtasksUpdated');
    }

    public function editSubtask($subtaskId)
    {
        $subtask = $this->task->getSubtask($subtaskId);

        if ($subtask) {
            $this->editingSubtaskId = $subtaskId;
            $this->subtaskName = $subtask['name'];
            $this->subtaskDescription = $subtask['description'] ?? '';
            $this->subtaskType = $subtask['type'];
            $this->subtaskIsCheckpoint = $subtask['is_checkpoint'] ?? false;
            $this->subtaskEstimatedHours = $subtask['estimated_hours'] ?? '';
            $this->showAddModal = true;
        }
    }

    public function deleteSubtask($subtaskId)
    {
        $this->task->deleteSubtask($subtaskId);
        $this->refreshSubtasks();
        session()->flash('message', 'Subtask deleted successfully!');
        $this->dispatch('subtasksUpdated');
    }

    public function toggleSubtaskComplete($subtaskId)
    {
        $subtask = $this->task->getSubtask($subtaskId);

        if (!$subtask) {
            return;
        }

        $isCompleted = ($subtask['status'] ?? 'pending') === 'completed';

        if ($isCompleted) {
            // Mark as pending
            $this->task->updateSubtask($subtaskId, [
                'status' => 'pending',
                'completed_at' => null,
                'completed_by' => null,
            ]);
        } else {
            // Mark as completed
            $this->task->completeSubtask($subtaskId, null, auth()->user()->name ?? 'user');
        }

        $this->refreshSubtasks();
        $this->dispatch('subtasksUpdated');
    }

    public function render()
    {
        return view('livewire.tasks.task-subtask-manager');
    }
}
