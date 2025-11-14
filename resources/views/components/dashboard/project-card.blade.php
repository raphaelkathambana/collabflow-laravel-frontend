{{-- Dashboard Project Card Component --}}
@props(['project'])

@php
    $statusColors = [
        'active' => ['text' => 'var(--color-glaucous)', 'bg' => 'var(--color-accent-100)'],
        'in_progress' => ['text' => 'var(--color-glaucous)', 'bg' => 'var(--color-accent-100)'],
        'planning' => ['text' => 'var(--color-orange-peel)', 'bg' => 'var(--color-secondary-100)'],
        'completed' => ['text' => 'var(--color-tea-green)', 'bg' => 'var(--color-success-100)'],
        'on_hold' => ['text' => 'var(--color-text-600)', 'bg' => 'var(--color-background-200)'],
        'draft' => ['text' => 'var(--color-text-600)', 'bg' => 'var(--color-background-200)'],
    ];

    $statusColor = $statusColors[$project->status] ?? $statusColors['draft'];
    $statusLabel = ucfirst(str_replace('_', ' ', $project->status));
@endphp

<a href="{{ route('projects.show', $project->id) }}" wire:navigate class="block">
    <div class="p-6 rounded-lg border transition-shadow hover:shadow-md" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
        <h3 class="font-bold text-lg mb-2" style="color: var(--color-text-800);">{{ $project->name }}</h3>
        <p class="text-sm mb-4 line-clamp-2" style="color: var(--color-text-600);">
            {{ $project->description ?? 'No description available' }}
        </p>

        {{-- Progress Bar --}}
        <div class="space-y-2">
            <div class="flex justify-between text-xs" style="color: var(--color-text-600);">
                <span>Progress</span>
                <span>{{ $project->progress }}%</span>
            </div>
            <div class="h-2 rounded-full overflow-hidden" style="background-color: var(--color-background-300);">
                <div class="h-full rounded-full transition-all"
                     style="width: {{ $project->progress }}%; background-color: var(--color-glaucous);"></div>
            </div>
        </div>

        {{-- Footer: Status and Task Count --}}
        <div class="mt-4 flex items-center justify-between">
            <span class="px-2 py-1 rounded text-xs font-medium"
                  style="background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};">
                {{ $statusLabel }}
            </span>
            <span class="text-xs" style="color: var(--color-text-600);">
                {{ $project->task_count ?? 0 }} {{ Str::plural('task', $project->task_count ?? 0) }}
            </span>
        </div>
    </div>
</a>
