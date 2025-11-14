@if($view === 'month')
    <div class="border rounded-xl p-6" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        {{-- Week day headers --}}
        <div class="grid grid-cols-7 gap-2 mb-4">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="text-center text-sm font-semibold py-2" style="color: var(--color-text-600);">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div class="grid grid-cols-7 gap-2">
            @foreach($days as $index => $date)
                @php
                    $tasksForDate = $this->getTasksForDate($date);
                    $isToday = $date && $date->isToday();
                @endphp
                <div
                    class="min-h-[100px] p-2 border rounded-lg transition-all cursor-pointer
                        {{ !$date ? 'cursor-default' : 'hover:border-[var(--color-glaucous)]' }}
                        {{ $isToday ? 'bg-[var(--color-accent-50)]' : '' }}"
                    style="
                        border-color: {{ $isToday ? 'var(--color-glaucous)' : 'var(--color-background-300)' }};
                        {{ !$date ? 'background-color: var(--color-background-100);' : '' }}
                    "
                >
                    @if($date)
                        <div class="text-sm font-semibold mb-2" style="color: {{ $isToday ? 'var(--color-glaucous)' : 'var(--color-text-700)' }};">
                            {{ $date->day }}
                        </div>
                        <div class="space-y-1">
                            @foreach(array_slice($tasksForDate, 0, 3) as $task)
                                <div
                                    class="text-xs px-2 py-1 rounded text-white truncate"
                                    style="background-color: {{ $this->getTaskColor($task['type']) }};"
                                    title="{{ $task['title'] }}"
                                >
                                    {{ $task['title'] }}
                                </div>
                            @endforeach
                            @if(count($tasksForDate) > 3)
                                <div class="text-xs px-2" style="color: var(--color-text-500);">
                                    +{{ count($tasksForDate) - 3 }} more
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

@elseif($view === 'week')
    <div class="border rounded-xl overflow-hidden" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        {{-- Week day headers --}}
        <div class="grid grid-cols-8 border-b" style="border-color: var(--color-background-300);">
            <div class="p-3" style="background-color: var(--color-background-100);"></div>
            @foreach($days as $day)
                @php
                    $isToday = $day->isToday();
                @endphp
                <div class="p-3 text-center" style="background-color: {{ $isToday ? 'var(--color-accent-50)' : 'var(--color-background-100)' }};">
                    <div class="text-xs font-semibold" style="color: var(--color-text-600);">{{ $day->format('D') }}</div>
                    <div class="text-lg font-bold {{ $isToday ? 'text-white rounded-full w-8 h-8 flex items-center justify-center mx-auto' : '' }}"
                         style="color: {{ $isToday ? 'white' : 'var(--color-text-800)' }}; {{ $isToday ? 'background-color: var(--color-glaucous);' : '' }}">
                        {{ $day->day }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Time slots grid --}}
        <div class="overflow-auto max-h-[600px]">
            @foreach(array_slice($hours, 6, 15) as $hour)
                <div class="grid grid-cols-8 border-b" style="border-color: var(--color-background-200);">
                    <div class="p-2 text-xs font-medium text-right pr-3" style="color: var(--color-text-500); background-color: var(--color-background-100);">
                        {{ \Carbon\Carbon::parse($hour)->format('g A') }}
                    </div>
                    @foreach($days as $day)
                        @php
                            $dayTasks = $this->getTasksForDate($day);
                        @endphp
                        <div class="p-2 min-h-[60px] border-l" style="border-color: var(--color-background-200);">
                            @foreach($dayTasks as $task)
                                @if(str_starts_with($task['time'], substr($hour, 0, 2)))
                                    <div class="text-xs px-2 py-1 rounded text-white mb-1" style="background-color: {{ $this->getTaskColor($task['type']) }};">
                                        <div class="font-semibold truncate">{{ $task['title'] }}</div>
                                        <div class="opacity-75">{{ $task['time'] }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

@elseif($view === 'day')
    <div class="border rounded-xl overflow-hidden" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        {{-- Day header --}}
        <div class="p-4 border-b" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
            <div class="text-sm font-semibold" style="color: var(--color-text-600);">{{ $selectedDay->format('l') }}</div>
            <div class="text-2xl font-bold" style="color: var(--color-text-900);">{{ $selectedDay->format('F d, Y') }}</div>
        </div>

        {{-- Time slots --}}
        <div class="overflow-auto max-h-[600px]">
            @foreach(array_slice($hours, 6, 15) as $hour)
                @php
                    $dayTasks = $this->getTasksForDate($selectedDay);
                @endphp
                <div class="flex border-b" style="border-color: var(--color-background-200);">
                    <div class="w-20 p-3 text-xs font-medium text-right" style="color: var(--color-text-500); background-color: var(--color-background-100);">
                        {{ \Carbon\Carbon::parse($hour)->format('g A') }}
                    </div>
                    <div class="flex-1 p-3 min-h-[80px]">
                        @foreach($dayTasks as $task)
                            @if(str_starts_with($task['time'], substr($hour, 0, 2)))
                                <div class="px-3 py-2 rounded-lg mb-2" style="background-color: {{ $this->getTaskColor($task['type']) }}; color: white;">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-semibold">{{ $task['title'] }}</span>
                                        <span class="text-xs opacity-75">{{ $task['time'] }}</span>
                                    </div>
                                    <div class="text-xs opacity-90">{{ $task['project'] }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@elseif($view === 'list')
    <div class="border rounded-xl" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        {{-- List header --}}
        <div class="p-4 border-b" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
            <h3 class="text-lg font-bold" style="color: var(--color-text-900);">All Tasks</h3>
            <p class="text-sm" style="color: var(--color-text-500);">Showing {{ count($tasks) }} tasks</p>
        </div>

        {{-- Task list --}}
        <div class="divide-y" style="border-color: var(--color-background-200);">
            @forelse($tasks as $task)
                @php
                    $matchesFilter = empty($filters['taskTypes']) || in_array($task['type'], $filters['taskTypes']);
                @endphp
                @if($matchesFilter)
                    <div class="p-4 hover:bg-[var(--color-background-100)] transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1">
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="background-color: {{ $this->getTaskColor($task['type']) }};">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold mb-1" style="color: var(--color-text-900);">{{ $task['title'] }}</h4>
                                    <p class="text-sm mb-2" style="color: var(--color-text-600);">{{ $task['project'] }}</p>
                                    <div class="flex items-center gap-3 text-xs" style="color: var(--color-text-500);">
                                        <div class="flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $task['date']->format('M d, Y') }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $task['time'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-1 rounded-full font-semibold text-white" style="background-color: {{ $this->getTaskColor($task['type']) }};">
                                    {{ strtoupper($task['type']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="p-12 text-center">
                    <svg class="h-12 w-12 mx-auto mb-3" style="color: var(--color-text-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="font-medium mb-1" style="color: var(--color-text-700);">No tasks scheduled</p>
                    <p class="text-sm" style="color: var(--color-text-500);">Create a project to get started</p>
                </div>
            @endforelse
        </div>
    </div>
@endif
