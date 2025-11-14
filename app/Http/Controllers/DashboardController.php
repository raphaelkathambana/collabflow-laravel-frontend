<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get statistics
        $stats = [
            'total_projects' => Project::where('user_id', $user->id)->count(),
            'ai_projects' => Project::where('user_id', $user->id)
                ->whereHas('tasks', fn($q) => $q->where('type', 'ai'))
                ->count(),
            'active_tasks' => Task::whereHas('project', fn($q) => $q->where('user_id', $user->id))
                ->active()
                ->count(),
            'completed_tasks' => Task::whereHas('project', fn($q) => $q->where('user_id', $user->id))
                ->completed()
                ->count(),
            'team_members' => User::count(),
        ];

        // Get recent projects
        $recentProjects = Project::where('user_id', $user->id)
            ->with('tasks')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', compact('stats', 'recentProjects'));
    }
}
