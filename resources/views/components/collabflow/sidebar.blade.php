{{-- CollabFlow Sidebar Component --}}
@props(['collapsed' => false])

<aside
    x-data="{ collapsed: {{ $collapsed ? 'true' : 'false' }} }"
    :class="collapsed ? 'w-[60px]' : 'w-[280px]'"
    class="fixed left-0 top-0 h-screen border-r transition-all duration-300 bg-[var(--color-background-50)] dark:bg-[var(--color-background-50)] border-[var(--color-background-300)]"
    style="z-index: 40;"
>
    <div class="flex h-full flex-col">
        {{-- Logo --}}
        <div class="flex h-14 items-center border-b border-[var(--color-background-300)] px-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3" wire:navigate>
                <x-cf-logo size="8" class="text-[var(--eggplant)] flex-shrink-0" />
                <span x-show="!collapsed" class="font-bold text-lg" style="color: var(--color-eggplant); font-family: Tahoma;">
                    CollabFlow
                </span>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 space-y-1 p-3">
            @php
                $navigation = [
                    ['name' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
                    ['name' => 'Projects', 'route' => 'projects.index', 'icon' => 'folder'],
                    ['name' => 'Tasks', 'route' => 'tasks.index', 'icon' => 'check-square'],
                    ['name' => 'Schedule', 'route' => 'schedule.index', 'icon' => 'calendar'],
                ];
            @endphp

            @foreach($navigation as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-[var(--color-background-200)] {{ $isActive ? 'bg-[var(--color-accent-100)] border-l-4 border-[var(--color-glaucous)] dark:bg-[var(--color-background-300)]' : '' }}"
                    style="color: {{ $isActive ? 'var(--color-glaucous)' : 'var(--color-text-600)' }};"
                >
                    <x-dynamic-component :component="'icon.' . $item['icon']" class="h-5 w-5 flex-shrink-0" />
                    <span x-show="!collapsed">{{ $item['name'] }}</span>
                </a>
            @endforeach

            {{-- Quick Action: New Project --}}
            <div x-show="!collapsed" class="pt-4">
                <a href="{{ route('projects.create') }}" wire:navigate>
                    <button class="w-full flex items-center justify-start gap-2 px-3 py-2 text-sm font-medium rounded-lg text-white transition-all bg-[var(--color-bittersweet)] hover:bg-[var(--color-primary-600)]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>New Project</span>
                    </button>
                </a>
            </div>
        </nav>

        {{-- Bottom Navigation --}}
        <div class="border-t border-[var(--color-background-300)] p-3 space-y-1">
            @php
                $bottomNav = [
                    ['name' => 'Settings', 'route' => 'settings', 'icon' => 'settings'],
                    ['name' => 'Help', 'route' => 'help', 'icon' => 'help-circle'],
                ];
            @endphp

            @foreach($bottomNav as $item)
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-[var(--color-background-200)]"
                    style="color: var(--color-text-600);"
                >
                    <x-dynamic-component :component="'icon.' . $item['icon']" class="h-5 w-5 flex-shrink-0" />
                    <span x-show="!collapsed">{{ $item['name'] }}</span>
                </a>
            @endforeach

            {{-- Collapse Toggle --}}
            <button
                @click="collapsed = !collapsed"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-[var(--color-background-200)]"
                style="color: var(--color-text-600);"
            >
                <svg x-show="collapsed" class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <template x-if="!collapsed">
                    <div class="flex items-center gap-3 w-full">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Collapse</span>
                    </div>
                </template>
            </button>
        </div>
    </div>
</aside>
