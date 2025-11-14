<div
    x-data="{
        open: @entangle('isOpen'),
        activeTab: @entangle('activeTab')
    }"
    x-show="open"
    x-cloak
    @keydown.escape.window="$wire.close()"
    class="fixed inset-0 z-50 flex items-start justify-center pt-[10vh]"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$wire.close()"
        class="absolute inset-0"
        style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);"
    ></div>

    {{-- Command Palette Dialog --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="relative w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden"
        style="background-color: var(--color-background-50); border: 1px solid var(--color-background-300);"
        @click.stop
    >
        {{-- Search Input --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b" style="border-color: var(--color-background-300);">
            <svg class="h-5 w-5 flex-shrink-0" style="color: var(--color-text-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                wire:model.live="query"
                type="text"
                placeholder="Search projects, tasks, or type a command..."
                class="flex-1 bg-transparent border-0 focus:outline-none text-base"
                style="color: var(--color-text-900);"
                autofocus
                x-init="$el.focus()"
            />
            <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded border" style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-600);">
                ESC
            </kbd>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
            <button
                wire:click="switchTab('search')"
                class="flex-1 px-4 py-2 text-sm font-medium transition-colors"
                style="
                    {{ $activeTab === 'search' ? 'color: var(--color-glaucous); border-bottom: 2px solid var(--color-glaucous);' : 'color: var(--color-text-600);' }}
                "
            >
                <svg class="h-4 w-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Search
            </button>
            <button
                wire:click="switchTab('ai')"
                class="flex-1 px-4 py-2 text-sm font-medium transition-colors"
                style="
                    {{ $activeTab === 'ai' ? 'color: var(--color-glaucous); border-bottom: 2px solid var(--color-glaucous);' : 'color: var(--color-text-600);' }}
                "
            >
                <svg class="h-4 w-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Ask AI
            </button>
        </div>

        {{-- Content --}}
        <div class="max-h-[60vh] overflow-y-auto">
            @if($activeTab === 'search')
                {{-- Search Results --}}
                @if($totalResults > 0)
                    <div class="py-2">
                        {{-- Quick Actions --}}
                        @if(count($results['actions']) > 0)
                            <div class="px-2 py-2">
                                <div class="px-2 text-xs font-semibold uppercase tracking-wide mb-2" style="color: var(--color-text-500);">
                                    Quick Actions
                                </div>
                                @foreach($results['actions'] as $action)
                                    <a
                                        href="{{ route($action['route']) }}"
                                        wire:navigate
                                        @click="$wire.close()"
                                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors cursor-pointer hover:bg-[var(--color-background-200)]"
                                    >
                                        <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($action['icon'] === 'plus')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            @elseif($action['icon'] === 'clipboard')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            @elseif($action['icon'] === 'folder')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            @elseif($action['icon'] === 'calendar')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            @elseif($action['icon'] === 'home')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            @endif
                                        </svg>
                                        <span class="font-medium" style="color: var(--color-text-800);">{{ $action['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        {{-- Projects --}}
                        @if(count($results['projects']) > 0)
                            <div class="px-2 py-2">
                                <div class="px-2 text-xs font-semibold uppercase tracking-wide mb-2" style="color: var(--color-text-500);">
                                    Projects
                                </div>
                                @foreach($results['projects'] as $project)
                                    <a
                                        href="{{ route($project['route'], $project['id']) }}"
                                        wire:navigate
                                        @click="$wire.close()"
                                        class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors cursor-pointer hover:bg-[var(--color-background-200)]"
                                    >
                                        <div class="flex items-center gap-3">
                                            <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                            <div>
                                                <div class="font-medium" style="color: var(--color-text-800);">{{ $project['name'] }}</div>
                                                <div class="text-xs" style="color: var(--color-text-500);">{{ $project['progress'] }}% complete</div>
                                            </div>
                                        </div>
                                        <kbd class="px-2 py-0.5 text-xs rounded border" style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-500);">↵</kbd>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        {{-- Tasks --}}
                        @if(count($results['tasks']) > 0)
                            <div class="px-2 py-2">
                                <div class="px-2 text-xs font-semibold uppercase tracking-wide mb-2" style="color: var(--color-text-500);">
                                    Tasks
                                </div>
                                @foreach($results['tasks'] as $task)
                                    <a
                                        href="{{ route('tasks.show', $task['id']) }}"
                                        wire:navigate
                                        @click="$wire.close()"
                                        class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors cursor-pointer hover:bg-[var(--color-background-200)]"
                                    >
                                        <div class="flex items-center gap-3">
                                            @php
                                                $taskColors = [
                                                    'ai' => 'var(--color-glaucous)',
                                                    'human' => 'var(--color-tea-green)',
                                                    'hitl' => 'var(--color-orange-peel)',
                                                ];
                                            @endphp
                                            <div class="h-3 w-3 rounded-full" style="background-color: {{ $taskColors[$task['task_type']] }};"></div>
                                            <div>
                                                <div class="font-medium" style="color: var(--color-text-800);">{{ $task['title'] }}</div>
                                                <div class="text-xs" style="color: var(--color-text-500);">{{ $task['project'] }} • {{ ucfirst($task['status']) }}</div>
                                            </div>
                                        </div>
                                        <span class="text-xs px-2 py-0.5 rounded" style="background-color: {{ $taskColors[$task['task_type']] }}; color: white;">
                                            {{ strtoupper($task['task_type']) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="py-12 px-4 text-center">
                        <svg class="h-12 w-12 mx-auto mb-3" style="color: var(--color-text-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-sm font-medium mb-1" style="color: var(--color-text-700);">No results found</p>
                        <p class="text-xs" style="color: var(--color-text-500);">Try searching for projects, tasks, or commands</p>
                    </div>
                @endif
            @else
                {{-- AI Assistant Tab --}}
                <div class="py-12 px-8 text-center">
                    <div class="relative inline-block mb-6">
                        <svg class="h-16 w-16 mx-auto" style="color: var(--color-glaucous); opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span class="absolute -top-1 -right-1 px-2 py-1 rounded-full text-xs font-semibold" style="background-color: var(--color-orange-peel); color: white;">API</span>
                    </div>

                    <h3 class="text-xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-text-800);">AI Assistant</h3>
                    <p class="text-sm mb-6" style="color: var(--color-text-600);">Connect to AI API to ask questions about your projects</p>

                    <div class="inline-flex flex-col gap-2 text-left p-4 rounded-lg border-2 border-dashed mb-6" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
                        <p class="text-xs font-medium mb-2" style="color: var(--color-text-600);">Example questions:</p>
                        <div class="flex items-start gap-2 text-xs" style="color: var(--color-text-700);">
                            <span style="color: var(--color-glaucous);">•</span>
                            <span>"What are my high-priority tasks?"</span>
                        </div>
                        <div class="flex items-start gap-2 text-xs" style="color: var(--color-text-700);">
                            <span style="color: var(--color-glaucous);">•</span>
                            <span>"Show me projects that are behind schedule"</span>
                        </div>
                        <div class="flex items-start gap-2 text-xs" style="color: var(--color-text-700);">
                            <span style="color: var(--color-glaucous);">•</span>
                            <span>"Generate a status report for this week"</span>
                        </div>
                    </div>

                    <p class="text-xs" style="color: var(--color-text-500);">
                        This feature will be connected to your FastAPI service
                    </p>
                </div>
            @endif
        </div>

        {{-- Footer with shortcuts hint --}}
        <div class="px-4 py-2 border-t flex items-center justify-between text-xs" style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-500);">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 rounded border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">↑</kbd>
                    <kbd class="px-1.5 py-0.5 rounded border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">↓</kbd>
                    to navigate
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 rounded border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">↵</kbd>
                    to select
                </span>
            </div>
            <span class="flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 rounded border" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">⌘K</kbd>
                to open
            </span>
        </div>
    </div>
</div>
