<div class="border rounded-xl p-6" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
    <h3 class="text-lg font-bold mb-4" style="font-family: Tahoma; color: var(--color-text-800);">
        Upcoming Tasks
    </h3>

    <div class="space-y-3">
        @foreach($tasks as $task)
            @php
                $daysUntil = $this->getDaysUntil($task['dueDate']);
                $isOverdue = $daysUntil === 'Overdue';
                $isToday = $daysUntil === 'Today';

                $taskTypeColors = [
                    'ai' => 'var(--color-glaucous)',
                    'human' => 'var(--color-tea-green)',
                    'hitl' => 'var(--color-orange-peel)',
                ];

                $priorityColors = [
                    'high' => 'var(--color-bittersweet)',
                    'medium' => 'var(--color-orange-peel)',
                    'low' => 'var(--color-text-500)',
                ];
            @endphp
            <div
                class="p-4 border rounded-lg transition-all cursor-pointer
                    {{ $isOverdue ? '' : ($isToday ? '' : 'hover:border-[var(--color-glaucous)] hover:shadow-md') }}"
                style="
                    {{ $isOverdue ? 'border-color: var(--color-bittersweet); background-color: var(--color-primary-50);' : '' }}
                    {{ $isToday && !$isOverdue ? 'border-color: var(--color-orange-peel); background-color: var(--color-secondary-50);' : '' }}
                    {{ !$isOverdue && !$isToday ? 'border-color: var(--color-background-300);' : '' }}
                "
            >
                {{-- Task header --}}
                <div class="flex items-start justify-between gap-2 mb-2">
                    <h4 class="font-semibold text-sm flex-1" style="color: var(--color-text-800);">{{ $task['title'] }}</h4>
                    <span class="px-2 py-0.5 text-xs font-medium rounded text-white" style="background-color: {{ $taskTypeColors[$task['type']] }};">
                        {{ strtoupper($task['type']) }}
                    </span>
                </div>

                {{-- Project --}}
                <p class="text-xs mb-3" style="color: var(--color-text-500);">{{ $task['project'] }}</p>

                {{-- Meta information --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <svg class="h-3 w-3" style="color: var(--color-text-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium" style="color: {{ $isOverdue ? 'var(--color-bittersweet)' : 'var(--color-text-700)' }};">{{ $daysUntil }}</span>
                        <span style="color: var(--color-text-500);">at {{ $task['dueTime'] }}</span>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <svg class="h-3 w-3" style="color: var(--color-text-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span style="color: var(--color-text-600);">{{ $task['assignee'] }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium capitalize" style="color: {{ $priorityColors[$task['priority']] }};">
                            {{ $task['priority'] }} priority
                        </span>
                    </div>
                </div>
            </div>
        @endforeach

        @if(count($tasks) === 0)
            <x-empty-state
                title="No Upcoming Tasks"
                description="All tasks matching your filters are complete or no tasks are scheduled."
            />
        @endif
    </div>
</div>
