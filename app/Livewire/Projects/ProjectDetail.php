<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Task;
use App\Events\TaskCompleted;
use App\Events\ProjectStarted;
use Livewire\Component;

class ProjectDetail extends Component
{
    public $projectId;
    public $project;
    public $activeTab = 'tasks';

    // Task filtering
    public $taskSearch = '';
    public $taskTypeFilter = 'all';
    public $taskStatusFilter = 'all';

    // Project editing
    public $editingProject = false;
    public $editName;
    public $editDescription;
    public $editStatus;
    public $editDomain;
    public $editStartDate;
    public $editEndDate;

    // Task creation/editing
    public $showTaskModal = false;
    public $editingTaskId = null;
    public $taskName = '';
    public $taskDescription = '';
    public $taskType = 'human';
    public $taskStatus = 'pending';
    public $taskDueDate = '';
    public $taskEstimatedHours = '';

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->loadProject();
        $this->initializeEditFields();
    }

    public function loadProject()
    {
        $this->project = Project::with(['tasks', 'user'])
            ->where('id', $this->projectId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleTaskStatus($taskId)
    {
        $task = Task::where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $task->status = $task->status === 'completed' ? 'pending' : 'completed';
        $task->save();

        $this->loadProject();
    }

    public function updateTaskStatus($taskId, $newStatus)
    {
        $validStatuses = ['generated', 'pending', 'in_progress', 'review', 'completed', 'cancelled', 'blocked'];

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
            'pending' => ['in_progress', 'blocked', 'cancelled'],
            'in_progress' => ['review', 'completed', 'blocked', 'pending', 'cancelled'],
            'review' => ['in_progress', 'completed', 'pending'],
            'blocked' => ['pending', 'in_progress', 'cancelled'],
            'completed' => ['in_progress'], // Allow reopening
            'cancelled' => ['pending'], // Allow uncancel
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

        $this->loadProject();
        session()->flash('message', 'Task status updated successfully!');
    }

    public function deleteTask($taskId)
    {
        $task = Task::where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $task->delete();
        $this->loadProject();
    }

    public function openCreateTaskModal()
    {
        $this->resetTaskForm();
        $this->showTaskModal = true;
    }

    public function openEditTaskModal($taskId)
    {
        $task = Task::where('id', $taskId)
            ->whereHas('project', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $this->editingTaskId = $task->id;
        $this->taskName = $task->name;
        $this->taskDescription = $task->description ?? '';
        $this->taskType = $task->type;
        $this->taskStatus = $task->status;
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
        $this->taskDueDate = '';
        $this->taskEstimatedHours = '';
    }

    public function saveTask()
    {
        $this->validate([
            'taskName' => 'required|min:3|max:255',
            'taskDescription' => 'nullable|max:1000',
            'taskType' => 'required|in:ai,human,hitl',
            'taskStatus' => 'required|in:pending,in_progress,completed',
            'taskDueDate' => 'nullable|date|after:today',
            'taskEstimatedHours' => 'nullable|numeric|min:0.5|max:1000',
        ], [
            'taskName.required' => 'Task name is required.',
            'taskName.min' => 'Task name must be at least 3 characters.',
            'taskDueDate.after' => 'Due date must be in the future.',
            'taskEstimatedHours.min' => 'Estimated hours must be at least 0.5.',
        ]);

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
                'due_date' => $this->taskDueDate ?: null,
                'estimated_hours' => $this->taskEstimatedHours ?: null,
            ]);

            session()->flash('message', 'Task updated successfully!');
        } else {
            // Create new task
            Task::create([
                'project_id' => $this->projectId,
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
        $this->loadProject();
    }

    public function deleteProject()
    {
        $project = Project::where('id', $this->projectId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $project->delete();

        return redirect()->route('projects.index');
    }

    public function initializeEditFields()
    {
        $this->editName = $this->project->name;
        $this->editDescription = $this->project->description;
        $this->editStatus = $this->project->status;
        $this->editDomain = $this->project->domain;
        $this->editStartDate = $this->project->start_date ? $this->project->start_date->format('Y-m-d') : null;
        $this->editEndDate = $this->project->end_date ? $this->project->end_date->format('Y-m-d') : null;
    }

    public function startEditingProject()
    {
        $this->editingProject = true;
    }

    public function cancelEditingProject()
    {
        $this->editingProject = false;
        $this->initializeEditFields();
        $this->resetValidation();
    }

    public function updateProject()
    {
        $this->validate([
            'editName' => 'required|min:3|max:255',
            'editDescription' => 'required|min:10',
            'editStatus' => 'required|in:planning,active,on_hold,completed,cancelled',
            'editDomain' => 'required|in:software_development,research_analysis,marketing_campaign,custom',
            'editStartDate' => 'required|date',
            'editEndDate' => 'required|date|after:editStartDate',
        ], [
            'editName.required' => 'Project name is required.',
            'editName.min' => 'Project name must be at least 3 characters.',
            'editDescription.required' => 'Description is required.',
            'editDescription.min' => 'Description must be at least 10 characters.',
            'editStartDate.required' => 'Start date is required.',
            'editEndDate.required' => 'End date is required.',
            'editEndDate.after' => 'End date must be after start date.',
        ]);

        $project = Project::where('id', $this->projectId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $project->update([
            'name' => $this->editName,
            'description' => $this->editDescription,
            'status' => $this->editStatus,
            'domain' => $this->editDomain,
            'start_date' => $this->editStartDate,
            'end_date' => $this->editEndDate,
        ]);

        $this->editingProject = false;
        $this->loadProject();
        $this->initializeEditFields();

        session()->flash('message', 'Project updated successfully!');
    }

    public function updateProjectStatus($newStatus)
    {
        $validStatuses = ['draft', 'planning', 'active', 'in_progress', 'on_hold', 'completed', 'cancelled'];

        if (!in_array($newStatus, $validStatuses)) {
            session()->flash('error', 'Invalid project status.');
            return;
        }

        $project = Project::where('id', $this->projectId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $oldStatus = $project->status;
        $project->update(['status' => $newStatus]);

        // Auto-start orchestration when project becomes active
        if ($newStatus === 'active' && $oldStatus !== 'active' && $project->orchestration_status === 'not_started') {
            $project->update([
                'orchestration_status' => 'running',
                'orchestration_started_at' => now()
            ]);

            // Trigger orchestration
            event(new ProjectStarted($project));

            session()->flash('message', 'Project activated! AI orchestration has started automatically.');
        } else {
            session()->flash('message', 'Project status updated to ' . ucfirst($newStatus));
        }

        $this->loadProject();
        $this->initializeEditFields();
    }

    public function getFilteredTasksProperty()
    {
        $query = $this->project->tasks();

        // Apply search
        if ($this->taskSearch) {
            $query->where('name', 'like', '%' . $this->taskSearch . '%');
        }

        // Apply type filter
        if ($this->taskTypeFilter !== 'all') {
            $query->where('type', $this->taskTypeFilter);
        }

        // Apply status filter
        if ($this->taskStatusFilter !== 'all') {
            $query->where('status', $this->taskStatusFilter);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    public function render()
    {
        return view('livewire.projects.project-detail', [
            'filteredTasks' => $this->filteredTasks,
        ]);
    }
}
