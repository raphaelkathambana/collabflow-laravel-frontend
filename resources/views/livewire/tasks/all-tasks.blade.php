<div class="space-y-8">
    {{-- Success/Error Message --}}
    @if(session()->has('message'))
        <div class="p-4 rounded-lg border" style="background-color: rgba(196,214,176,0.2); border-color: var(--color-tea-green); color: var(--color-tea-green);">
            {{ session('message') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-4 rounded-lg border" style="background-color: rgba(235,94,85,0.1); border-color: var(--color-bittersweet); color: var(--color-bittersweet);">
            {{ session('error') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 style="font-family: Tahoma; color: var(--color-text-900);">Tasks</h1>
            <p class="mt-2" style="color: var(--color-text-500);">View and manage all tasks across your projects</p>
        </div>
        <div class="flex gap-3">
            @if($bulkEditMode && count($selectedTasks) > 0)
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg border" style="border-color: var(--color-accent-200); background-color: var(--color-accent-50); color: var(--color-glaucous);">
                    <span class="text-sm font-medium">{{ count($selectedTasks) }} selected</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="bulkUpdateStatus('completed')"
                            class="px-3 py-2 text-sm rounded-lg border transition-all"
                            style="border-color: var(--color-tea-green); background-color: rgba(196,214,176,0.2); color: var(--color-tea-green);"
                            onmouseover="this.style.opacity='0.8';"
                            onmouseout="this.style.opacity='1';">
                        Mark Complete
                    </button>
                    <button type="button" wire:click="bulkUpdateStatus('in_progress')"
                            class="px-3 py-2 text-sm rounded-lg border transition-all"
                            style="border-color: var(--color-glaucous); background-color: var(--color-accent-50); color: var(--color-glaucous);"
                            onmouseover="this.style.opacity='0.8';"
                            onmouseout="this.style.opacity='1';">
                        In Progress
                    </button>
                    <button type="button" wire:click="bulkDelete" wire:confirm="Delete {{ count($selectedTasks) }} tasks?"
                            class="px-3 py-2 text-sm rounded-lg border transition-all"
                            style="border-color: var(--color-bittersweet); background-color: rgba(235,94,85,0.1); color: var(--color-bittersweet);"
                            onmouseover="this.style.opacity='0.8';"
                            onmouseout="this.style.opacity='1';">
                        Delete
                    </button>
                </div>
            @endif

            {{-- Bulk Edit Mode Toggle --}}
            <button type="button" wire:click="toggleBulkEditMode"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-all"
                    style="border-color: {{ $bulkEditMode ? 'var(--color-glaucous)' : 'var(--color-background-300)' }};
                           background-color: {{ $bulkEditMode ? 'var(--color-accent-50)' : 'transparent' }};
                           color: {{ $bulkEditMode ? 'var(--color-glaucous)' : 'var(--color-text-700)' }};"
                    onmouseover="this.style.backgroundColor='{{ $bulkEditMode ? 'var(--color-accent-100)' : 'var(--color-background-100)' }}';"
                    onmouseout="this.style.backgroundColor='{{ $bulkEditMode ? 'var(--color-accent-50)' : 'transparent' }}';">
                @if($bulkEditMode)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Exit Bulk Edit
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Bulk Edit
                @endif
            </button>

            <button type="button" wire:click="openCreateTaskModal"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg transition-all"
                    style="background-color: var(--color-bittersweet); color: white;"
                    onmouseover="this.style.opacity='0.9';"
                    onmouseout="this.style.opacity='1';">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Task
            </button>
        </div>
    </div>

    {{-- Filters Toolbar --}}
    <div class="flex gap-4 items-center p-4" style="background-color: var(--color-background-100); border: 1px solid var(--color-background-300); border-radius: var(--radius-lg);">
        <div class="flex-1 relative">
            <svg class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
                 style="color: var(--color-text-400);"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search tasks..."
                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                   style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
        </div>

        <select wire:model.live="typeFilter" class="px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
            <option value="all">All Types</option>
            <option value="ai">AI Tasks</option>
            <option value="human">Human Tasks</option>
            <option value="hitl">HITL Tasks</option>
        </select>

        <select wire:model.live="statusFilter" class="px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
            <option value="all">All Status</option>
            <option value="pending">To Do</option>
            <option value="in_progress">In Progress</option>
            <option value="blocked">Blocked</option>
            <option value="completed">Completed</option>
        </select>

        <select wire:model.live="projectFilter" class="px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
            <option value="all">All Projects</option>
            @foreach($userProjects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="border p-4" style="border-radius: var(--radius-lg); border-color: var(--color-background-300); background-color: var(--color-background-50);">
            <div class="text-sm" style="color: var(--color-text-600);">To Do</div>
            <div class="mt-1 text-2xl font-bold" style="font-family: Tahoma; color: var(--color-text-800);">{{ $taskStats['todo'] }}</div>
        </div>
        <div class="border p-4" style="border-radius: var(--radius-lg); border-color: var(--color-accent-200); background-color: var(--color-accent-50);">
            <div class="text-sm" style="color: var(--color-glaucous);">In Progress</div>
            <div class="mt-1 text-2xl font-bold" style="font-family: Tahoma; color: var(--color-glaucous);">{{ $taskStats['in_progress'] }}</div>
        </div>
        <div class="border p-4" style="border-radius: var(--radius-lg); border-color: var(--color-bittersweet); background-color: rgba(235,94,85,0.05);">
            <div class="text-sm" style="color: var(--color-bittersweet);">Blocked</div>
            <div class="mt-1 text-2xl font-bold" style="font-family: Tahoma; color: var(--color-bittersweet);">{{ $taskStats['blocked'] }}</div>
        </div>
        <div class="border p-4" style="border-radius: var(--radius-lg); border-color: var(--color-tea-green); background-color: rgba(196,214,176,0.2);">
            <div class="text-sm" style="color: var(--color-tea-green);">Completed</div>
            <div class="mt-1 text-2xl font-bold" style="font-family: Tahoma; color: var(--color-tea-green);">{{ $taskStats['completed'] }}</div>
        </div>
    </div>

    {{-- Sorting Bar --}}
    <div class="flex items-center gap-4 p-3 border" style="border-radius: var(--radius-lg); border-color: var(--color-background-300); background-color: var(--color-background-50);">
        @if($bulkEditMode)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded" style="accent-color: var(--color-glaucous);">
                <span class="text-sm" style="color: var(--color-text-700);">Select All</span>
            </label>
            <div class="h-4 w-px" style="background-color: var(--color-background-300);"></div>
        @endif
        <div class="flex gap-2 text-sm">
            <button wire:click="sortBy('name')" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-100 transition-colors" style="color: var(--color-text-700);">
                Name
                @if($sortBy === 'name')
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($sortDirection === 'asc')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        @endif
                    </svg>
                @endif
            </button>
            <button wire:click="sortBy('status')" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-100 transition-colors" style="color: var(--color-text-700);">
                Status
                @if($sortBy === 'status')
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($sortDirection === 'asc')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        @endif
                    </svg>
                @endif
            </button>
            <button wire:click="sortBy('due_date')" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-100 transition-colors" style="color: var(--color-text-700);">
                Due Date
                @if($sortBy === 'due_date')
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($sortDirection === 'asc')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        @endif
                    </svg>
                @endif
            </button>
            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-100 transition-colors" style="color: var(--color-text-700);">
                Created
                @if($sortBy === 'created_at')
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($sortDirection === 'asc')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        @endif
                    </svg>
                @endif
            </button>
        </div>
    </div>

    {{-- Task List --}}
    <div class="space-y-2">
        @forelse($tasks as $task)
            <div class="group border p-4 transition-all duration-200"
                 style="border-radius: var(--radius-lg);
                        background-color: var(--color-background-50);
                        border-color: {{ in_array($task->id, $selectedTasks) ? 'var(--color-glaucous)' : ($task->status === 'completed' ? 'var(--color-tea-green)' : ($task->status === 'blocked' ? 'var(--color-bittersweet)' : 'var(--color-background-300)')) }};"
                 onmouseover="this.style.boxShadow='var(--shadow-md)';"
                 onmouseout="this.style.boxShadow='';">
                <div class="flex items-start gap-4">
                    {{-- Selection Checkbox (only in bulk edit mode) --}}
                    @if($bulkEditMode)
                        <input type="checkbox" wire:model.live="selectedTasks" value="{{ $task->id }}"
                               class="mt-1 w-5 h-5 rounded transition-all cursor-pointer"
                               style="accent-color: var(--color-glaucous);">
                    @endif

                    {{-- Task Content --}}
                    <div class="flex-1 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h6 class="text-sm font-semibold {{ $task->status === 'completed' ? 'line-through' : '' }} cursor-pointer"
                                        wire:click="viewTaskDetail('{{ $task->id }}')"
                                        style="color: {{ $task->status === 'completed' ? 'var(--color-text-500)' : 'var(--color-text-800)' }};">
                                        {{ $task->name }}
                                    </h6>

                                    {{-- Type Badge --}}
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
                                              $task->status === 'generated' ? 'rgba(255,159,28,0.15)' :
                                              ($task->status === 'completed' ? 'rgba(196,214,176,0.2)' :
                                              ($task->status === 'in_progress' ? 'rgba(92,128,188,0.15)' :
                                              ($task->status === 'blocked' ? 'rgba(235,94,85,0.1)' : 'var(--color-background-200)')))
                                          }};
                                          color: {{
                                              $task->status === 'generated' ? 'var(--color-orange-peel)' :
                                              ($task->status === 'completed' ? 'var(--color-tea-green)' :
                                              ($task->status === 'in_progress' ? 'var(--color-glaucous)' :
                                              ($task->status === 'blocked' ? 'var(--color-bittersweet)' : 'var(--color-text-700)')))
                                          }};">
                                        {{ $task->status === 'generated' ? '🤖 Generated' : ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </div>

                                {{-- Project Link --}}
                                <a href="{{ route('projects.show', $task->project->id) }}"
                                   class="inline-flex items-center gap-1.5 text-xs hover:underline"
                                   style="color: var(--color-glaucous);">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                    {{ $task->project->name }}
                                </a>
                            </div>

                            {{-- Actions Menu --}}
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
                                <button type="button" wire:click="openEditTaskModal('{{ $task->id }}')"
                                        class="p-2 rounded-lg transition-all"
                                        style="color: var(--color-text-500); background-color: transparent;"
                                        onmouseover="this.style.color='var(--color-glaucous)'; this.style.backgroundColor='var(--color-accent-50)';"
                                        onmouseout="this.style.color='var(--color-text-500)'; this.style.backgroundColor='transparent';"
                                        title="Edit task">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>

                                {{-- Delete Button --}}
                                <button type="button" wire:click="confirmDelete('{{ $task->id }}')"
                                        class="p-2 rounded-lg transition-all"
                                        style="color: var(--color-text-500); background-color: transparent;"
                                        onmouseover="this.style.color='var(--color-bittersweet)'; this.style.backgroundColor='rgba(235,94,85,0.1)';"
                                        onmouseout="this.style.color='var(--color-text-500)'; this.style.backgroundColor='transparent';"
                                        title="Delete task">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Due Date and Hours Info --}}
                        @if($task->due_date || $task->estimated_hours)
                            <div class="flex items-center gap-4 text-xs" style="color: var(--color-text-500);">
                                @if($task->due_date)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ $task->due_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                                @if($task->estimated_hours)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ $task->estimated_hours }}h</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 px-4 border" style="border-radius: var(--radius-lg); background-color: var(--color-background-50); border-color: var(--color-background-300);">
                <svg class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <h3 class="text-lg font-semibold mb-2" style="color: var(--color-text-700);">No tasks found</h3>
                <p class="text-sm" style="color: var(--color-text-500);">
                    @if($search || $typeFilter !== 'all' || $statusFilter !== 'all' || $projectFilter !== 'all')
                        Try adjusting your filters to see more tasks.
                    @else
                        Get started by creating your first task.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Task Create/Edit Modal --}}
    @if($showTaskModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
            <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg border"
                 style="background-color: var(--color-background-50); border-color: var(--color-background-300); box-shadow: var(--shadow-xl);">

                {{-- Modal Header --}}
                <div class="sticky top-0 flex items-center justify-between p-6 border-b"
                     style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                    <h2 class="text-xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">
                        {{ $editingTaskId ? 'Edit Task' : 'Create New Task' }}
                    </h2>
                    <button type="button" wire:click="closeTaskModal" class="p-2 rounded-lg transition-all"
                            style="color: var(--color-text-500);"
                            onmouseover="this.style.backgroundColor='var(--color-background-200)';"
                            onmouseout="this.style.backgroundColor='transparent';">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-4">
                    {{-- Task Name --}}
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Task Name *</label>
                        <input type="text" wire:model="taskName" placeholder="Enter task name..."
                               class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                               style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                        @error('taskName') <p class="mt-1 text-sm" style="color: var(--color-bittersweet);">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Description</label>
                        <textarea wire:model="taskDescription" rows="3" placeholder="Enter task description..."
                                  class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2 resize-none"
                                  style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);"></textarea>
                        @error('taskDescription') <p class="mt-1 text-sm" style="color: var(--color-bittersweet);">{{ $message }}</p> @enderror
                    </div>

                    {{-- Project Selection --}}
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Project *</label>
                        <select wire:model="taskProjectId"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                            <option value="">Select a project</option>
                            @foreach($userProjects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('taskProjectId') <p class="mt-1 text-sm" style="color: var(--color-bittersweet);">{{ $message }}</p> @enderror
                    </div>

                    {{-- Type and Status --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Task Type *</label>
                            <select wire:model="taskType"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                                <option value="human">Human Task</option>
                                <option value="ai">AI Task</option>
                                <option value="hitl">HITL Task</option>
                            </select>
                            @error('taskType') <p class="mt-1 text-sm" style="color: var(--color-bittersweet);">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Status *</label>
                            <select wire:model="taskStatus"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="blocked">Blocked</option>
                                <option value="completed">Completed</option>
                            </select>
                            @error('taskStatus') <p class="mt-1 text-sm" style="color: var(--color-bittersweet);">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Due Date and Estimated Hours --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Due Date</label>
                            <input type="date" wire:model="taskDueDate"
                                   class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                   style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                            @error('taskDueDate') <p class="mt-1 text-sm" style="color: var(--color-bittersweet);">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Estimated Hours</label>
                            <input type="number" wire:model="taskEstimatedHours" step="0.5" min="0.5" placeholder="Hours"
                                   class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                   style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-200);">
                            @error('taskEstimatedHours') <p class="mt-1 text-sm" style="color: var(--color-bittersweet);">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="sticky bottom-0 flex items-center justify-end gap-3 p-6 border-t"
                     style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                    <button type="button" wire:click="closeTaskModal"
                            class="px-4 py-2 rounded-lg border transition-all"
                            style="border-color: var(--color-background-300); background-color: transparent; color: var(--color-text-700);"
                            onmouseover="this.style.backgroundColor='var(--color-background-100)';"
                            onmouseout="this.style.backgroundColor='transparent';">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveTask"
                            class="px-4 py-2 rounded-lg transition-all"
                            style="background-color: var(--color-bittersweet); color: white;"
                            onmouseover="this.style.opacity='0.9';"
                            onmouseout="this.style.opacity='1';">
                        {{ $editingTaskId ? 'Update Task' : 'Create Task' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Task Detail Modal --}}
    @if($showDetailModal && $detailTask)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
            <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-lg border"
                 style="background-color: var(--color-background-50); border-color: var(--color-background-300); box-shadow: var(--shadow-xl);">

                {{-- Modal Header --}}
                <div class="flex items-start justify-between p-6 border-b"
                     style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">
                                {{ $detailTask->name }}
                            </h2>
                            {{-- Type Badge --}}
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md border"
                                  style="background-color: {{ $detailTask->type === 'ai' ? 'var(--color-accent-100)' : ($detailTask->type === 'human' ? 'rgba(196,214,176,0.2)' : 'rgba(255,159,28,0.15)') }};
                                         color: {{ $detailTask->type === 'ai' ? 'var(--color-accent-700)' : ($detailTask->type === 'human' ? '#316837' : 'var(--color-orange-peel)') }};
                                         border-color: {{ $detailTask->type === 'ai' ? 'var(--color-accent-200)' : ($detailTask->type === 'human' ? 'rgba(196,214,176,0.4)' : 'rgba(255,159,28,0.3)') }};">
                                {{ strtoupper($detailTask->type) }}
                            </span>
                            {{-- Status Badge --}}
                            <span class="px-2.5 py-1 text-xs font-medium rounded-md"
                                  style="background-color: {{
                                      $detailTask->status === 'completed' ? 'rgba(196,214,176,0.2)' :
                                      ($detailTask->status === 'in_progress' ? 'rgba(92,128,188,0.15)' :
                                      ($detailTask->status === 'blocked' ? 'rgba(235,94,85,0.1)' : 'var(--color-background-200)'))
                                  }};
                                  color: {{
                                      $detailTask->status === 'completed' ? 'var(--color-tea-green)' :
                                      ($detailTask->status === 'in_progress' ? 'var(--color-glaucous)' :
                                      ($detailTask->status === 'blocked' ? 'var(--color-bittersweet)' : 'var(--color-text-700)'))
                                  }};">
                                {{ ucfirst(str_replace('_', ' ', $detailTask->status)) }}
                            </span>
                        </div>
                        <a href="{{ route('projects.show', $detailTask->project->id) }}"
                           class="inline-flex items-center gap-1.5 text-sm hover:underline"
                           style="color: var(--color-glaucous);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            {{ $detailTask->project->name }}
                        </a>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="p-2 rounded-lg transition-all"
                            style="color: var(--color-text-500);"
                            onmouseover="this.style.backgroundColor='var(--color-background-200)';"
                            onmouseout="this.style.backgroundColor='transparent';">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-6">
                    {{-- Description --}}
                    @if($detailTask->description)
                        <div>
                            <h3 class="text-sm font-semibold mb-2" style="color: var(--color-text-700);">Description</h3>
                            <p class="text-sm leading-relaxed" style="color: var(--color-text-600);">{{ $detailTask->description }}</p>
                        </div>
                    @endif

                    {{-- Details Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        @if($detailTask->due_date)
                            <div class="p-4 rounded-lg border" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
                                <div class="text-xs font-medium mb-1" style="color: var(--color-text-500);">Due Date</div>
                                <div class="flex items-center gap-2" style="color: var(--color-text-800);">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">{{ $detailTask->due_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        @endif

                        @if($detailTask->estimated_hours)
                            <div class="p-4 rounded-lg border" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
                                <div class="text-xs font-medium mb-1" style="color: var(--color-text-500);">Estimated Hours</div>
                                <div class="flex items-center gap-2" style="color: var(--color-text-800);">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">{{ $detailTask->estimated_hours }} hours</span>
                                </div>
                            </div>
                        @endif

                        <div class="p-4 rounded-lg border" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
                            <div class="text-xs font-medium mb-1" style="color: var(--color-text-500);">Created</div>
                            <div class="text-sm font-medium" style="color: var(--color-text-800);">{{ $detailTask->created_at->format('M d, Y g:i A') }}</div>
                        </div>

                        <div class="p-4 rounded-lg border" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
                            <div class="text-xs font-medium mb-1" style="color: var(--color-text-500);">Last Updated</div>
                            <div class="text-sm font-medium" style="color: var(--color-text-800);">{{ $detailTask->updated_at->format('M d, Y g:i A') }}</div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div>
                        <h3 class="text-sm font-semibold mb-3" style="color: var(--color-text-700);">Quick Actions</h3>
                        <div class="flex gap-2 flex-wrap">
                            @if($detailTask->status === 'pending')
                                <button type="button" wire:click="updateTaskStatus('{{ $detailTask->id }}', 'in_progress')"
                                        class="px-3 py-2 text-sm rounded-lg border transition-all"
                                        style="border-color: var(--color-glaucous); background-color: var(--color-accent-50); color: var(--color-glaucous);"
                                        onmouseover="this.style.opacity='0.8';"
                                        onmouseout="this.style.opacity='1';">
                                    Start Task
                                </button>
                            @endif
                            @if($detailTask->status === 'in_progress')
                                <button type="button" wire:click="updateTaskStatus('{{ $detailTask->id }}', 'completed')"
                                        class="px-3 py-2 text-sm rounded-lg border transition-all"
                                        style="border-color: var(--color-tea-green); background-color: rgba(196,214,176,0.2); color: var(--color-tea-green);"
                                        onmouseover="this.style.opacity='0.8';"
                                        onmouseout="this.style.opacity='1';">
                                    Mark Complete
                                </button>
                                <button type="button" wire:click="updateTaskStatus('{{ $detailTask->id }}', 'blocked')"
                                        class="px-3 py-2 text-sm rounded-lg border transition-all"
                                        style="border-color: var(--color-bittersweet); background-color: rgba(235,94,85,0.1); color: var(--color-bittersweet);"
                                        onmouseover="this.style.opacity='0.8';"
                                        onmouseout="this.style.opacity='1';">
                                    Mark Blocked
                                </button>
                            @endif
                            <button type="button" wire:click="openEditTaskModal('{{ $detailTask->id }}')"
                                    class="px-3 py-2 text-sm rounded-lg border transition-all"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-100); color: var(--color-text-700);"
                                    onmouseover="this.style.backgroundColor='var(--color-background-200)';"
                                    onmouseout="this.style.backgroundColor='var(--color-background-100)';">
                                Edit Task
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
            <div class="w-full max-w-md rounded-lg border"
                 style="background-color: var(--color-background-50); border-color: var(--color-background-300); box-shadow: var(--shadow-xl);">

                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 rounded-full" style="background-color: rgba(235,94,85,0.1);">
                            <svg class="w-6 h-6" style="color: var(--color-bittersweet);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold" style="color: var(--color-text-900);">Delete Task</h3>
                            <p class="text-sm mt-1" style="color: var(--color-text-600);">Are you sure you want to delete this task? This action cannot be undone.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button" wire:click="cancelDelete"
                                class="px-4 py-2 rounded-lg border transition-all"
                                style="border-color: var(--color-background-300); background-color: transparent; color: var(--color-text-700);"
                                onmouseover="this.style.backgroundColor='var(--color-background-100)';"
                                onmouseout="this.style.backgroundColor='transparent';">
                            Cancel
                        </button>
                        <button type="button" wire:click="deleteTask"
                                class="px-4 py-2 rounded-lg transition-all"
                                style="background-color: var(--color-bittersweet); color: white;"
                                onmouseover="this.style.opacity='0.9';"
                                onmouseout="this.style.opacity='1';">
                            Delete Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
