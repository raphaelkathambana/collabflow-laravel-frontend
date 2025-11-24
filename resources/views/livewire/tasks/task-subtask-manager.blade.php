<div>
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    {{-- Header with Progress --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Subtasks</h3>
            <button
                wire:click="openAddModal"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Subtask
            </button>
        </div>

        {{-- Progress Bar --}}
        @if(count($subtasks) > 0)
            <div class="mb-2">
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-600">Progress</span>
                    <span class="font-medium text-gray-900">
                        {{ $task->getCompletedSubtasksCount() }} / {{ count($subtasks) }} completed
                        ({{ round($task->getSubtaskProgress()) }}%)
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-green-500 h-2 rounded-full transition-all duration-300"
                        style="width: {{ $task->getSubtaskProgress() }}%"
                    ></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Subtasks List --}}
    @if(count($subtasks) > 0)
        <div class="space-y-3">
            @foreach($subtasks as $index => $subtask)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
                    <div class="flex items-start space-x-3">
                        {{-- Checkbox --}}
                        <button
                            wire:click="toggleSubtaskComplete('{{ $subtask['id'] }}')"
                            class="flex-shrink-0 mt-1"
                        >
                            @if(($subtask['status'] ?? 'pending') === 'completed')
                                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-gray-300 hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="8" stroke-width="2"></circle>
                                </svg>
                            @endif
                        </button>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                <h4 class="text-sm font-medium text-gray-900 {{ ($subtask['status'] ?? 'pending') === 'completed' ? 'line-through' : '' }}">
                                    {{ $index + 1 }}. {{ $subtask['name'] }}
                                </h4>

                                {{-- Type Badge --}}
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if($subtask['type'] === 'ai') bg-blue-100 text-blue-800
                                    @elseif($subtask['type'] === 'human') bg-green-100 text-green-800
                                    @else bg-orange-100 text-orange-800
                                    @endif
                                ">
                                    {{ strtoupper($subtask['type']) }}
                                </span>

                                {{-- Checkpoint Badge --}}
                                @if($subtask['is_checkpoint'] ?? false)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                        ⭐ Checkpoint
                                    </span>
                                @endif
                            </div>

                            @if($subtask['description'] ?? false)
                                <p class="text-sm text-gray-600 mb-2">{{ $subtask['description'] }}</p>
                            @endif

                            {{-- Metadata --}}
                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                @if($subtask['estimated_hours'] ?? false)
                                    <span>⏱️ {{ $subtask['estimated_hours'] }}h estimated</span>
                                @endif
                                @if(isset($subtask['completed_at']))
                                    <span>✓ Completed {{ \Carbon\Carbon::parse($subtask['completed_at'])->diffForHumans() }}</span>
                                @endif
                                @if(isset($subtask['completed_by']))
                                    <span>by {{ $subtask['completed_by'] }}</span>
                                @endif
                            </div>

                            {{-- Output Preview --}}
                            @if(isset($subtask['output']) && $subtask['output'])
                                <div class="mt-2 p-2 bg-gray-50 rounded border border-gray-200">
                                    <div class="text-xs font-medium text-gray-700 mb-1">Output:</div>
                                    <div class="text-xs text-gray-600">
                                        {{ \Illuminate\Support\Str::limit($subtask['output']['content'] ?? json_encode($subtask['output']), 150) }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center space-x-2">
                            <button
                                wire:click="editSubtask('{{ $subtask['id'] }}')"
                                class="p-2 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50 transition"
                                title="Edit"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button
                                wire:click="deleteSubtask('{{ $subtask['id'] }}')"
                                wire:confirm="Are you sure you want to delete this subtask?"
                                class="p-2 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition"
                                title="Delete"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No subtasks yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new subtask.</p>
            <div class="mt-6">
                <button
                    wire:click="openAddModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Subtask
                </button>
            </div>
        </div>
    @endif

    {{-- Add/Edit Subtask Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="closeAddModal"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full" wire:click.stop>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $editingSubtaskId ? 'Edit Subtask' : 'Add New Subtask' }}
                        </h3>
                    </div>

                    <form wire:submit="saveSubtask" class="p-6 space-y-4">
                        {{-- Name --}}
                        <div>
                            <label for="subtaskName" class="block text-sm font-medium text-gray-700 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="subtaskName"
                                wire:model="subtaskName"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter subtask name"
                            >
                            @error('subtaskName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="subtaskDescription" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                id="subtaskDescription"
                                wire:model="subtaskDescription"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter subtask description"
                            ></textarea>
                            @error('subtaskDescription') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Type --}}
                        <div>
                            <label for="subtaskType" class="block text-sm font-medium text-gray-700 mb-1">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="subtaskType"
                                wire:model="subtaskType"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="human">Human</option>
                                <option value="ai">AI</option>
                                <option value="hitl">Human-in-the-Loop (HITL)</option>
                            </select>
                            @error('subtaskType') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Estimated Hours --}}
                        <div>
                            <label for="subtaskEstimatedHours" class="block text-sm font-medium text-gray-700 mb-1">
                                Estimated Hours
                            </label>
                            <input
                                type="number"
                                id="subtaskEstimatedHours"
                                wire:model="subtaskEstimatedHours"
                                step="0.5"
                                min="0.1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g., 2.5"
                            >
                            @error('subtaskEstimatedHours') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Checkpoint --}}
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                id="subtaskIsCheckpoint"
                                wire:model="subtaskIsCheckpoint"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="subtaskIsCheckpoint" class="ml-2 block text-sm text-gray-700">
                                Mark as checkpoint (requires manual review)
                            </label>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                wire:click="closeAddModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                            >
                                {{ $editingSubtaskId ? 'Update Subtask' : 'Add Subtask' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
