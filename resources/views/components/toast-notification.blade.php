{{-- Toast Notification Component --}}
@props(['type' => 'success', 'message' => '', 'duration' => 5000])

<div x-data="{
    show: false,
    message: '',
    type: 'success',
    timeout: null,
    init() {
        // Listen for toast events
        window.addEventListener('show-toast', (event) => {
            this.showToast(event.detail.message, event.detail.type || 'success', event.detail.duration || 5000);
        });
    },
    showToast(message, type, duration) {
        this.message = message;
        this.type = type;
        this.show = true;

        // Clear existing timeout
        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        // Auto-hide after duration
        this.timeout = setTimeout(() => {
            this.show = false;
        }, duration);
    },
    hideToast() {
        this.show = false;
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
    }
}"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-6 right-6 z-50 max-w-md"
    style="display: none;"
    role="alert"
    aria-live="polite">

    <div class="flex items-start gap-3 p-4 rounded-lg border shadow-lg"
        :style="{
            'background-color': type === 'success' ? 'var(--color-success-50)' :
                               type === 'error' ? 'rgba(235, 94, 85, 0.1)' :
                               type === 'warning' ? 'rgba(255, 159, 28, 0.1)' :
                               'var(--color-accent-50)',
            'border-color': type === 'success' ? 'var(--color-tea-green)' :
                          type === 'error' ? 'var(--color-bittersweet)' :
                          type === 'warning' ? 'var(--color-orange-peel)' :
                          'var(--color-glaucous)'
        }">

        {{-- Icon --}}
        <div class="flex-shrink-0">
            {{-- Success Icon --}}
            <svg x-show="type === 'success'" class="w-5 h-5" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            {{-- Error Icon --}}
            <svg x-show="type === 'error'" class="w-5 h-5" style="color: var(--color-bittersweet);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            {{-- Warning Icon --}}
            <svg x-show="type === 'warning'" class="w-5 h-5" style="color: var(--color-orange-peel);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>

            {{-- Info Icon --}}
            <svg x-show="type === 'info'" class="w-5 h-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        {{-- Message --}}
        <p class="flex-1 text-sm font-medium"
            :style="{ color: type === 'success' ? 'var(--color-text-900)' :
                           type === 'error' ? 'var(--color-bittersweet)' :
                           type === 'warning' ? 'var(--color-orange-peel)' :
                           'var(--color-glaucous)' }"
            x-text="message"></p>

        {{-- Close Button --}}
        <button @click="hideToast()"
            class="flex-shrink-0 p-0.5 rounded hover:bg-black hover:bg-opacity-10 transition-colors"
            :style="{ color: type === 'success' ? 'var(--color-tea-green)' :
                           type === 'error' ? 'var(--color-bittersweet)' :
                           type === 'warning' ? 'var(--color-orange-peel)' :
                           'var(--color-glaucous)' }"
            aria-label="Close notification">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
