<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">
                My Profile
            </h1>
            <p class="mt-1" style="color: var(--color-text-600);">Manage your account information</p>
        </div>
        @if($isEditing)
            <button
                type="button"
                wire:click="cancelEditing"
                class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-colors"
                style="border-color: var(--color-background-300); color: var(--color-text-600); background-color: transparent;"
                onmouseover="this.style.backgroundColor='var(--color-background-100)'"
                onmouseout="this.style.backgroundColor='transparent'"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
            </button>
        @else
            <button
                type="button"
                wire:click="startEditing"
                class="flex items-center gap-2 px-4 py-2 rounded-lg transition-opacity text-white"
                style="background-color: var(--color-glaucous);"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Profile
            </button>
        @endif
    </div>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="p-3 rounded-lg border" style="background-color: rgba(196,214,176,0.1); border-color: var(--color-tea-green); color: var(--color-tea-green);">
            {{ session('message') }}
        </div>
    @endif

    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Sidebar - Avatar & Stats --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Avatar Card --}}
            <div class="border rounded-lg p-6 space-y-4" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                <h3 class="font-semibold text-sm uppercase tracking-wide" style="color: var(--color-text-600);">Profile Photo</h3>

                <div class="flex flex-col items-center space-y-4">
                    {{-- Avatar Display --}}
                    @if($avatar)
                        <img src="{{ Storage::url($avatar) }}" alt="Profile Avatar" class="h-32 w-32 rounded-full object-cover border-4" style="border-color: var(--color-background-200);">
                    @elseif($newAvatar)
                        <img src="{{ $newAvatar->temporaryUrl() }}" alt="Preview" class="h-32 w-32 rounded-full object-cover border-4" style="border-color: var(--color-glaucous);">
                    @else
                        <div class="h-32 w-32 rounded-full flex items-center justify-center border-4" style="background-color: rgba(92,128,188,0.1); border-color: var(--color-background-200);">
                            <span class="text-4xl font-bold" style="color: var(--color-glaucous);">{{ auth()->user()->initials() }}</span>
                        </div>
                    @endif

                    {{-- Avatar Actions --}}
                    @if($isEditing)
                        <div class="w-full space-y-2">
                            <label class="block">
                                <input type="file" wire:model="newAvatar" accept="image/*" class="hidden" />
                                <span class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg border cursor-pointer transition-colors text-sm"
                                      style="border-color: var(--color-background-300); color: var(--color-text-700); background-color: transparent;"
                                      onmouseover="this.style.backgroundColor='var(--color-background-100)'"
                                      onmouseout="this.style.backgroundColor='transparent'">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $avatar || $newAvatar ? 'Change Photo' : 'Upload Photo' }}
                                </span>
                            </label>
                            @if($avatar)
                                <button
                                    type="button"
                                    wire:click="removeAvatar"
                                    wire:confirm="Remove your profile photo?"
                                    class="w-full px-4 py-2 rounded-lg text-sm transition-colors"
                                    style="color: var(--color-bittersweet); background-color: rgba(235,94,85,0.1);"
                                    onmouseover="this.style.backgroundColor='rgba(235,94,85,0.2)'"
                                    onmouseout="this.style.backgroundColor='rgba(235,94,85,0.1)'"
                                >
                                    Remove Photo
                                </button>
                            @endif
                            @error('newAvatar')
                                <span class="text-xs block text-center" style="color: var(--color-bittersweet);">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-center" style="color: var(--color-text-500);">JPG, PNG or GIF. Max 2MB</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stats Card --}}
            <div class="border rounded-lg p-6 space-y-4" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                <h3 class="font-semibold text-sm uppercase tracking-wide" style="color: var(--color-text-600);">Quick Stats</h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm" style="color: var(--color-text-600);">Projects</span>
                        <span class="font-bold" style="color: var(--color-text-900);">{{ auth()->user()->projects()->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm" style="color: var(--color-text-600);">Member Since</span>
                        <span class="font-semibold text-sm" style="color: var(--color-text-800);">{{ $joinDate }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Content - Profile Details --}}
        <div class="lg:col-span-2">
            <div class="border rounded-lg p-8 space-y-6" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                <h3 class="font-semibold text-lg" style="color: var(--color-text-900);">Personal Information</h3>

                {{-- Profile Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Full Name</label>
                        @if($isEditing)
                            <input
                                type="text"
                                wire:model="editName"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            />
                            @error('editName') <span class="text-sm" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        @else
                            <p class="text-lg" style="color: var(--color-text-800);">{{ $name }}</p>
                        @endif
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--color-text-700);">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Email
                        </label>
                        @if($isEditing)
                            <input
                                type="email"
                                wire:model="editEmail"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            />
                            @error('editEmail') <span class="text-sm" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        @else
                            <p style="color: var(--color-text-800);">{{ $email }}</p>
                        @endif
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--color-text-700);">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Phone
                        </label>
                        @if($isEditing)
                            <input
                                type="text"
                                wire:model="editPhone"
                                placeholder="Enter phone number"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            />
                            @error('editPhone') <span class="text-sm" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        @else
                            <p style="color: var(--color-text-800);">{{ $phone ?: 'Not set' }}</p>
                        @endif
                    </div>

                    {{-- Location --}}
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 text-sm font-medium mb-2" style="color: var(--color-text-700);">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Location
                        </label>
                        @if($isEditing)
                            <input
                                type="text"
                                wire:model="editLocation"
                                placeholder="City, Country"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            />
                            @error('editLocation') <span class="text-sm" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        @else
                            <p style="color: var(--color-text-800);">{{ $location ?: 'Not set' }}</p>
                        @endif
                    </div>

                    {{-- Bio --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" style="color: var(--color-text-700);">Bio</label>
                        @if($isEditing)
                            <textarea
                                wire:model="editBio"
                                rows="4"
                                placeholder="Tell us about yourself..."
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                                style="background-color: var(--color-background-100); border-color: var(--color-background-300); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                            ></textarea>
                            @error('editBio') <span class="text-sm" style="color: var(--color-bittersweet);">{{ $message }}</span> @enderror
                        @else
                            <p style="color: var(--color-text-800);">{{ $bio ?: 'No bio yet' }}</p>
                        @endif
                    </div>
                </div>

                {{-- Save Button --}}
                @if($isEditing)
                    <div class="flex gap-3 pt-6 border-t" style="border-color: var(--color-background-300);">
                        <button
                            type="button"
                            wire:click="saveProfile"
                            class="flex items-center gap-2 px-6 py-2.5 rounded-lg transition-opacity text-white font-medium"
                            style="background-color: var(--color-glaucous);"
                            onmouseover="this.style.opacity='0.9'"
                            onmouseout="this.style.opacity='1'"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Save Changes
                        </button>
                        <button
                            type="button"
                            wire:click="cancelEditing"
                            class="px-6 py-2.5 rounded-lg border transition-colors font-medium"
                            style="border-color: var(--color-background-300); color: var(--color-text-600); background-color: transparent;"
                            onmouseover="this.style.backgroundColor='var(--color-background-100)'"
                            onmouseout="this.style.backgroundColor='transparent'"
                        >
                            Cancel
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
