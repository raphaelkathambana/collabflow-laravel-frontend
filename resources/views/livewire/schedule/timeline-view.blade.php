<div class="border rounded-xl p-6 overflow-x-auto" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
    <div class="min-w-[800px]">
        {{-- Timeline header --}}
        <div class="flex mb-4 border-b pb-2" style="border-color: var(--color-background-300);">
            <div class="w-48 flex-shrink-0 font-semibold" style="color: var(--color-text-700);">Project</div>
            <div class="flex-1 flex">
                @foreach($days as $i => $day)
                    <div class="flex-1 text-center text-xs {{ $day->isToday() ? 'font-semibold' : '' }}" style="color: {{ $day->isToday() ? 'var(--color-glaucous)' : 'var(--color-text-500)' }};">
                        <div>{{ $day->day }}</div>
                        <div class="text-[10px]">{{ $day->format('D') }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Project timelines --}}
        <div class="space-y-6">
            @foreach($projects as $project)
                <div class="space-y-2">
                    {{-- Project name and progress --}}
                    <div class="flex items-center gap-4">
                        <div class="w-48 flex-shrink-0">
                            <div class="font-semibold text-sm" style="color: var(--color-text-800);">{{ $project['name'] }}</div>
                            <div class="text-xs" style="color: var(--color-text-500);">{{ $project['progress'] }}% complete</div>
                        </div>
                        <div class="flex-1 relative h-12 rounded-lg" style="background-color: var(--color-background-100);">
                            {{-- Today indicator --}}
                            @php
                                $todayOffset = \Carbon\Carbon::now()->diffInDays($startDate, false);
                                $todayPosition = (($todayOffset + 7) / 21) * 100;
                            @endphp
                            <div
                                class="absolute top-0 bottom-0 w-0.5 z-10"
                                style="background-color: var(--color-glaucous); left: {{ $todayPosition }}%;"
                            ></div>

                            {{-- Task bars --}}
                            @foreach($project['tasks'] as $i => $task)
                                @php
                                    $left = (($task['start'] + 7) / 21) * 100;
                                    $width = ($task['duration'] / 21) * 100;
                                @endphp
                                <div
                                    class="absolute h-8 rounded flex items-center px-2 text-white text-xs font-medium shadow-sm"
                                    style="
                                        background-color: {{ $this->getTaskColor($task['type']) }};
                                        left: {{ $left }}%;
                                        width: {{ $width }}%;
                                        top: 8px;
                                    "
                                    title="{{ $task['name'] }}"
                                >
                                    <span class="truncate">{{ $task['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="flex items-center gap-6 mt-8 pt-4 border-t" style="border-color: var(--color-background-300);">
            <div class="text-sm font-semibold" style="color: var(--color-text-700);">Legend:</div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded" style="background-color: var(--color-glaucous);"></div>
                <span class="text-xs" style="color: var(--color-text-600);">AI Tasks</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded" style="background-color: var(--color-tea-green);"></div>
                <span class="text-xs" style="color: var(--color-text-600);">Human Tasks</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded" style="background-color: var(--color-orange-peel);"></div>
                <span class="text-xs" style="color: var(--color-text-600);">HITL Tasks</span>
            </div>
        </div>
    </div>
</div>
