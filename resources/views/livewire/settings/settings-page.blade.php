<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">
            Settings
        </h1>
        <p class="mt-1" style="color: var(--color-text-600);">Manage your account preferences and settings</p>
    </div>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="p-3 rounded-lg border animate-pulse-once" style="background-color: rgba(196,214,176,0.1); border-color: var(--color-tea-green); color: var(--color-tea-green);">
            {{ session('message') }}
        </div>
    @endif

    {{-- Notifications Section --}}
    <div class="border rounded-lg overflow-hidden" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        {{-- Section Header --}}
        <div class="flex items-center gap-3 p-4 border-b" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
            <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <h2 class="font-semibold" style="color: var(--color-text-800);">Notifications</h2>
        </div>

        {{-- Settings Items --}}
        <div class="divide-y" style="border-color: var(--color-background-200);">
            <div class="flex items-center justify-between p-4 transition-colors hover:bg-[var(--color-background-100)]">
                <div class="flex-1">
                    <p class="font-medium" style="color: var(--color-text-800);">Email Notifications</p>
                    <p class="text-sm" style="color: var(--color-text-600);">Receive email updates about your projects and tasks</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleSetting('emailNotifications')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $emailNotifications ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; --tw-ring-color: var(--color-glaucous);"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                        style="transform: translateX({{ $emailNotifications ? '1.25rem' : '0.25rem' }});"
                    ></span>
                </button>
            </div>

            <div class="flex items-center justify-between p-4 transition-colors hover:bg-[var(--color-background-100)]">
                <div class="flex-1">
                    <p class="font-medium" style="color: var(--color-text-800);">Push Notifications</p>
                    <p class="text-sm" style="color: var(--color-text-600);">Get real-time notifications in your browser</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleSetting('pushNotifications')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $pushNotifications ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; --tw-ring-color: var(--color-glaucous);"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                        style="transform: translateX({{ $pushNotifications ? '1.25rem' : '0.25rem' }});"
                    ></span>
                </button>
            </div>

            <div class="flex items-center justify-between p-4 transition-colors hover:bg-[var(--color-background-100)]">
                <div class="flex-1">
                    <p class="font-medium" style="color: var(--color-text-800);">Weekly Digest</p>
                    <p class="text-sm" style="color: var(--color-text-600);">Receive a summary of your weekly activity</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleSetting('weeklyDigest')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $weeklyDigest ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; --tw-ring-color: var(--color-glaucous);"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                        style="transform: translateX({{ $weeklyDigest ? '1.25rem' : '0.25rem' }});"
                    ></span>
                </button>
            </div>

            <div class="flex items-center justify-between p-4 transition-colors hover:bg-[var(--color-background-100)]">
                <div class="flex-1">
                    <p class="font-medium" style="color: var(--color-text-800);">Task Reminders</p>
                    <p class="text-sm" style="color: var(--color-text-600);">Get notified about upcoming task deadlines</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleSetting('taskReminders')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $taskReminders ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; --tw-ring-color: var(--color-glaucous);"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                        style="transform: translateX({{ $taskReminders ? '1.25rem' : '0.25rem' }});"
                    ></span>
                </button>
            </div>

            <div class="flex items-center justify-between p-4 transition-colors hover:bg-[var(--color-background-100)]">
                <div class="flex-1">
                    <p class="font-medium" style="color: var(--color-text-800);">Project Updates</p>
                    <p class="text-sm" style="color: var(--color-text-600);">Receive notifications when projects are updated</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleSetting('projectUpdates')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $projectUpdates ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; --tw-ring-color: var(--color-glaucous);"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                        style="transform: translateX({{ $projectUpdates ? '1.25rem' : '0.25rem' }});"
                    ></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Privacy Section --}}
    <div class="border rounded-lg overflow-hidden" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        <div class="flex items-center gap-3 p-4 border-b" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
            <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <h2 class="font-semibold" style="color: var(--color-text-800);">Privacy</h2>
        </div>

        <div class="divide-y" style="border-color: var(--color-background-200);">
            <div class="flex items-center justify-between p-4 transition-colors hover:bg-[var(--color-background-100)]">
                <div class="flex-1">
                    <p class="font-medium" style="color: var(--color-text-800);">Profile Visibility</p>
                    <p class="text-sm" style="color: var(--color-text-600);">Allow others to view your profile information</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleSetting('profileVisibility')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $profileVisibility ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; --tw-ring-color: var(--color-glaucous);"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                        style="transform: translateX({{ $profileVisibility ? '1.25rem' : '0.25rem' }});"
                    ></span>
                </button>
            </div>

            <div class="flex items-center justify-between p-4 transition-colors hover:bg-[var(--color-background-100)]">
                <div class="flex-1">
                    <p class="font-medium" style="color: var(--color-text-800);">Analytics & Data Collection</p>
                    <p class="text-sm" style="color: var(--color-text-600);">Help us improve CollabFlow by sharing usage data</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleSetting('dataCollection')"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: {{ $dataCollection ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; --tw-ring-color: var(--color-glaucous);"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"
                        style="transform: translateX({{ $dataCollection ? '1.25rem' : '0.25rem' }});"
                    ></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Preferences Section --}}
    <div class="border rounded-lg overflow-hidden" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
        <div class="flex items-center gap-3 p-4 border-b" style="border-color: var(--color-background-300); background-color: var(--color-background-100);">
            <svg class="h-5 w-5" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <h2 class="font-semibold" style="color: var(--color-text-800);">Preferences</h2>
        </div>

        <div class="p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Timezone --}}
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Timezone</label>
                    <select
                        wire:model.live="timezone"
                        wire:change="updatePreference('timezone')"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                    >
                        <option value="UTC">UTC</option>
                        <option value="America/New_York">Eastern Time</option>
                        <option value="America/Chicago">Central Time</option>
                        <option value="America/Denver">Mountain Time</option>
                        <option value="America/Los_Angeles">Pacific Time</option>
                        <option value="Europe/London">London</option>
                        <option value="Europe/Paris">Paris</option>
                        <option value="Asia/Tokyo">Tokyo</option>
                    </select>
                </div>

                {{-- Language --}}
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Language</label>
                    <select
                        wire:model.live="language"
                        wire:change="updatePreference('language')"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                    >
                        <option value="en">English</option>
                        <option value="es">Español</option>
                        <option value="fr">Français</option>
                        <option value="de">Deutsch</option>
                        <option value="ja">日本語</option>
                    </select>
                </div>

                {{-- Date Format --}}
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Date Format</label>
                    <select
                        wire:model.live="dateFormat"
                        wire:change="updatePreference('dateFormat')"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                    >
                        <option value="M d, Y">Jan 1, 2025</option>
                        <option value="d/m/Y">01/01/2025</option>
                        <option value="Y-m-d">2025-01-01</option>
                        <option value="F j, Y">January 1, 2025</option>
                    </select>
                </div>

                {{-- Time Format --}}
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Time Format</label>
                    <select
                        wire:model.live="timeFormat"
                        wire:change="updatePreference('timeFormat')"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                        style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                    >
                        <option value="12h">12-hour (3:00 PM)</option>
                        <option value="24h">24-hour (15:00)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="border rounded-lg overflow-hidden" style="background-color: var(--color-background-50); border-color: rgba(235,94,85,0.3);">
        <div class="flex items-center gap-3 p-4 border-b" style="border-color: rgba(235,94,85,0.3); background-color: rgba(235,94,85,0.05);">
            <svg class="h-5 w-5" style="color: var(--color-bittersweet);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h2 class="font-semibold" style="color: var(--color-text-800);">Danger Zone</h2>
        </div>
        <div class="p-4 space-y-3">
            <p class="text-sm" style="color: var(--color-text-600);">
                Once you log out, you'll need to sign in again to access your account.
            </p>
            <button
                type="button"
                wire:click="logout"
                wire:confirm="Are you sure you want to log out?"
                class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-colors font-medium"
                style="border-color: var(--color-bittersweet); color: var(--color-bittersweet); background-color: transparent;"
                onmouseover="this.style.backgroundColor='rgba(235,94,85,0.1)'"
                onmouseout="this.style.backgroundColor='transparent'"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Log Out
            </button>
        </div>
    </div>
</div>
