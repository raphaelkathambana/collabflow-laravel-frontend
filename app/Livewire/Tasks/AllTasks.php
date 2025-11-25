<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\Project;
use App\Events\TaskCompleted;
use Livewire\Component;

class AllTasks extends Component
{
    public $search = '';
    public $typeFilter = 'all';
    public $statusFilter = 'all';
    public $projectFilter = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    // Task modal
    public $showTaskModal = false;
    public $editingTaskId = null;
    public $taskName = '';
    public $taskDescription = '';
    public $taskType = 'human';
    public $taskStatus = 'pending';
    public $taskProjectId = '';
    public $taskDueDate = '';
    public $taskEstimatedHours = '';

    // Task detail modal
    public $showDetailModal = false;
    public $detailTask = null;

    // Bulk actions
    public $bulkEditMode = false;
    public $selectedTasks = [];
    public $selectAll = false;

    // Delete confirmation
    public $showDeleteConfirm = false;
    public $deletingTaskId = null;

    public function toggleBulkEditMode()
    {
        $this->bulkEditMode = !$this->bulkEditMode;

        // Clear selections when exiting bulk edit mode
        if (!$this->bulkEditMode) {
            $this->selectedTasks = [];
            $this->selectAll = false;
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedTasks = $this->tasks->pluck('id')->toArray();
        } else {
            $this->selectedTasks = [];
        }
    }

    public function openCreateTaskModal()
    {
        $this->resetTaskForm();
        $this->showTaskModal = true;
    }

    public function openEditTaskModal($taskId)
    {
        // Close detail modal if it's open
        if ($this->showDetailModal) {
            $this->closeDetailModal();
        }

        $task = Task::where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $this->editingTaskId = $task->id;
        $this->taskName = $task->name;
        $this->taskDescription = $task->description ?? '';
        $this->taskType = $task->type;
        $this->taskStatus = $task->status;
        $this->taskProjectId = $task->project_id;
        $this->taskDueDate = $task->due_date ? $task->due_date->format('Y-m-d') : '';
        $this->taskEstimatedHours = $task->estimated_hours ?? '';
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->resetTaskForm();
        $this->resetValidation();
    }

    public function resetTaskForm()
    {
        $this->editingTaskId = null;
        $this->taskName = '';
        $this->taskDescription = '';
        $this->taskType = 'human';
        $this->taskStatus = 'pending';
        $this->taskProjectId = '';
        $this->taskDueDate = '';
        $this->taskEstimatedHours = '';
    }

