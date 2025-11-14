{{-- CollabFlow Header Component --}}
<header class="fixed top-0 right-0 left-[280px] h-14 border-b bg-[var(--color-background-50)] dark:bg-[var(--color-background-200)] border-[var(--color-background-300)] z-30">
    <div class="flex h-full items-center justify-between px-6">
        {{-- Search --}}
        <div class="flex-1 max-w-md">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2" style="color: var(--color-text-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    readonly
                    @click="$dispatch('open-command-palette')"
                    placeholder="Search or ask AI..."
                    class="w-full pl-10 pr-16 py-2 text-sm rounded-lg border cursor-pointer transition-colors hover:bg-[var(--color-background-200)] focus:outline-none focus:ring-2 focus:ring-[var(--color-glaucous)] bg-[var(--color-background-100)] border-[var(--color-background-300)]"
                    style="color: var(--color-text-800);"
                />
                {{-- Keyboard shortcut hint --}}
                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-0.5">
                    <kbd class="px-1.5 py-0.5 text-xs font-medium rounded border" style="background-color: var(--color-background-200); border-color: var(--color-background-300); color: var(--color-text-500);">
                        ⌘
                    </kbd>
                    <kbd class="px-1.5 py-0.5 text-xs font-medium rounded border" style="background-color: var(--color-background-200); border-color: var(--color-background-300); color: var(--color-text-500);">
                        K
                    </kbd>
                </div>
            </div>
        </div>

        {{-- Right Section --}}
        <div class="flex items-center gap-2">
            {{-- Theme Toggle --}}
            <x-theme-toggle />

            {{-- Notifications Button --}}
            <button
                @click="$dispatch('toggle-notifications')"
                class="p-2 rounded-lg transition-colors hover:bg-[var(--color-background-200)] relative"
                style="color: var(--color-text-600);"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                {{-- Notification badge --}}
                <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-[var(--color-bittersweet)]"></span>
            </button>

            {{-- User Menu --}}
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button
                    @click="open = !open"
                    class="flex items-center gap-2 px-2 py-1 rounded-lg transition-colors hover:bg-[var(--color-background-200)]"
                >
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full object-cover border-2" style="border-color: var(--color-background-300);">
                    @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--color-glaucous)] text-white text-sm font-medium">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif
                    <span class="text-sm font-medium" style="color: var(--color-text-800);">{{ auth()->user()->name }}</span>
                    <svg class="h-4 w-4" style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- User Dropdown Menu --}}
                <div
                    x-show="open"
                    x-transition
                    class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg border bg-white dark:bg-[var(--color-background-100)] border-[var(--color-background-300)]"
                >
                    <div class="p-2 border-b border-[var(--color-background-300)]">
                        <p class="text-sm font-medium" style="color: var(--color-text-800);">My Account</p>
                    </div>
                    <div class="p-1">
                        <a href="{{ route('profile') }}" wire:navigate class="block px-3 py-2 text-sm rounded hover:bg-[var(--color-background-200)]" style="color: var(--color-text-700);">
                            Profile
                        </a>
                        <a href="{{ route('settings') }}" wire:navigate class="block px-3 py-2 text-sm rounded hover:bg-[var(--color-background-200)]" style="color: var(--color-text-700);">
                            Settings
                        </a>
                    </div>
                    <div class="p-1 border-t border-[var(--color-background-300)]">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-[var(--color-background-200)]" style="color: var(--color-text-700);">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
