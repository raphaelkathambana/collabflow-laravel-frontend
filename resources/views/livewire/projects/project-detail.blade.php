<div class="space-y-6">
    {{-- Back Button --}}
    <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 text-sm transition-colors"
       style="color: var(--color-text-600);"
       onmouseover="this.style.color='var(--color-text-800)'"
       onmouseout="this.style.color='var(--color-text-600)'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Projects
    </a>

    {{-- Project Header --}}
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 space-y-3">
            {{-- Title and Badges --}}
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-3xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">
                    {{ $project->name }}
                </h2>

                {{-- Status Badge --}}
                @php
                    $statusColors = [
                        'draft' => ['bg' => 'var(--color-background-200)', 'text' => 'var(--color-text-700)'],
                        'active' => ['bg' => 'rgba(196,214,176,0.2)', 'text' => '#316837'],
                        'in_progress' => ['bg' => 'var(--color-accent-100)', 'text' => 'var(--color-accent-700)'],
                        'completed' => ['bg' => 'var(--color-accent-100)', 'text' => 'var(--color-accent-700)'],
                        'on_hold' => ['bg' => 'var(--color-background-200)', 'text' => 'var(--color-text-500)'],
                        'archived' => ['bg' => 'var(--color-background-200)', 'text' => 'var(--color-text-500)'],
                    ];
                    $statusColor = $statusColors[$project->status] ?? $statusColors['draft'];
                @endphp
                <span class="px-3 py-1 text-sm font-medium rounded-lg border"
                      style="background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }}; border-color: {{ $statusColor['text'] }};">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>

                {{-- Domain Badge --}}
                @if($project->domain)
                    <span class="px-3 py-1 text-sm rounded-lg border"
                          style="background-color: var(--color-accent-50); color: var(--color-accent-700); border-color: var(--color-accent-200);">
                        {{ ucfirst(str_replace('_', ' ', $project->domain)) }}
                    </span>
                @endif
            </div>

            {{-- Description --}}
            <p class="max-w-3xl" style="color: var(--color-text-600);">{{ $project->description }}</p>

            {{-- Meta Info --}}
            <div class="flex flex-wrap items-center gap-6 text-sm" style="color: var(--color-text-600);">
                @if($project->start_date && $project->end_date)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $project->start_date->format('M d, Y') }} - {{ $project->end_date->format('M d, Y') }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $project->progress_percentage }}% complete</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            @if($activeTab === 'settings' && !$editingProject)
                <button type="button" wire:click="startEditingProject"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-all"
                        style="border-color: var(--color-background-300); background-color: transparent; color: var(--color-text-700);"
                        onmouseover="this.style.backgroundColor='var(--color-background-100)';"
                        onmouseout="this.style.backgroundColor='transparent';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </button>
            @endif
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium" style="color: var(--color-text-700);">Overall Progress</span>
            <span class="font-bold" style="color: var(--color-text-800);">{{ $project->progress_percentage }}%</span>
        </div>
        <div class="h-3 w-full rounded-full overflow-hidden" style="background-color: var(--color-background-200);">
            <div class="h-full rounded-full transition-all duration-500"
                 style="width: {{ $project->progress_percentage }}%; background: linear-gradient(to right, var(--color-glaucous), var(--color-tea-green));"></div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="mb-6">
        <div class="flex gap-1 border-b" style="border-color: var(--color-background-300);">
            @php
                $tabs = [
                    ['id' => 'tasks', 'label' => 'Tasks', 'icon' => 'check-square'],
                    ['id' => 'workflow', 'label' => 'Workflow', 'icon' => 'share-2'],
                    ['id' => 'analytics', 'label' => 'Analytics', 'icon' => 'bar-chart'],
                    ['id' => 'activity', 'label' => 'Activity', 'icon' => 'activity'],
                    ['id' => 'settings', 'label' => 'Settings', 'icon' => 'settings'],
                ];
            @endphp

            @foreach($tabs as $tab)
                <button
                    type="button"
                    wire:click="switchTab('{{ $tab['id'] }}')"
                    class="px-4 py-3 font-medium transition-all border-b-2"
                    style="{{ $activeTab === $tab['id']
                        ? 'border-color: var(--color-glaucous); color: var(--color-glaucous);'
                        : 'border-color: transparent; color: var(--color-text-600);' }}">
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tab Content --}}
    <div>
        {{-- Tasks Tab --}}
        @if($activeTab === 'tasks')
            <div class="space-y-4">
                {{-- Success Message --}}
                @if (session()->has('message'))
                    <div class="p-3 rounded-lg border" style="background-color: rgba(196,214,176,0.1); border-color: var(--color-tea-green); color: var(--color-tea-green);">
                        {{ session('message') }}
                    </div>
                @endif

                {{-- Task Toolbar --}}
                <div class="flex gap-3 items-center">
                    {{-- Add Task Button --}}
                    <button
                        type="button"
                        wire:click="openCreateTaskModal"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-lg transition-opacity text-white font-medium"
                        style="background-color: var(--color-glaucous);"
                        onmouseover="this.style.opacity='0.9'"
                        onmouseout="this.style.opacity='1'"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Task
                    </button>

                    {{-- Search Input with Icon --}}
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
                             style="color: var(--color-text-400);"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="taskSearch"
                            placeholder="Search tasks..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                            style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                    </div>

                    {{-- Type Filter Dropdown --}}
                    <div class="relative">
                        <select wire:model.live="taskTypeFilter"
                                class="pl-4 pr-10 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2 appearance-none cursor-pointer"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                            <option value="all">All Types</option>
                            <option value="ai">AI Tasks</option>
                            <option value="human">Human Tasks</option>
                            <option value="hitl">HITL Tasks</option>
                        </select>
                        <svg class="w-4 h-4 absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
                             style="color: var(--color-text-400);"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>

                    {{-- Status Filter Dropdown --}}
                    <div class="relative">
                        <select wire:model.live="taskStatusFilter"
                                class="pl-4 pr-10 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2 appearance-none cursor-pointer"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        <svg class="w-4 h-4 absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
                             style="color: var(--color-text-400);"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>

                {{-- Task List --}}
                <div class="space-y-2">
                    @forelse($filteredTasks as $task)
                        <div class="group flex items-start gap-4 p-4 rounded-lg border transition-all hover:border-[var(--color-background-400)] hover:shadow-sm"
                             style="background-color: var(--color-background-50); border-color: {{ $task->status === 'completed' ? 'var(--color-tea-green)' : ($task->status === 'blocked' ? 'var(--color-bittersweet)' : 'var(--color-background-300)') }};">

                            {{-- Task Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-3 mb-2">
                                    <h4 class="font-medium {{ $task->status === 'completed' ? 'line-through' : '' }}"
                                        style="color: {{ $task->status === 'completed' ? 'var(--color-text-500)' : 'var(--color-text-900)' }};">
                                        {{ $task->name }}
                                    </h4>
                                </div>

                                <div class="flex items-center gap-3 flex-wrap">
                                    {{-- Type Badge with Icon --}}
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md border {{ $task->type === 'hitl' ? 'animate-pulse-subtle' : '' }}"
                                          style="background-color: {{ $task->type === 'ai' ? 'var(--color-accent-100)' : ($task->type === 'human' ? 'rgba(196,214,176,0.2)' : 'rgba(255,159,28,0.15)') }};
                                                 color: {{ $task->type === 'ai' ? 'var(--color-accent-700)' : ($task->type === 'human' ? '#316837' : 'var(--color-orange-peel)') }};
                                                 border-color: {{ $task->type === 'ai' ? 'var(--color-accent-200)' : ($task->type === 'human' ? 'rgba(196,214,176,0.4)' : 'rgba(255,159,28,0.3)') }};">
                                        @if($task->type === 'ai')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <rect x="4" y="4" width="6" height="6" rx="1" stroke-width="2"></rect>
                                                <rect x="14" y="4" width="6" height="6" rx="1" stroke-width="2"></rect>
                                                <rect x="4" y="14" width="6" height="6" rx="1" stroke-width="2"></rect>
                                                <rect x="14" y="14" width="6" height="6" rx="1" stroke-width="2"></rect>
                                            </svg>
                                        @elseif($task->type === 'human')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        @endif
                                        {{ strtoupper($task->type) }}
                                    </span>

                                    {{-- Status Badge --}}
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md"
                                          style="background-color: {{
                                              $task->status === 'completed' ? 'rgba(196,214,176,0.2)' :
                                              ($task->status === 'in_progress' ? 'rgba(92,128,188,0.15)' :
                                              ($task->status === 'blocked' ? 'rgba(235,94,85,0.1)' : 'var(--color-background-200)'))
                                          }};
                                          color: {{
                                              $task->status === 'completed' ? 'var(--color-tea-green)' :
                                              ($task->status === 'in_progress' ? 'var(--color-glaucous)' :
                                              ($task->status === 'blocked' ? 'var(--color-bittersweet)' : 'var(--color-text-700)'))
                                          }};">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>

                                    @if($task->due_date || $task->estimated_hours)
                                        <div class="flex items-center gap-3 text-xs" style="color: var(--color-text-500);">
                                            @if($task->due_date)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    {{ $task->due_date->format('M d') }}
                                                </span>
                                            @endif
                                            @if($task->estimated_hours)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $task->estimated_hours }}h
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Status Actions & Edit/Delete (show on hover) --}}
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{-- Quick Status Change --}}
                                @if($task->status !== 'completed')
                                    <button type="button" wire:click="updateTaskStatus('{{ $task->id }}', 'completed')"
                                            class="p-2 rounded-lg transition-all"
                                            style="color: var(--color-text-500); background-color: transparent;"
                                            onmouseover="this.style.color='var(--color-tea-green)'; this.style.backgroundColor='rgba(196,214,176,0.2)';"
                                            onmouseout="this.style.color='var(--color-text-500)'; this.style.backgroundColor='transparent';"
                                            title="Mark as complete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                @endif
                                @if($task->status === 'pending')
                                    <button type="button" wire:click="updateTaskStatus('{{ $task->id }}', 'in_progress')"
                                            class="p-2 rounded-lg transition-all"
                                            style="color: var(--color-text-500); background-color: transparent;"
                                            onmouseover="this.style.color='var(--color-glaucous)'; this.style.backgroundColor='var(--color-accent-50)';"
                                            onmouseout="this.style.color='var(--color-text-500)'; this.style.backgroundColor='transparent';"
                                            title="Start task">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Edit Button --}}
                                <button
                                    type="button"
                                    wire:click="openEditTaskModal('{{ $task->id }}')"
                                    class="p-2 rounded-lg transition-all"
                                    style="color: var(--color-text-500); background-color: transparent;"
                                    onmouseover="this.style.color='var(--color-glaucous)'; this.style.backgroundColor='rgba(92,128,188,0.1)';"
                                    onmouseout="this.style.color='var(--color-text-500)'; this.style.backgroundColor='transparent';"
                                    title="Edit task"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                {{-- Delete Button --}}
                                <button
                                    type="button"
                                    wire:click="deleteTask('{{ $task->id }}')"
                                    wire:confirm="Are you sure you want to delete this task?"
                                    class="p-2 rounded-lg transition-all"
                                    style="color: var(--color-text-500); background-color: transparent;"
                                    onmouseover="this.style.color='var(--color-bittersweet)'; this.style.backgroundColor='rgba(235,94,85,0.1)';"
                                    onmouseout="this.style.color='var(--color-text-500)'; this.style.backgroundColor='transparent';"
                                    title="Delete task"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 px-4 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                            <svg class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            <h3 class="text-lg font-semibold mb-2" style="color: var(--color-text-700);">No tasks found</h3>
                            <p class="text-sm" style="color: var(--color-text-500);">
                                @if($taskSearch || $taskTypeFilter !== 'all' || $taskStatusFilter !== 'all')
                                    Try adjusting your filters to see more tasks.
                                @else
                                    Get started by creating your first task.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Workflow Tab --}}
        @if($activeTab === 'workflow')
            <div class="space-y-4">
                {{-- Info Banner --}}
                <div class="p-3 rounded-lg flex items-center gap-3" style="background-color: rgba(92, 128, 188, 0.1); border: 1px solid rgba(92, 128, 188, 0.3);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium" style="color: var(--color-text-900);">Read-Only Workflow View</p>
                        <p class="text-xs" style="color: var(--color-text-600);">This is a saved snapshot from project creation. Click any task to view its subtasks.</p>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="flex items-center gap-6 p-4 rounded-lg border" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="4" y="4" width="6" height="6" rx="1" stroke-width="2"></rect>
                            <rect x="14" y="4" width="6" height="6" rx="1" stroke-width="2"></rect>
                            <rect x="4" y="14" width="6" height="6" rx="1" stroke-width="2"></rect>
                            <rect x="14" y="14" width="6" height="6" rx="1" stroke-width="2"></rect>
                        </svg>
                        <span class="text-sm" style="color: var(--color-text-600);">AI Task</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-sm" style="color: var(--color-text-600);">Human Task</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: var(--color-orange-peel);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="text-sm" style="color: var(--color-text-600);">HITL Checkpoint</span>
                    </div>
                    <div class="ml-auto flex items-center gap-2 text-sm" style="color: var(--color-text-500);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>Click any task to view subtasks</span>
                    </div>
                </div>

                {{-- Workflow Canvas --}}
                <div class="rounded-lg overflow-hidden shadow-sm" style="background-color: var(--color-background-100); border: 1px solid var(--color-background-300);">
                    <div
                        x-data="{
                            ...projectWorkflowBridge(),
                            tasks: @js($project->tasks->map(function($task) {
                                return [
                                    'id' => $task->id,
                                    'name' => $task->name,
                                    'description' => $task->description,
                                    'type' => $task->type,
                                    'estimated_hours' => $task->estimated_hours,
                                    'status' => $task->status,
                                    'dependencies' => $task->dependencies ?? [],
                                    'subtasks' => $task->subtasks_from_metadata ?? [],
                                ];
                            })),
                            layoutDirection: 'vertical'
                        }"
                        x-init="init()"
                        x-on:destroy="destroy()"
                        wire:ignore
                        class="relative"
                    >
                        {{-- React Flow Container --}}
                        <div x-ref="workflowContainer" class="w-full h-[600px]"></div>
                    </div>
                </div>
                </div>
            </div>
        @endif

        {{-- Analytics Tab --}}
        @if($activeTab === 'analytics')
            <div class="space-y-6">
                {{-- Key Metrics --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Completion Rate --}}
                    <div class="p-6 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4" style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-sm font-medium" style="color: var(--color-text-600);">Completion Rate</h4>
                        </div>
                        <div class="text-3xl font-bold mb-1" style="font-family: Tahoma; color: var(--color-text-800);">
                            {{ $project->progress_percentage }}%
                        </div>
                        <p class="text-xs" style="color: var(--color-text-500);">
                            {{ $project->tasks->where('status', 'completed')->count() }} of {{ $project->tasks->count() }} tasks
                        </p>
                    </div>

                    {{-- Avg Task Duration --}}
                    <div class="p-6 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4" style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-sm font-medium" style="color: var(--color-text-600);">Avg. Task Duration</h4>
                        </div>
                        <div class="text-3xl font-bold mb-1" style="font-family: Tahoma; color: var(--color-text-800);">
                            18h
                        </div>
                        <p class="text-xs" style="color: var(--color-text-500);">Per task average</p>
                    </div>

                    {{-- Velocity --}}
                    <div class="p-6 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4" style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <h4 class="text-sm font-medium" style="color: var(--color-text-600);">Velocity</h4>
                        </div>
                        <div class="text-3xl font-bold mb-1" style="font-family: Tahoma; color: var(--color-text-800);">
                            3.2
                        </div>
                        <p class="text-xs" style="color: var(--color-text-500);">Tasks per week</p>
                    </div>
                </div>

                {{-- Task Distribution --}}
                <div class="p-6 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                    <h3 class="text-base font-semibold mb-6" style="font-family: Tahoma; color: var(--color-text-800);">Task Distribution</h3>
                    <div class="space-y-4">
                        {{-- AI Tasks --}}
                        @php
                            $aiTasks = $project->tasks->where('type', 'ai')->count();
                            $aiPercentage = $project->tasks->count() > 0 ? round(($aiTasks / $project->tasks->count()) * 100) : 0;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="4" y="4" width="6" height="6" rx="1" stroke-width="2"></rect>
                                        <rect x="14" y="4" width="6" height="6" rx="1" stroke-width="2"></rect>
                                        <rect x="4" y="14" width="6" height="6" rx="1" stroke-width="2"></rect>
                                        <rect x="14" y="14" width="6" height="6" rx="1" stroke-width="2"></rect>
                                    </svg>
                                    <span class="font-medium" style="color: var(--color-text-700);">AI Tasks</span>
                                </div>
                                <span style="color: var(--color-text-600);">{{ $aiTasks }} ({{ $aiPercentage }}%)</span>
                            </div>
                            <div class="h-2 w-full rounded-full overflow-hidden" style="background-color: var(--color-background-200);">
                                <div class="h-full transition-all duration-500" style="width: {{ $aiPercentage }}%; background-color: var(--color-glaucous);"></div>
                            </div>
                        </div>

                        {{-- Human Tasks --}}
                        @php
                            $humanTasks = $project->tasks->where('type', 'human')->count();
                            $humanPercentage = $project->tasks->count() > 0 ? round(($humanTasks / $project->tasks->count()) * 100) : 0;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="font-medium" style="color: var(--color-text-700);">Human Tasks</span>
                                </div>
                                <span style="color: var(--color-text-600);">{{ $humanTasks }} ({{ $humanPercentage }}%)</span>
                            </div>
                            <div class="h-2 w-full rounded-full overflow-hidden" style="background-color: var(--color-background-200);">
                                <div class="h-full transition-all duration-500" style="width: {{ $humanPercentage }}%; background-color: var(--color-tea-green);"></div>
                            </div>
                        </div>

                        {{-- HITL Checkpoints --}}
                        @php
                            $hitlTasks = $project->tasks->where('type', 'hitl')->count();
                            $hitlPending = $project->tasks->where('type', 'hitl')->where('status', '!=', 'completed')->count();
                            $hitlPercentage = $project->tasks->count() > 0 ? round(($hitlTasks / $project->tasks->count()) * 100) : 0;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" style="color: var(--color-orange-peel);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span class="font-medium" style="color: var(--color-text-700);">HITL Checkpoints</span>
                                </div>
                                <span style="color: var(--color-text-600);">{{ $hitlPending }} pending</span>
                            </div>
                            <div class="h-2 w-full rounded-full overflow-hidden" style="background-color: var(--color-background-200);">
                                <div class="h-full transition-all duration-500 animate-pulse-subtle" style="width: {{ $hitlPercentage }}%; background-color: var(--color-orange-peel);"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Activity Feed Tab --}}
        @if($activeTab === 'activity')
            <div class="p-6 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                <h3 class="text-lg font-semibold mb-6" style="font-family: Tahoma; color: var(--color-text-800);">Recent Activity</h3>

                <div class="space-y-6">
                    {{-- Mock activity items --}}
                    @php
                        $activities = [
                            ['type' => 'task_completed', 'user' => 'System', 'content' => 'Project created', 'time' => $project->created_at->diffForHumans()],
                            ['type' => 'task_completed', 'user' => auth()->user()->name, 'content' => 'completed initial setup tasks', 'time' => '2 hours ago'],
                            ['type' => 'comment', 'user' => auth()->user()->name, 'content' => 'added project goals and KPIs', 'time' => '4 hours ago'],
                        ];
                    @endphp

                    @foreach($activities as $index => $activity)
                        <div class="flex gap-4">
                            {{-- Timeline --}}
                            <div class="flex flex-col items-center">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full" style="background-color: var(--color-background-100);">
                                    @if($activity['type'] === 'task_completed')
                                        <svg class="h-4 w-4" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @elseif($activity['type'] === 'comment')
                                        <svg class="h-4 w-4" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4" style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                                @if($index < count($activities) - 1)
                                    <div class="w-0.5 flex-1 mt-2" style="background-color: var(--color-background-300); min-height: 40px;"></div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 pb-6">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full font-semibold text-sm"
                                         style="background-color: var(--color-accent-100); color: var(--color-accent-700);">
                                        {{ substr($activity['user'], 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm" style="color: var(--color-text-700);">
                                            <span class="font-semibold">{{ $activity['user'] }}</span> {{ $activity['content'] }}
                                        </p>
                                        <div class="flex items-center gap-1 mt-1 text-xs" style="color: var(--color-text-500);">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>{{ $activity['time'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Settings Tab --}}
        @if($activeTab === 'settings')
            <div class="space-y-4">
                {{-- Success Message --}}
                @if(session()->has('message'))
                    <div class="p-4 rounded-lg border flex items-center gap-3"
                         style="background-color: rgba(196,214,176,0.2); border-color: rgba(196,214,176,0.4);">
                        <svg class="w-5 h-5 flex-shrink-0" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium" style="color: #316837;">{{ session('message') }}</p>
                    </div>
                @endif

                <div class="p-6 rounded-lg border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                    <h3 class="text-lg font-semibold mb-6" style="font-family: Tahoma; color: var(--color-text-800);">Project Settings</h3>

                <div class="space-y-6 max-w-2xl">
                    {{-- Project Details --}}
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold" style="color: var(--color-text-700);">Project Details</h4>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Project Name</label>
                            <input type="text" wire:model="editName" {{ !$editingProject ? 'disabled' : '' }}
                                   class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                   style="border-color: var(--color-background-300);
                                          background-color: {{ $editingProject ? 'var(--color-background-50)' : 'var(--color-background-100)' }};
                                          color: var(--color-text-900);
                                          --tw-ring-color: var(--color-accent-200);
                                          {{ !$editingProject ? 'cursor: not-allowed;' : '' }}">
                            @error('editName') <span class="text-xs mt-1" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Description</label>
                            <textarea wire:model="editDescription" {{ !$editingProject ? 'disabled' : '' }} rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                      style="border-color: var(--color-background-300);
                                             background-color: {{ $editingProject ? 'var(--color-background-50)' : 'var(--color-background-100)' }};
                                             color: var(--color-text-900);
                                             --tw-ring-color: var(--color-accent-200);
                                             {{ !$editingProject ? 'cursor: not-allowed;' : '' }}"></textarea>
                            @error('editDescription') <span class="text-xs mt-1" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Status</label>
                            <select wire:model="editStatus" {{ !$editingProject ? 'disabled' : '' }}
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300);
                                           background-color: {{ $editingProject ? 'var(--color-background-50)' : 'var(--color-background-100)' }};
                                           color: var(--color-text-900);
                                           --tw-ring-color: var(--color-accent-200);
                                           {{ !$editingProject ? 'cursor: not-allowed;' : '' }}">
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('editStatus') <span class="text-xs mt-1" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Domain</label>
                            <select wire:model="editDomain" {{ !$editingProject ? 'disabled' : '' }}
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300);
                                           background-color: {{ $editingProject ? 'var(--color-background-50)' : 'var(--color-background-100)' }};
                                           color: var(--color-text-900);
                                           --tw-ring-color: var(--color-accent-200);
                                           {{ !$editingProject ? 'cursor: not-allowed;' : '' }}">
                                <option value="healthcare">Healthcare</option>
                                <option value="finance">Finance</option>
                                <option value="education">Education</option>
                                <option value="retail">Retail</option>
                                <option value="manufacturing">Manufacturing</option>
                                <option value="technology">Technology</option>
                                <option value="other">Other</option>
                            </select>
                            @error('editDomain') <span class="text-xs mt-1" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Start Date</label>
                                <input type="date" wire:model="editStartDate" {{ !$editingProject ? 'disabled' : '' }}
                                       class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                       style="border-color: var(--color-background-300);
                                              background-color: {{ $editingProject ? 'var(--color-background-50)' : 'var(--color-background-100)' }};
                                              color: var(--color-text-900);
                                              --tw-ring-color: var(--color-accent-200);
                                              {{ !$editingProject ? 'cursor: not-allowed;' : '' }}">
                                @error('editStartDate') <span class="text-xs mt-1" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">End Date</label>
                                <input type="date" wire:model="editEndDate" {{ !$editingProject ? 'disabled' : '' }}
                                       class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                       style="border-color: var(--color-background-300);
                                              background-color: {{ $editingProject ? 'var(--color-background-50)' : 'var(--color-background-100)' }};
                                              color: var(--color-text-900);
                                              --tw-ring-color: var(--color-accent-200);
                                              {{ !$editingProject ? 'cursor: not-allowed;' : '' }}">
                                @error('editEndDate') <span class="text-xs mt-1" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if($editingProject)
                            <div class="flex items-center gap-3 pt-4">
                                <button type="button" wire:click="updateProject"
                                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                                        style="background-color: var(--color-glaucous); color: white;"
                                        onmouseover="this.style.opacity='0.9';"
                                        onmouseout="this.style.opacity='1';">
                                    <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Changes
                                </button>
                                <button type="button" wire:click="cancelEditingProject"
                                        class="px-4 py-2 rounded-lg border text-sm font-medium transition-all"
                                        style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-700);"
                                        onmouseover="this.style.backgroundColor='var(--color-background-200)';"
                                        onmouseout="this.style.backgroundColor='var(--color-background-50)';">
                                    Cancel
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Danger Zone --}}
                    <div class="pt-6" style="border-top: 1px solid var(--color-background-300);">
                        <h4 class="text-sm font-semibold mb-4" style="color: var(--color-bittersweet);">Danger Zone</h4>
                        <button type="button"
                                wire:click="deleteProject"
                                wire:confirm="Are you sure you want to delete this project? This action cannot be undone."
                                class="px-4 py-2 rounded-lg border text-sm font-medium transition-all"
                                style="border-color: var(--color-bittersweet); color: var(--color-bittersweet); background-color: transparent;"
                                onmouseover="this.style.backgroundColor='var(--color-bittersweet)'; this.style.color='white';"
                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--color-bittersweet)';">
                            Delete Project
                        </button>
                    </div>
                </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Task Creation/Edit Modal --}}
    @if($showTaskModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
            <div class="bg-white dark:bg-[var(--color-background-50)] rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                 @click.stop
                 x-data
                 x-init="$el.focus()"
                 @keydown.escape.window="$wire.closeTaskModal()">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between p-6 border-b" style="border-color: var(--color-background-300);">
                    <h2 class="text-xl font-bold" style="color: var(--color-text-900);">
                        {{ $editingTaskId ? 'Edit Task' : 'Create New Task' }}
                    </h2>
                    <button
                        type="button"
                        wire:click="closeTaskModal"
                        class="p-2 rounded-lg transition-colors"
                        style="color: var(--color-text-500);"
                        onmouseover="this.style.backgroundColor='var(--color-background-200)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-4">
                    {{-- Task Name --}}
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">
                            Task Name <span style="color: var(--color-bittersweet);">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="taskName"
                            placeholder="Enter task name..."
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                            style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                        />
                        @error('taskName') <span class="text-xs" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                    </div>

                    {{-- Task Description --}}
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Description</label>
                        <textarea
                            wire:model="taskDescription"
                            rows="3"
                            placeholder="Enter task description..."
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                            style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                        ></textarea>
                        @error('taskDescription') <span class="text-xs" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Task Type --}}
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">
                                Task Type <span style="color: var(--color-bittersweet);">*</span>
                            </label>
                            <select
                                wire:model="taskType"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            >
                                <option value="human">Human Task</option>
                                <option value="ai">AI Task</option>
                                <option value="hitl">HITL Task</option>
                            </select>
                            @error('taskType') <span class="text-xs" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>

                        {{-- Task Status --}}
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">
                                Status <span style="color: var(--color-bittersweet);">*</span>
                            </label>
                            <select
                                wire:model="taskStatus"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            >
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            @error('taskStatus') <span class="text-xs" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>

                        {{-- Due Date --}}
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Due Date</label>
                            <input
                                type="date"
                                wire:model="taskDueDate"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            />
                            @error('taskDueDate') <span class="text-xs" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>

                        {{-- Estimated Hours --}}
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Estimated Hours</label>
                            <input
                                type="number"
                                wire:model="taskEstimatedHours"
                                step="0.5"
                                min="0.5"
                                placeholder="e.g., 8"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            />
                            @error('taskEstimatedHours') <span class="text-xs" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end gap-3 p-6 border-t" style="border-color: var(--color-background-300);">
                    <button
                        type="button"
                        wire:click="closeTaskModal"
                        class="px-4 py-2 rounded-lg border transition-colors font-medium"
                        style="border-color: var(--color-background-300); color: var(--color-text-700); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--color-background-100)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="saveTask"
                        class="px-6 py-2 rounded-lg transition-opacity text-white font-medium"
                        style="background-color: var(--color-glaucous);"
                        onmouseover="this.style.opacity='0.9'"
                        onmouseout="this.style.opacity='1'"
                    >
                        {{ $editingTaskId ? 'Update Task' : 'Create Task' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
