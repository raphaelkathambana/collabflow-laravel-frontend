<div>
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Review & Approval</h3>

            {{-- Task Status Overview --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Current Status</div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($task->status === 'completed') bg-green-100 text-green-800
                            @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                            @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif
                        ">
                            {{ ucfirst($task->status) }}
                        </span>
                    </div>

                    @if($this->getConfidencePercentage())
                        <div>
                            <div class="text-xs text-gray-500 mb-1">AI Confidence</div>
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div
                                        class="h-2 rounded-full {{ $this->getConfidencePercentage() > 80 ? 'bg-green-500' : ($this->getConfidencePercentage() > 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                        style="width: {{ $this->getConfidencePercentage() }}%"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $this->getConfidencePercentage() }}%
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                @if($this->getExecutionTime())
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="text-xs text-gray-500">Execution Time</div>
                        <div class="text-sm font-medium text-gray-900 mt-1">
                            ⏱️ {{ $this->getExecutionTime() }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Previous Review Notes --}}
            @if($task->review_notes)
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Previous Review Notes</h4>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-gray-700">{{ $task->review_notes }}</p>
                        @if($task->reviewer)
                            <div class="mt-2 text-xs text-gray-500">
                                — {{ $task->reviewer->name }} • {{ $task->reviewed_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Review Actions --}}
            @if($task->status === 'review' || $task->output)
                <div class="flex space-x-3">
                    <button
                        wire:click="openApproveModal"
                        class="flex-1 inline-flex justify-center items-center px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve & Complete
                    </button>

                    <button
                        wire:click="openRequestChangesModal"
                        class="flex-1 inline-flex justify-center items-center px-4 py-3 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Request Changes
                    </button>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm">
                        @if($task->status === 'completed')
                            This task has been completed and approved.
                        @elseif($task->status === 'pending')
                            Waiting for work to be submitted.
                        @elseif($task->status === 'in_progress')
                            Work is currently in progress.
                        @else
                            Not ready for review yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Approve Modal --}}
    @if($showApproveModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="closeModals"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full" wire:click.stop>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Approve Task</h3>
                    </div>

                    <form wire:submit="approve" class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            You're about to approve this task and mark it as completed. Please provide your review notes.
                        </p>

                        <div class="mb-4">
                            <label for="approveNotes" class="block text-sm font-medium text-gray-700 mb-2">
                                Review Notes <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="approveNotes"
                                wire:model="reviewNotes"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                placeholder="What aspects of the work meet or exceed expectations?"
                            ></textarea>
                            @error('reviewNotes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                wire:click="closeModals"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
                            >
                                Approve & Complete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Request Changes Modal --}}
    @if($showRequestChangesModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50" wire:click="closeModals"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full" wire:click.stop>
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Request Changes</h3>
                    </div>

                    <form wire:submit="requestChanges" class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            Please describe what changes are needed. The task will be sent back to in-progress status.
                        </p>

                        <div class="mb-4">
                            <label for="changesNotes" class="block text-sm font-medium text-gray-700 mb-2">
                                Required Changes <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="changesNotes"
                                wire:model="reviewNotes"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Be specific about what needs to be improved or changed..."
                            ></textarea>
                            @error('reviewNotes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                wire:click="closeModals"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700"
                            >
                                Request Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
