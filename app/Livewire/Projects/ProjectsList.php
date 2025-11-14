<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectsList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $sortBy = 'updated_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'sortBy' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortByColumn($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Project::where('user_id', auth()->id())
            ->with('tasks');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $projects = $query->paginate(12);

        // Get statistics for filters
        $stats = [
            'all' => Project::where('user_id', auth()->id())->count(),
            'active' => Project::where('user_id', auth()->id())->where('status', 'active')->count(),
            'in_progress' => Project::where('user_id', auth()->id())->where('status', 'in_progress')->count(),
            'completed' => Project::where('user_id', auth()->id())->where('status', 'completed')->count(),
            'on_hold' => Project::where('user_id', auth()->id())->where('status', 'on_hold')->count(),
            'archived' => Project::where('user_id', auth()->id())->where('status', 'archived')->count(),
        ];

        return view('livewire.projects.projects-list', [
            'projects' => $projects,
            'stats' => $stats,
        ]);
    }
}
