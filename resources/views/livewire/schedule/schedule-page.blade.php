<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-4xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-text-900);">Schedule</h1>
        <p style="color: var(--color-text-500);">View and manage your project timelines and task deadlines</p>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between p-4 border rounded-xl" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        {{-- Left: Date Navigation --}}
        <div class="flex items-center gap-3">
            <button wire:click="navigateDate('prev')" class="h-8 w-8 p-0 rounded-lg transition-colors hover:bg-[var(--color-background-200)]" style="color: var(--color-text-600);">
                <svg class="h-4 w-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <div class="flex items-center gap-2 min-w-[200px]">
                <svg class="h-4 w-4" style="color: var(--color-text-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-semibold" style="color: var(--color-text-800);">
                    @if($view === 'month')
                        {{ \Carbon\Carbon::parse($selectedDate)->format('F Y') }}
                    @elseif($view === 'week')
                        Week of {{ \Carbon\Carbon::parse($selectedDate)->format('M d') }}
                    @else
                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d') }}
                    @endif
                </span>
            </div>
            <button wire:click="navigateDate('next')" class="h-8 w-8 p-0 rounded-lg transition-colors hover:bg-[var(--color-background-200)]" style="color: var(--color-text-600);">
                <svg class="h-4 w-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <button wire:click="goToToday" class="text-xs px-3 py-1 rounded-lg border transition-colors" style="border-color: var(--color-background-300); color: var(--color-text-700);">
                Today
            </button>
        </div>

        {{-- Right: View Toggle & Filters --}}
        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="flex items-center gap-1 p-1 rounded-lg" style="background-color: var(--color-background-100);">
                @foreach(['month', 'week', 'day', 'timeline', 'list'] as $v)
                    <button
                        wire:click="changeView('{{ $v }}')"
                        class="px-3 py-1 text-xs font-medium rounded-md transition-all capitalize"
                        style="
                            {{ $view === $v
                                ? 'background-color: var(--color-background-50); color: var(--color-glaucous); box-shadow: 0 1px 2px rgba(0,0,0,0.05);'
                                : 'color: var(--color-text-600);'
                            }}
                        "
                    >
                        {{ $v }}
                    </button>
                @endforeach
            </div>

            {{-- Filters --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 px-3 py-1 text-xs border rounded-lg transition-colors" style="border-color: var(--color-background-300); background-color: transparent; color: var(--color-text-700);">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filters
                    @if(count($filters['taskTypes']) > 0)
                        <span class="h-5 w-5 rounded-full flex items-center justify-center text-xs" style="background-color: var(--color-background-200); color: var(--color-text-700);">
                            {{ count($filters['taskTypes']) }}
                        </span>
                    @endif
                </button>

                <div
                    x-show="open"
                    x-transition
                    @click.outside="open = false"
                    class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg border z-10"
                    style="background-color: var(--color-background-50); border-color: var(--color-background-300);"
                >
                    <div class="p-2">
                        <div class="px-2 py-1.5 text-xs font-semibold" style="color: var(--color-text-700);">Task Type</div>
                        @foreach([['ai', 'glaucous', 'AI Tasks'], ['human', 'tea-green', 'Human Tasks'], ['hitl', 'orange-peel', 'HITL Tasks']] as [$type, $color, $label])
                            <button
                                wire:click="toggleTaskTypeFilter('{{ $type }}')"
                                @click.stop
                                class="w-full flex items-center gap-2 px-2 py-2 text-sm rounded hover:bg-[var(--color-background-100)] transition-colors"
                                style="color: var(--color-text-700);"
                            >
                                <div class="h-4 w-4 border rounded flex items-center justify-center" style="border-color: var(--color-background-300);">
                                    @if(in_array($type, $filters['taskTypes']))
                                        <svg class="h-3 w-3" style="color: var(--color-{{ $color }});" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </div>
                                <span class="h-2 w-2 rounded-full" style="background-color: var(--color-{{ $color }});"></span>
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Calendar/Timeline View --}}
        <div class="lg:col-span-2">
            @if($view === 'timeline')
                <livewire:schedule.timeline-view
                    :selectedDate="$selectedDate"
                    :filters="$filters"
                    :key="'timeline-'.$selectedDate.'-'.json_encode($filters['taskTypes'])"
                />
            @else
                <livewire:schedule.calendar-view
                    :view="$view"
                    :selectedDate="$selectedDate"
                    :filters="$filters"
                    :key="'calendar-'.$view.'-'.$selectedDate.'-'.json_encode($filters['taskTypes'])"
                />
            @endif
        </div>

        {{-- Upcoming Tasks Sidebar --}}
        <div class="lg:col-span-1">
            <livewire:schedule.upcoming-tasks
                :filters="$filters"
                :key="'upcoming-'.json_encode($filters['taskTypes'])"
            />
        </div>
    </div>
</div>
