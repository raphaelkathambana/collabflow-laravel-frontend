@props(['loading' => false, 'size' => 'large', 'showProgress' => false, 'progress' => 0])

@php
    // Map size names to Tailwind classes
    $sizeClass = match($size) {
        'small' => 'w-4 h-4',      // 16px
        'medium' => 'w-8 h-8',     // 32px
        'large' => 'w-16 h-16',    // 64px
        'xlarge' => 'w-32 h-32',   // 128px
        default => $size
    };
@endphp

<div {{ $attributes->merge(['class' => "relative inline-block $sizeClass"]) }}
     x-data="{
         loading: @js($loading),
         progress: @js($progress),
         showProgress: @js($showProgress)
     }">
    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
        <defs>
            {{-- Gradient for loading state --}}
            <linearGradient id="cfGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:var(--glaucous);stop-opacity:1" />
                <stop offset="100%" style="stop-color:var(--tea-green);stop-opacity:1" />
            </linearGradient>
        </defs>

        {{-- Loading State: Spinning Circle with Gradient --}}
        <g x-show="loading" x-transition:enter="transition ease-out duration-500"
           x-transition:enter-start="opacity-0 scale-90"
           x-transition:enter-end="opacity-100 scale-100">
            <circle
                cx="50"
                cy="50"
                r="30"
                stroke="url(#cfGradient)"
                stroke-width="2.5"
                stroke-linecap="round"
                fill="none"
                class="animate-spin origin-center"
                style="animation-duration: 1.5s; transform-origin: 50px 50px;"
            />

            {{-- Progress indicator (optional partial circle) --}}
            <template x-if="showProgress && progress > 0">
                <circle
                    cx="50"
                    cy="50"
                    r="30"
                    stroke="var(--glaucous)"
                    stroke-width="3"
                    stroke-linecap="round"
                    fill="none"
                    :stroke-dasharray="`${(progress / 100) * 188.4} 188.4`"
                    stroke-dashoffset="0"
                    transform="rotate(-90 50 50)"
                    class="transition-all duration-300"
                />
            </template>
        </g>

        {{-- Idle/Complete State: Static CF Logo --}}
        <g x-show="!loading" x-transition:enter="transition ease-out duration-500"
           x-transition:enter-start="opacity-0 scale-90 rotate-180"
           x-transition:enter-end="opacity-100 scale-100 rotate-0">
            {{-- C Letter (Blue - Glaucous) --}}
            <path
                d="M 70 20 A 30 30 0 1 0 70 80"
                stroke="var(--glaucous)"
                stroke-width="2.5"
                stroke-linecap="round"
                fill="none"
            />

            {{-- F Letter (Green - Tea Green) --}}
            <g stroke="var(--tea-green)" stroke-width="2.5" stroke-linecap="round">
                {{-- Vertical stem --}}
                <line x1="30" y1="20" x2="30" y2="80" />
                {{-- Top horizontal bar --}}
                <line x1="30" y1="20" x2="55" y2="20" />
                {{-- Middle horizontal bar --}}
                <line x1="30" y1="50" x2="50" y2="50" />
            </g>
        </g>
    </svg>

    {{-- Progress Text (optional) --}}
    <template x-if="showProgress && loading">
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-xs font-medium" style="color: var(--color-text-700);" x-text="`${Math.round(progress)}%`"></span>
        </div>
    </template>
</div>

<style>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
