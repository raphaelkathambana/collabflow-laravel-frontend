<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        {{-- Header with Filters --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Activity Timeline</h3>

            <div class="flex space-x-2">
                <button
                    wire:click="setFilter('all')"
                    class="px-3 py-1.5 text-sm font-medium rounded-md transition {{ $filter === 'all' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}"
                >
                    All
                </button>
                <button
                    wire:click="setFilter('ai')"
                    class="px-3 py-1.5 text-sm font-medium rounded-md transition {{ $filter === 'ai' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}"
                >
                    AI
                </button>
                <button
                    wire:click="setFilter('human')"
                    class="px-3 py-1.5 text-sm font-medium rounded-md transition {{ $filter === 'human' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}"
                >
                    Human
                </button>
                <button
                    wire:click="setFilter('system')"
                    class="px-3 py-1.5 text-sm font-medium rounded-md transition {{ $filter === 'system' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}"
                >
                    System
                </button>
            </div>
        </div>

        {{-- Timeline --}}
        @if(count($activities) > 0)
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($activities as $index => $activity)
                        <li>
                            <div class="relative pb-8">
                                {{-- Connecting Line --}}
                                @if($index < count($activities) - 1)
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif

                                <div class="relative flex items-start space-x-3">
                                    {{-- Icon --}}
                                    <div class="relative">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-lg
                                            {{ $this->getActivityColor($activity['action']) === 'blue' ? 'bg-blue-100' : '' }}
                                            {{ $this->getActivityColor($activity['action']) === 'green' ? 'bg-green-100' : '' }}
                                            {{ $this->getActivityColor($activity['action']) === 'orange' ? 'bg-orange-100' : '' }}
                                            {{ $this->getActivityColor($activity['action']) === 'red' ? 'bg-red-100' : '' }}
                                            {{ $this->getActivityColor($activity['action']) === 'purple' ? 'bg-purple-100' : '' }}
                                            {{ $this->getActivityColor($activity['action']) === 'gray' ? 'bg-gray-100' : '' }}
                                        ">
                                            {{ $this->getActivityIcon($activity['action']) }}
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $this->getActivityTitle($activity['action']) }}
                                                </p>
                                                @if(isset($activity['user']))
                                                    <p class="mt-0.5 text-xs text-gray-500">
                                                        by {{ $activity['user']['name'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <time class="flex-shrink-0 text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}
                                            </time>
                                        </div>

                                        {{-- Details --}}
                                        @if(isset($activity['details']) && count($activity['details']) > 0)
                                            <div class="mt-2 text-sm text-gray-700 bg-gray-50 rounded-lg p-3">
                                                @foreach($activity['details'] as $key => $value)
                                                    @if($key === 'notes' && $value)
                                                        <div class="mb-2 last:mb-0">
                                                            <span class="font-medium">Notes:</span>
                                                            <p class="mt-1 text-gray-600">{{ $value }}</p>
                                                        </div>
                                                    @elseif($key === 'output_type')
                                                        <div class="mb-2 last:mb-0">
                                                            <span class="font-medium">Output Type:</span>
                                                            <span class="ml-2 text-gray-600">{{ ucfirst($value) }}</span>
                                                        </div>
                                                    @elseif($key === 'confidence_score')
                                                        <div class="mb-2 last:mb-0">
                                                            <span class="font-medium">Confidence:</span>
                                                            <span class="ml-2 text-gray-600">{{ round($value * 100) }}%</span>
                                                        </div>
                                                    @elseif($key === 'old_status' || $key === 'new_status')
                                                        @if($key === 'new_status')
                                                            <div class="mb-2 last:mb-0">
                                                                <span class="font-medium">Status:</span>
                                                                <span class="ml-2 text-gray-600">
                                                                    {{ ucfirst($activity['details']['old_status'] ?? 'unknown') }}
                                                                    →
                                                                    {{ ucfirst($value) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @elseif($key === 'subtask_name')
                                                        <div class="mb-2 last:mb-0">
                                                            <span class="font-medium">Subtask:</span>
                                                            <span class="ml-2 text-gray-600">{{ $value }}</span>
                                                        </div>
                                                    @elseif($key === 'subtask_id')
                                                        {{-- Skip, handled by subtask_name --}}
                                                    @elseif($key === 'completed_by')
                                                        <div class="mb-2 last:mb-0">
                                                            <span class="font-medium">Completed by:</span>
                                                            <span class="ml-2 text-gray-600">{{ $value }}</span>
                                                        </div>
                                                    @elseif($key === 'updates' && is_array($value))
                                                        <div class="mb-2 last:mb-0">
                                                            <span class="font-medium">Updated fields:</span>
                                                            <span class="ml-2 text-gray-600">{{ implode(', ', $value) }}</span>
                                                        </div>
                                                    @elseif(!is_array($value) && !is_object($value))
                                                        <div class="mb-2 last:mb-0">
                                                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                            <span class="ml-2 text-gray-600">{{ $value }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No activity yet</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($filter !== 'all')
                        No {{ $filter }} activities found. Try changing the filter.
                    @else
                        Activity will appear here as work progresses on this task.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
