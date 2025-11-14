<div style="display: {{ $isOpen ? 'block' : 'none' }};">
    {{-- Overlay --}}
    <div class="fixed inset-0 bg-black/50 z-40" wire:click="closePanel"></div>

    {{-- Panel --}}
    <div class="fixed right-0 top-0 bottom-0 w-full max-w-2xl border-l z-50 flex flex-col" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        {{-- Header --}}
        <div class="flex items-center justify-between p-6 border-b" style="border-color: var(--color-background-300);">
            <h2 class="text-xl font-semibold" style="color: var(--color-text-800);">Notifications</h2>
            <button type="button" wire:click="closePanel" class="p-2 rounded-lg transition-colors hover:bg-[var(--color-background-200)]" style="color: var(--color-text-600);">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex gap-2 px-6 py-4 border-b" style="border-color: var(--color-background-300);">
            @foreach(['all', 'unread', 'read'] as $tab)
                <button
                    type="button"
                    wire:click="setFilter('{{ $tab }}')"
                    class="px-3 py-1 rounded-full text-sm font-medium transition-colors"
                    style="
                        {{ $filter === $tab
                            ? 'background-color: var(--color-glaucous); color: white;'
                            : 'background-color: var(--color-background-100); color: var(--color-text-600);'
                        }}
                    "
                    onmouseover="if('{{ $filter }}' !== '{{ $tab }}') this.style.backgroundColor='var(--color-background-200)'"
                    onmouseout="if('{{ $filter }}' !== '{{ $tab }}') this.style.backgroundColor='var(--color-background-100)'"
                >
                    {{ ucfirst($tab) }}
                </button>
            @endforeach
        </div>

        {{-- Bulk Actions --}}
        @if($unreadCount > 0)
            <div class="px-6 py-3 border-b flex items-center justify-between" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
                <span class="text-sm" style="color: var(--color-text-600);">
                    {{ $unreadCount }} unread notification{{ $unreadCount > 1 ? 's' : '' }}
                </span>
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="text-sm px-3 py-1 rounded-lg transition-colors"
                    style="color: var(--color-glaucous);"
                    onmouseover="this.style.backgroundColor='rgba(92,128,188,0.1)'"
                    onmouseout="this.style.backgroundColor='transparent'"
                >
                    Mark all as read
                </button>
            </div>
        @endif

        {{-- Notifications List --}}
        <div class="flex-1 overflow-y-auto">
            @if(count($filteredNotifications) === 0)
                <x-empty-state
                    title="No Notifications"
                    description="You're all caught up! We'll notify you when there's something new."
                />
            @else
                <div class="divide-y" style="border-color: var(--color-background-300);">
                    @foreach($filteredNotifications as $notification)
                        @php
                            $typeColors = [
                                'task' => 'rgba(196,214,176,0.1)',
                                'project' => 'rgba(92,128,188,0.1)',
                                'hitl' => 'rgba(255,159,28,0.1)',
                                'comment' => 'rgba(235,94,85,0.1)',
                            ];
                            $typeBorderColors = [
                                'task' => 'rgba(196,214,176,0.2)',
                                'project' => 'rgba(92,128,188,0.2)',
                                'hitl' => 'rgba(255,159,28,0.2)',
                                'comment' => 'rgba(235,94,85,0.2)',
                            ];
                            $typeLabels = [
                                'task' => 'Task',
                                'project' => 'Project',
                                'hitl' => 'HITL',
                                'comment' => 'Comment',
                            ];
                        @endphp
                        <div
                            class="p-6 transition-colors border-l-4"
                            style="
                                border-left-color: {{ !$notification['read'] ? 'var(--color-glaucous)' : 'transparent' }};
                                background-color: {{ !$notification['read'] ? 'var(--color-background-100)' : 'transparent' }};
                            "
                            onmouseover="this.style.backgroundColor='var(--color-background-100)'"
                            onmouseout="this.style.backgroundColor='{{ !$notification['read'] ? 'var(--color-background-100)' : 'transparent' }}'"
                        >
                            <div class="flex items-start gap-4">
                                {{-- Type Badge --}}
                                <div class="px-2 py-1 rounded text-xs font-medium flex-shrink-0 border" style="background-color: {{ $typeColors[$notification['type']] }}; border-color: {{ $typeBorderColors[$notification['type']] }};">
                                    {{ $typeLabels[$notification['type']] }}
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold" style="color: var(--color-text-800);">{{ $notification['title'] }}</h3>
                                    <p class="text-sm mt-1" style="color: var(--color-text-600);">{{ $notification['message'] }}</p>
                                    <p class="text-xs mt-2" style="color: var(--color-text-500);">{{ $notification['timestamp'] }}</p>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button
                                        type="button"
                                        wire:click="markAsRead({{ $notification['id'] }})"
                                        class="p-2 rounded-lg transition-colors"
                                        style="color: var(--color-text-600);"
                                        onmouseover="this.style.color='var(--color-glaucous)'"
                                        onmouseout="this.style.color='var(--color-text-600)'"
                                        title="{{ $notification['read'] ? 'Mark as unread' : 'Mark as read' }}"
                                    >
                                        @if($notification['read'])
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteNotification({{ $notification['id'] }})"
                                        class="p-2 rounded-lg transition-colors"
                                        style="color: var(--color-text-600);"
                                        onmouseover="this.style.color='var(--color-bittersweet)'"
                                        onmouseout="this.style.color='var(--color-text-600)'"
                                        title="Delete"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>