@props(['size' => 'large', 'animated' => false])

@php
    // Map size names to Tailwind classes
    $sizeClass = match($size) {
        'small' => 'w-6 h-6',      // 24px
        'medium' => 'w-12 h-12',   // 48px
        'large' => 'w-24 h-24',    // 96px
        'xlarge' => 'w-36 h-36',   // 144px
        // Also support direct Tailwind class names
        default => $size
    };
@endphp

<div {{ $attributes->merge(['class' => "relative $sizeClass"]) }}>
    <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg"
         style="height: 2rem; " {{ $animated ? 'transition-all duration-300' : '' }}>
        {{-- C shape --}}
        <path
            d="M70 25C60 15 40 15 30 25C20 35 20 55 30 65C35 70 42 72 48 71"
            stroke="currentColor"
            stroke-width="8"
            stroke-linecap="round"
            fill="none"
        />

        {{-- F shape --}}
        <path
            d="M50 35 L50 65 M50 35 L70 35 M50 50 L65 50"
            stroke="currentColor"
            stroke-width="8"
            stroke-linecap="round"
            fill="none"
        />

        {{-- Flow circle accent --}}
        <circle
            cx="50"
            cy="50"
            r="35"
            stroke="currentColor"
            stroke-width="2"
            fill="none"
            opacity="0.2"
            class="{{ $animated ? 'origin-center' : '' }}"
            style="{{ $animated ? 'animation: spin 3s linear infinite; transform-origin: 50px 50px;' : '' }}"
        />
    </svg>
</div>
