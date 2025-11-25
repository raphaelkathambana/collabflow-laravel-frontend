@php
    $task = App\Models\Task::with(['project', 'assignee', 'reviewer'])->find($id);
    if (!$task) {
        abort(404, 'Task not found');
    }
@endphp

<x-collabflow.layout title="{{ $task->name }} - Task Details">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm mb-2" style="color: var(--color-text-600);">
                <a href="{{ route('tasks.index') }}" wire:navigate class="hover:underline">All Tasks</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('projects.show', $task->project_id) }}" wire:navigate class="hover:underline">
                    {{ $task->project->name }}
                </a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span>{{ $task->name }}</span>
            </div>
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-text-900);">
                        {{ $task->name }}
                    </h1>
                    @if($task->description)
                        <p class="text-base" style="color: var(--color-text-600);">
                            {{ $task->description }}
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    {{-- Type Badge --}}
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                        @if($task->type === 'ai') bg-blue-100 text-blue-800
                        @elseif($task->type === 'human') bg-green-100 text-green-800
                        @else bg-orange-100 text-orange-800
                        @endif
                    ">
                        {{ strtoupper($task->type) }}
                    </span>

                    {{-- Status Badge --}}
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                        @if($task->status === 'completed') bg-green-100 text-green-800
                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                        @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                        @elseif($task->status === 'blocked') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif
                    ">
                        {{ ucfirst($task->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Task Metadata Cards --}}
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs text-gray-500 mb-1">Assigned To</div>
                <div class="text-sm font-medium text-gray-900">
                    {{ $task->assignee->name ?? 'Unassigned' }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs text-gray-500 mb-1">Due Date</div>
                <div class="text-sm font-medium text-gray-900">
                    {{ $task->due_date ? $task->due_date->format('M d, Y') : 'Not set' }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs text-gray-500 mb-1">Estimated Hours</div>
                <div class="text-sm font-medium text-gray-900">
                    {{ $task->estimated_hours ?? 'Not set' }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs text-gray-500 mb-1">Complexity</div>
                <div class="text-sm font-medium text-gray-900">
                    {{ $task->complexity ? ucfirst(strtolower($task->complexity)) : 'Not set' }}
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="mb-6" x-data="{ activeTab: 'overview' }">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'overview'"
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Overview
                    </button>

                    @if($task->output)
                        <button
                            @click="activeTab = 'work'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'work', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'work' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                        >
                            AI Work
                        </button>
                    @endif

                    <button
                        @click="activeTab = 'subtasks'"
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'subtasks', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'subtasks' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Subtasks
                        @if(count($task->getSubtasks()) > 0)
                            <span class="ml-2 bg-gray-200 text-gray-700 py-0.5 px-2 rounded-full text-xs">
                                {{ count($task->getSubtasks()) }}
                            </span>
                        @endif
                    </button>

                    @if($task->type === 'hitl' || $task->status === 'review' || $task->output)
                        <button
                            @click="activeTab = 'review'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'review', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'review' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                        >
                            Review
                        </button>
                    @endif

                    <button
                        @click="activeTab = 'activity'"
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'activity', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'activity' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Activity
                    </button>
                </nav>
            </div>

            {{-- Tab Panels --}}
            <div class="mt-6">
                {{-- Overview Tab --}}
                <div x-show="activeTab === 'overview'" x-cloak>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Information</h3>

                        <dl class="grid grid-cols-2 gap-4">
                            @if($task->required_skills && count($task->required_skills) > 0)
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Required Skills</dt>
                                    <dd class="mt-1 flex flex-wrap gap-2">
                                        @foreach($task->required_skills as $skill)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $skill }}
                                            </span>
                                        @endforeach
                                    </dd>
                                </div>
                            @endif

                            @if($task->dependencies && count($task->dependencies) > 0)
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Dependencies</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <ul class="list-disc list-inside">
                                            @foreach($task->dependencies as $dependency)
                                                <li>{{ $dependency }}</li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                </div>
                            @endif

                            @if($task->deliverables && count($task->deliverables) > 0)
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Deliverables</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <ul class="list-disc list-inside">
                                            @foreach($task->deliverables as $deliverable)
                                                <li>{{ $deliverable }}</li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                </div>
                            @endif

                            @if($task->ai_suitability_score)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">AI Suitability</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ round($task->ai_suitability_score * 100) }}%</dd>
                                </div>
                            @endif

                            @if($task->confidence_score)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Confidence Score</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ round($task->confidence_score * 100) }}%</dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $task->created_at->format('M d, Y H:i') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $task->updated_at->diffForHumans() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- AI Work Tab --}}
                @if($task->output)
                    <div x-show="activeTab === 'work'" x-cloak>
                        <livewire:tasks.task-work-display :task="$task" />
                    </div>
                @endif

                {{-- Subtasks Tab --}}
                <div x-show="activeTab === 'subtasks'" x-cloak>
                    <livewire:tasks.task-subtask-manager :task="$task" />
                </div>

                {{-- Review Tab --}}
                @if($task->type === 'hitl' || $task->status === 'review' || $task->output)
                    <div x-show="activeTab === 'review'" x-cloak>
                        <livewire:tasks.task-review-panel :task="$task" />
                    </div>
                @endif

                {{-- Activity Tab --}}
                <div x-show="activeTab === 'activity'" x-cloak>
                    <livewire:tasks.task-activity-timeline :task="$task" />
                </div>
            </div>
        </div>
    </div>
</x-collabflow.layout>
