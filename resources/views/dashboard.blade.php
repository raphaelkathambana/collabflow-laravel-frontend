<x-collabflow.layout title="Dashboard - CollabFlow">
    <div class="space-y-8">
        {{-- Page Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">Dashboard</h1>
                <p class="text-sm mt-1" style="color: var(--color-text-600);">Welcome back, {{ auth()->user()->name }}!</p>
            </div>
            <a href="{{ route('projects.create') }}" wire:navigate>
                <button class="flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium transition-all bg-[var(--color-bittersweet)] hover:bg-[var(--color-primary-600)]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Project
                </button>
            </a>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- AI Projects Card --}}
            <x-dashboard.stat-card
                title="AI Projects"
                :value="$stats['ai_projects']"
                color="glaucous"
                :subtitle="$stats['ai_projects'] > 0 ? 'With AI-generated tasks' : 'No AI projects yet'">
                <x-slot:icon>
                    <svg class="h-8 w-8" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </x-slot:icon>
            </x-dashboard.stat-card>

            {{-- Active Tasks Card --}}
            <x-dashboard.stat-card
                title="Active Tasks"
                :value="$stats['active_tasks']"
                color="orange-peel"
                subtitle="In progress or pending">
                <x-slot:icon>
                    <svg class="h-8 w-8" style="color: var(--color-orange-peel);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </x-slot:icon>
            </x-dashboard.stat-card>

            {{-- Completed Tasks Card --}}
            <x-dashboard.stat-card
                title="Completed"
                :value="$stats['completed_tasks']"
                color="tea-green"
                :subtitle="$stats['active_tasks'] + $stats['completed_tasks'] > 0 ? round(($stats['completed_tasks'] / ($stats['active_tasks'] + $stats['completed_tasks'])) * 100) . '% completion rate' : 'No tasks yet'">
                <x-slot:icon>
                    <svg class="h-8 w-8" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot:icon>
            </x-dashboard.stat-card>

            {{-- Total Projects Card --}}
            <x-dashboard.stat-card
                title="Total Projects"
                :value="$stats['total_projects']"
                color="text"
                subtitle="All your projects">
                <x-slot:icon>
                    <svg class="h-8 w-8" style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </x-slot:icon>
            </x-dashboard.stat-card>
        </div>

        {{-- Recent Projects Section --}}
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">Recent Projects</h2>
                @if($recentProjects->count() > 0)
                    <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-medium hover:underline" style="color: var(--color-glaucous);">
                        View all projects →
                    </a>
                @endif
            </div>

            @if($recentProjects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recentProjects as $project)
                        <x-dashboard.project-card :project="$project" />
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-12 px-4 rounded-lg border" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
                    <svg class="mx-auto h-12 w-12 mb-4" style="color: var(--color-text-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium mb-2" style="color: var(--color-text-800);">No projects yet</h3>
                    <p class="text-sm mb-4" style="color: var(--color-text-600);">Get started by creating your first project</p>
                    <a href="{{ route('projects.create') }}" wire:navigate>
                        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium transition-all bg-[var(--color-glaucous)] hover:bg-[var(--color-accent-600)]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create Your First Project
                        </button>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-collabflow.layout>
