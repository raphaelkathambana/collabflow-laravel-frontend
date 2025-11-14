@props(['title', 'description', 'actionLabel' => 'Get Started', 'actionUrl' => null])

<div class="flex flex-col items-center justify-center py-16 px-4 text-center">
    {{-- CF Logo --}}
    <x-cf-logo size="24" class="text-[var(--eggplant)] opacity-20 mb-6" />

    {{-- Title --}}
    <h4 class="text-lg font-semibold mb-2" style="color: var(--color-text-700);">{{ $title }}</h4>

    {{-- Description --}}
    <p class="text-sm mb-6 max-w-md" style="color: var(--color-text-500);">{{ $description }}</p>

    {{-- Action Button (optional) --}}
    @if($actionUrl)
        <a href="{{ $actionUrl }}" wire:navigate>
            <button type="button" class="flex items-center gap-2 px-4 py-2 rounded-lg text-white font-medium transition-opacity" style="background-color: var(--color-bittersweet);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ $actionLabel }}
            </button>
        </a>
    @endif
</div>