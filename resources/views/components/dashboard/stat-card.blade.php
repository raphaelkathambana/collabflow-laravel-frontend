{{-- Dashboard Stat Card Component --}}
@props(['title', 'value', 'icon', 'color' => 'glaucous', 'subtitle' => null])

@php
    $colorMap = [
        'glaucous' => ['text' => 'var(--color-glaucous)', 'bg' => 'var(--color-accent-100)'],
        'orange-peel' => ['text' => 'var(--color-orange-peel)', 'bg' => 'var(--color-secondary-100)'],
        'tea-green' => ['text' => 'var(--color-tea-green)', 'bg' => 'var(--color-success-100)'],
        'text' => ['text' => 'var(--color-text-600)', 'bg' => 'var(--color-background-200)'],
    ];

    $colors = $colorMap[$color] ?? $colorMap['text'];
@endphp

<div class="p-6 rounded-lg border" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium" style="color: var(--color-text-600);">{{ $title }}</p>
            <p class="text-3xl font-bold mt-2" style="color: {{ $colors['text'] }};">{{ $value }}</p>
        </div>
        <div class="p-3 rounded-lg" style="background-color: {{ $colors['bg'] }};">
            {{ $icon }}
        </div>
    </div>
    @if($subtitle)
        <p class="text-xs mt-4" style="color: var(--color-text-600);">{{ $subtitle }}</p>
    @endif
</div>
