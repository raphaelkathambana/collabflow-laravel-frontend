<div class="max-w-3xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="text-center space-y-3">
        <h1 class="text-3xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">
            Help & Support
        </h1>
        <p style="color: var(--color-text-600);">Find answers to common questions and get support</p>
    </div>

    {{-- Search --}}
    <div class="relative">
        <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2" style="color: var(--color-text-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <input
            type="search"
            wire:model.live="searchQuery"
            placeholder="Search help articles..."
            class="w-full pl-10 pr-4 py-2 border rounded-lg"
            style="background-color: var(--color-background-50); border-color: var(--color-background-300); color: var(--color-text-900);"
        />
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="mailto:support@collabflow.com" class="flex items-center gap-3 p-4 border rounded-lg transition-colors" style="background-color: var(--color-background-50); border-color: var(--color-background-300);" onmouseover="this.style.borderColor='var(--color-glaucous)'" onmouseout="this.style.borderColor='var(--color-background-300)'">
            <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <div>
                <p class="font-medium" style="color: var(--color-text-800);">Email Support</p>
                <p class="text-xs" style="color: var(--color-text-600);">support@collabflow.com</p>
            </div>
        </a>

        <a href="#" class="flex items-center gap-3 p-4 border rounded-lg transition-colors" style="background-color: var(--color-background-50); border-color: var(--color-background-300);" onmouseover="this.style.borderColor='var(--color-glaucous)'" onmouseout="this.style.borderColor='var(--color-background-300)'">
            <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <div>
                <p class="font-medium" style="color: var(--color-text-800);">Live Chat</p>
                <p class="text-xs" style="color: var(--color-text-600);">Chat with our team</p>
            </div>
        </a>

        <a href="#" class="flex items-center gap-3 p-4 border rounded-lg transition-colors" style="background-color: var(--color-background-50); border-color: var(--color-background-300);" onmouseover="this.style.borderColor='var(--color-glaucous)'" onmouseout="this.style.borderColor='var(--color-background-300)'">
            <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <div>
                <p class="font-medium" style="color: var(--color-text-800);">Documentation</p>
                <p class="text-xs" style="color: var(--color-text-600);">Read our docs</p>
            </div>
        </a>
    </div>

    {{-- FAQs --}}
    <div class="space-y-4">
        <h2 class="text-xl font-bold" style="color: var(--color-text-900);">Frequently Asked Questions</h2>

        @if(count($filteredFaqs) > 0)
            @foreach($filteredFaqs as $categoryIndex => $category)
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wide" style="color: var(--color-text-700);">
                        {{ $category['category'] }}
                    </h3>
                    <div class="space-y-2">
                        @foreach($category['items'] as $itemIndex => $item)
                            @php
                                $faqId = "{$categoryIndex}-{$itemIndex}";
                                $isExpanded = $expandedFaq === $faqId;
                            @endphp
                            <button
                                type="button"
                                wire:click="toggleFaq('{{ $faqId }}')"
                                class="w-full text-left border rounded-lg p-4 transition-colors"
                                style="background-color: var(--color-background-50); border-color: var(--color-background-300);"
                                onmouseover="this.style.borderColor='var(--color-glaucous)'"
                                onmouseout="this.style.borderColor='var(--color-background-300)'"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <p class="font-medium" style="color: var(--color-text-800);">{{ $item['question'] }}</p>
                                    <svg class="h-5 w-5 flex-shrink-0 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                @if($isExpanded)
                                    <p class="mt-3 text-sm leading-relaxed" style="color: var(--color-text-600);">{{ $item['answer'] }}</p>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-8">
                <p style="color: var(--color-text-600);">No results found for "{{ $searchQuery }}"</p>
            </div>
        @endif
    </div>

    {{-- CTA --}}
    <div class="border rounded-lg p-6 text-center space-y-3" style="background-color: rgba(92,128,188,0.1); border-color: rgba(92,128,188,0.3);">
        <div class="flex justify-center">
            <svg class="h-8 w-8" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <h3 class="font-semibold" style="color: var(--color-text-800);">Still need help?</h3>
        <p class="text-sm" style="color: var(--color-text-600);">
            Can't find what you're looking for? Contact our support team and we'll be happy to help.
        </p>
        <button type="button" class="px-4 py-2 rounded-lg font-medium text-white transition-opacity" style="background-color: var(--color-glaucous);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            Contact Support
        </button>
    </div>
</div>
