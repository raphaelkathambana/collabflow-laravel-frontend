<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    @if($output)
        <div class="p-6">
            {{-- Header with output type and metadata --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">{{ $this->getOutputTypeLabel() }}</span>
                    <h3 class="text-lg font-semibold text-gray-900">Work Output</h3>
                </div>
                <button
                    wire:click="toggleRawJson"
                    class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1 rounded border border-gray-300 hover:border-gray-400 transition"
                >
                    {{ $showRawJson ? 'Show Formatted' : 'Show Raw JSON' }}
                </button>
            </div>

            {{-- Metadata Bar --}}
            @if(isset($output['metadata']) || $task->confidence_score || $task->completed_at)
                <div class="bg-gray-50 rounded-lg p-4 mb-6 grid grid-cols-3 gap-4">
                    @if($task->confidence_score)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Confidence Score</div>
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div
                                        class="bg-{{ $task->confidence_score > 0.8 ? 'green' : ($task->confidence_score > 0.6 ? 'yellow' : 'red') }}-500 h-2 rounded-full"
                                        style="width: {{ $task->confidence_score * 100 }}%"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ round($task->confidence_score * 100) }}%
                                </span>
                            </div>
                        </div>
                    @endif

                    @if($task->completed_at)
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Completed</div>
                            <div class="text-sm font-medium text-gray-700">
                                {{ $task->completed_at->diffForHumans() }}
                            </div>
                        </div>
                    @endif

                    @if(isset($output['metadata']['model']))
                        <div>
                            <div class="text-xs text-gray-500 mb-1">AI Model</div>
                            <div class="text-sm font-medium text-gray-700">
                                {{ $output['metadata']['model'] }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Output Content --}}
            @if($showRawJson)
                {{-- Raw JSON View --}}
                <div class="bg-gray-900 text-gray-100 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm"><code>{{ json_encode($output, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            @else
                {{-- Formatted View --}}
                <div class="space-y-4">
                    {{-- Content --}}
                    @if(isset($output['content']))
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Content</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                @if(($output['format'] ?? 'text') === 'markdown')
                                    <div class="prose max-w-none">
                                        {!! \Illuminate\Support\Str::markdown($output['content']) !!}
                                    </div>
                                @elseif(($output['format'] ?? 'text') === 'html')
                                    <div class="prose max-w-none">
                                        {!! $output['content'] !!}
                                    </div>
                                @elseif(($output['format'] ?? 'text') === 'json')
                                    <pre class="text-sm bg-gray-900 text-gray-100 rounded p-3 overflow-x-auto"><code>{{ json_encode(json_decode($output['content']), JSON_PRETTY_PRINT) }}</code></pre>
                                @else
                                    <pre class="text-sm whitespace-pre-wrap font-mono">{{ $output['content'] }}</pre>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Files --}}
                    @if(isset($output['files']) && count($output['files']) > 0)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">
                                Generated Files ({{ count($output['files']) }})
                            </h4>
                            <div class="space-y-2">
                                @foreach($output['files'] as $file)
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $file['name'] ?? 'Unnamed file' }}
                                                </span>
                                            </div>
                                            @if(isset($file['size']))
                                                <span class="text-xs text-gray-500">
                                                    {{ number_format($file['size'] / 1024, 2) }} KB
                                                </span>
                                            @endif
                                        </div>
                                        @if(isset($file['content']) && strlen($file['content']) < 1000)
                                            <div class="mt-2 text-xs bg-gray-50 rounded p-2 overflow-x-auto">
                                                <pre class="font-mono">{{ substr($file['content'], 0, 500) }}{{ strlen($file['content']) > 500 ? '...' : '' }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Additional Metadata --}}
                    @if(isset($output['metadata']) && count($output['metadata']) > 0)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Additional Information</h4>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <dl class="grid grid-cols-2 gap-3">
                                    @foreach($output['metadata'] as $key => $value)
                                        @if(!in_array($key, ['model', 'confidence']))
                                            <div>
                                                <dt class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                                <dd class="text-sm font-medium text-gray-900 mt-0.5">
                                                    @if(is_array($value))
                                                        {{ json_encode($value) }}
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </dd>
                                            </div>
                                        @endif
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @else
        {{-- No output yet --}}
        <div class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No work output yet</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if($task->status === 'pending')
                    This task hasn't been started yet.
                @elseif($task->status === 'in_progress')
                    Work is currently in progress.
                @else
                    No output has been submitted for this task.
                @endif
            </p>
        </div>
    @endif
</div>
