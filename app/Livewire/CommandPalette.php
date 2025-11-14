<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Project;
use App\Models\Task;

class CommandPalette extends Component
{
    public $isOpen = false;
    public $query = '';
    public $activeTab = 'search'; // search or ai
    public $selectedIndex = 0;

    #[On('open-command-palette')]
    public function open()
    {
        $this->isOpen = true;
        $this->query = '';
        $this->selectedIndex = 0;
    }

    public function close()
    {
        $this->isOpen = false;
        $this->query = '';
        $this->activeTab = 'search';
        $this->selectedIndex = 0;
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->selectedIndex = 0;
    }

    public function updatedQuery()
    {
        $this->selectedIndex = 0;
    }

    public function selectAction($route)
    {
        $this->close();
        $this->redirect(route($route));
    }

    public function selectProject($id)
    {
        $this->close();
        $this->redirect(route('projects.show', $id));
    }

    public function selectTask($id)
    {
        $this->close();
        $this->redirect(route('tasks.show', $id));
    }

    public function getProjects()
    {
        return Project::where('user_id', auth()->id())
            ->select('id', 'name', 'progress')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'progress' => $project->progress ?? 0,
                    'type' => 'project',
                    'route' => 'projects.show',
                ];
            })
            ->toArray();
    }

    public function getTasks()
    {
        return Task::whereHas('project', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with('project:id,name')
            ->select('id', 'name', 'project_id', 'status', 'type')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->name,
                    'project' => $task->project->name ?? 'No Project',
                    'status' => $task->status,
                    'task_type' => $task->type,
                    'type' => 'task',
                ];
            })
            ->toArray();
    }

    public function getQuickActions()
    {
        return [
            [
                'label' => 'Create New Project',
                'icon' => 'plus',
                'route' => 'projects.create',
                'type' => 'action',
            ],
            [
                'label' => 'View My Tasks',
                'icon' => 'clipboard',
                'route' => 'tasks.index',
                'type' => 'action',
            ],
            [
                'label' => 'View All Projects',
                'icon' => 'folder',
                'route' => 'projects.index',
                'type' => 'action',
            ],
            [
                'label' => 'View Schedule',
                'icon' => 'calendar',
                'route' => 'schedule.index',
                'type' => 'action',
            ],
            [
                'label' => 'Go to Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
                'type' => 'action',
            ],
        ];
    }

    public function getSearchResults()
    {
        if (empty($this->query)) {
            return [
                'actions' => $this->getQuickActions(),
                'projects' => [],
                'tasks' => [],
            ];
        }

        $query = strtolower($this->query);

        // Filter projects
        $projects = array_filter($this->getProjects(), function ($project) use ($query) {
            return str_contains(strtolower($project['name']), $query);
        });

        // Filter tasks
        $tasks = array_filter($this->getTasks(), function ($task) use ($query) {
            return str_contains(strtolower($task['title']), $query) ||
                   str_contains(strtolower($task['project']), $query);
        });

        // Filter actions
        $actions = array_filter($this->getQuickActions(), function ($action) use ($query) {
            return str_contains(strtolower($action['label']), $query);
        });

        return [
            'actions' => array_values($actions),
            'projects' => array_values($projects),
            'tasks' => array_values($tasks),
        ];
    }

    public function getTotalResults()
    {
        $results = $this->getSearchResults();
        return count($results['actions']) + count($results['projects']) + count($results['tasks']);
    }

    public function render()
    {
        return view('livewire.command-palette', [
            'results' => $this->getSearchResults(),
            'totalResults' => $this->getTotalResults(),
        ]);
    }
}