    public function saveTask()
    {
        $this->validate([
            'taskName' => 'required|min:3|max:255',
            'taskDescription' => 'nullable|max:1000',
            'taskType' => 'required|in:ai,human,hitl',
            'taskStatus' => 'required|in:pending,in_progress,completed,blocked',
            'taskProjectId' => 'required|exists:projects,id',
            'taskDueDate' => 'nullable|date',
            'taskEstimatedHours' => 'nullable|numeric|min:0.5|max:1000',
        ], [
            'taskName.required' => 'Task name is required.',
            'taskName.min' => 'Task name must be at least 3 characters.',
            'taskProjectId.required' => 'Please select a project.',
            'taskProjectId.exists' => 'Selected project does not exist.',
            'taskEstimatedHours.min' => 'Estimated hours must be at least 0.5.',
        ]);

        // Verify the project belongs to the user
        $project = Project::where('id', $this->taskProjectId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($this->editingTaskId) {
            // Update existing task
            $task = Task::where('id', $this->editingTaskId)
                ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
                ->firstOrFail();

            $task->update([
                'name' => $this->taskName,
                'description' => $this->taskDescription,
                'type' => $this->taskType,
                'status' => $this->taskStatus,
                'project_id' => $this->taskProjectId,
                'due_date' => $this->taskDueDate ?: null,
                'estimated_hours' => $this->taskEstimatedHours ?: null,
            ]);

            session()->flash('message', 'Task updated successfully!');
        } else {
            // Create new task
            Task::create([
                'project_id' => $this->taskProjectId,
                'name' => $this->taskName,
                'description' => $this->taskDescription,
                'type' => $this->taskType,
                'status' => $this->taskStatus,
                'due_date' => $this->taskDueDate ?: null,
                'estimated_hours' => $this->taskEstimatedHours ?: null,
            ]);

            session()->flash('message', 'Task created successfully!');
        }

        $this->closeTaskModal();
    }

    public function confirmDelete($taskId)
    {
        $this->deletingTaskId = $taskId;
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete()
    {
        $this->deletingTaskId = null;
        $this->showDeleteConfirm = false;
    }

    public function deleteTask($taskId = null)
    {
        $taskId = $taskId ?? $this->deletingTaskId;

        $task = Task::where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $task->delete();

        $this->showDeleteConfirm = false;
        $this->deletingTaskId = null;
        session()->flash('message', 'Task deleted successfully!');
    }

    public function viewTaskDetail($taskId)
    {
        $this->detailTask = Task::with(['project'])
            ->where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->detailTask = null;
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        $validStatuses = ['generated', 'pending', 'in_progress', 'completed', 'blocked'];

        if (!in_array($newStatus, $validStatuses)) {
            session()->flash('error', 'Invalid status.');
            return;
        }

        $task = Task::where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        // State transition validation
        $currentStatus = $task->status;

        // Define valid transitions
        $validTransitions = [
            'generated' => ['pending', 'in_progress'], // AI-generated tasks can be reviewed and started
            'pending' => ['in_progress', 'blocked'],
            'in_progress' => ['completed', 'blocked', 'pending'],
            'blocked' => ['pending', 'in_progress'],
            'completed' => ['in_progress'], // Allow reopening
        ];

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
            session()->flash('error', "Cannot transition from {$currentStatus} to {$newStatus}.");
            return;
        }

        $task->status = $newStatus;
        $task->save();

        // Dispatch TaskCompleted event for orchestration system
        if ($newStatus === 'completed' && $task->project->orchestration_status === 'running') {
            event(new TaskCompleted($task));
        }

        session()->flash('message', 'Task status updated successfully!');
    }

    public function toggleTaskStatus($taskId)
    {
        $task = Task::where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $task->status = $task->status === 'completed' ? 'pending' : 'completed';
        $task->save();
    }

    public function bulkUpdateStatus($status)
    {
        if (empty($this->selectedTasks)) {
            session()->flash('error', 'No tasks selected.');
            return;
        }

        Task::whereIn('id', $this->selectedTasks)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->update(['status' => $status]);

        $this->selectedTasks = [];
        $this->selectAll = false;
        session()->flash('message', count($this->selectedTasks) . ' tasks updated successfully!');
    }

    public function bulkDelete()
    {
        if (empty($this->selectedTasks)) {
            session()->flash('error', 'No tasks selected.');
            return;
        }

        $count = count($this->selectedTasks);

        Task::whereIn('id', $this->selectedTasks)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->delete();

        $this->selectedTasks = [];
        $this->selectAll = false;
        session()->flash('message', "{$count} tasks deleted successfully!");
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getTasksProperty()
    {
        $query = Task::with(['project'])
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()));

        // Apply search
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Apply type filter
        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply project filter
        if ($this->projectFilter !== 'all') {
            $query->where('project_id', $this->projectFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->get();
    }

    public function getTaskStatsProperty()
    {
        $allTasks = Task::whereHas('project', fn($q) => $q->where('user_id', auth()->id()))->get();

        return [
            'todo' => $allTasks->where('status', 'pending')->count(),
            'in_progress' => $allTasks->where('status', 'in_progress')->count(),
            'blocked' => $allTasks->where('status', 'blocked')->count(),
            'completed' => $allTasks->where('status', 'completed')->count(),
        ];
    }

    public function getUserProjectsProperty()
    {
        return auth()->user()->projects()->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.tasks.all-tasks', [
            'tasks' => $this->tasks,
            'taskStats' => $this->taskStats,
            'userProjects' => $this->userProjects,
        ]);
    }
}
