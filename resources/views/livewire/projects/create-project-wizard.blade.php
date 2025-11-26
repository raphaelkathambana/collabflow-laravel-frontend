<div class="space-y-8 py-8">
    {{-- Toast Notification Component --}}
    <x-toast-notification />
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        /* Split-view responsive layout */
        .split-view-container {
            display: flex;
            gap: 1rem;
        }

        @media (max-width: 1023px) {
            .split-view-container {
                flex-direction: column;
            }
            .task-list-panel,
            .flowchart-panel {
                width: 100% !important;
            }
        }

        @media (min-width: 1024px) {
            .split-view-container {
                flex-direction: row;
                min-height: 500px;
            }
            .task-list-panel {
                width: 40%;
            }
            .flowchart-panel {
                width: 60%;
            }
        }

        /* Task list scrollbar styling */
        .task-list-panel .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        .task-list-panel .overflow-y-auto::-webkit-scrollbar-track {
            background: var(--color-background-100);
            border-radius: 3px;
        }
        .task-list-panel .overflow-y-auto::-webkit-scrollbar-thumb {
            background: var(--color-background-300);
            border-radius: 3px;
        }
        .task-list-panel .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: var(--color-glaucous);
        }

        /* Line clamp utility for task descriptions */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Shimmer animation for loading tasks */
        @keyframes shimmer {
            0% {
                background-position: -468px 0;
            }
            100% {
                background-position: 468px 0;
            }
        }

        .shimmer {
            animation: shimmer 1.5s infinite;
            background: linear-gradient(to right, var(--color-background-100) 0%, var(--color-background-200) 20%, var(--color-background-100) 40%, var(--color-background-100) 100%);
            background-size: 800px 104px;
        }

        .shimmer-line {
            height: 12px;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .shimmer-line.short {
            width: 60%;
        }
    </style>
    {{-- Error Notification --}}
    @if ($showError && $errorMessage)
        <div class="fixed top-4 right-4 z-50 max-w-md animate-fade-in" wire:key="error-notification">
            <div class="flex items-start gap-3 p-4 rounded-lg shadow-lg border-2"
                style="background-color: var(--color-background-50); border-color: var(--color-bittersweet);">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: var(--color-bittersweet);" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium" style="color: var(--color-bittersweet);">{{ $errorMessage }}</p>
                </div>
                <button wire:click="clearError" class="flex-shrink-0 p-1 rounded hover:bg-red-100"
                    style="color: var(--color-bittersweet);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Step Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-between max-w-3xl mx-auto">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                    {{-- Step Circle --}}
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all"
                            style="{{ $currentStep >= $i ? 'background-color: var(--color-glaucous); color: white;' : 'background-color: var(--color-background-300); color: var(--color-text-500);' }}">
                            {{ $i }}
                        </div>
                        <span class="mt-2 text-xs font-medium"
                            style="color: {{ $currentStep >= $i ? 'var(--color-text-900)' : 'var(--color-text-500)' }};">
                            @if ($i === 1)
                                Details
                            @elseif($i === 2)
                                Goals
                            @elseif($i === 3)
                                Generate Tasks
                            @elseif($i === 4)
                                Workflow
                            @else
                                Review
                            @endif
                        </span>
                    </div>

                    {{-- Connecting Line --}}
                    @if ($i < $totalSteps)
                        <div class="flex-1 h-1 mx-2"
                            style="background-color: {{ $currentStep > $i ? 'var(--color-glaucous)' : 'var(--color-background-300)' }};">
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- Wizard Content --}}
    <div class="max-w-4xl mx-auto">
        <div class="p-8 rounded-lg border shadow-md relative"
            style="background-color: var(--color-background-50); border-color: var(--color-background-300);">

            {{-- Loading Overlay --}}
            <div wire:loading wire:target="nextStep,previousStep,createProject"
                class="absolute inset-0 backdrop-blur-sm rounded-lg flex items-center justify-center z-40"
                style="background-color: rgba(var(--color-background-50-rgb, 255, 255, 255), 0.8);">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2"
                        style="border-color: var(--color-glaucous);"></div>
                    <p class="mt-4 font-medium" style="color: var(--color-text-700);">Processing...</p>
                </div>
            </div>

            {{-- Step 1: Project Details --}}
            @if ($currentStep === 1)
                <div class="space-y-6">
                    <div>
                        <h3 class="text-2xl font-bold" style="color: var(--color-text-800); font-family: Tahoma;">
                            Step 1: Project Details
                        </h3>
                        <p class="mt-2 text-sm" style="color: var(--color-text-600);">
                            Tell us about your project
                        </p>
                    </div>

                    {{-- AI Hint --}}
                    <div class="flex items-start gap-3 p-4 rounded-lg border"
                        style="border-color: var(--color-accent-200); background-color: var(--color-accent-50);">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: var(--color-glaucous);" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                            </path>
                        </svg>
                        <p class="text-sm" style="color: var(--color-text-600);">
                            Our AI will use this information to generate a comprehensive task breakdown tailored to your
                            project needs.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="project-name" class="text-sm font-medium" style="color: var(--color-text-700);">
                                Project Name <span style="color: var(--color-bittersweet);">*</span>
                            </label>
                            <input type="text" id="project-name" wire:model.live="name"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-100);"
                                placeholder="e.g., Mobile App Launch"
                                maxlength="100"
                                aria-required="true"
                                aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}">
                            @error('name')
                                <span id="name-error" class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-right" style="color: var(--color-text-500);">{{ strlen($name) }}/100</p>
                        </div>

                        <div class="space-y-2">
                            <label for="project-description" class="text-sm font-medium" style="color: var(--color-text-700);">
                                Description <span style="color: var(--color-bittersweet);">*</span>
                            </label>
                            <textarea id="project-description" wire:model.live="description" rows="4"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-100);"
                                placeholder="Brief description of what you're building..."
                                maxlength="500"
                                aria-required="true"
                                aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('description') ? 'description-error' : '' }}"></textarea>
                            @error('description')
                                <span id="description-error" class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-right" style="color: var(--color-text-500);">{{ strlen($description) }}/500</p>
                        </div>

                        {{-- Domain and Deadline Row --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="project-domain" class="text-sm font-medium" style="color: var(--color-text-700);">
                                    Domain <span style="color: var(--color-bittersweet);">*</span>
                                </label>
                                <select id="project-domain" wire:model="domain"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-100);"
                                    aria-required="true"
                                    aria-invalid="{{ $errors->has('domain') ? 'true' : 'false' }}"
                                    aria-describedby="{{ $errors->has('domain') ? 'domain-error' : '' }}">
                                    <option value="">Select project type...</option>
                                    <option value="software_development">Software Development</option>
                                    <option value="research_analysis">Research & Analysis</option>
                                    <option value="marketing_campaign">Marketing Campaign</option>
                                    <option value="custom">Custom Project</option>
                                </select>
                                @error('domain')
                                    <span id="domain-error" class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @enderror
                                <p class="text-xs" style="color: var(--color-text-500);">Helps AI generate relevant tasks</p>
                            </div>

                            <div class="space-y-2">
                                <label for="project-end-date" class="text-sm font-medium" style="color: var(--color-text-700);">
                                    Deadline <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                                </label>
                                <input type="date" id="project-end-date" wire:model="end_date"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-100);"
                                    min="{{ date('Y-m-d') }}"
                                    aria-invalid="{{ $errors->has('end_date') ? 'true' : 'false' }}"
                                    aria-describedby="{{ $errors->has('end_date') ? 'end-date-error' : '' }}">
                                @error('end_date')
                                    <span id="end-date-error" class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="project-team-size" class="text-sm font-medium" style="color: var(--color-text-700);">
                                Team Size <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                            </label>
                            <input type="number" id="project-team-size" wire:model="team_size"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-100);"
                                placeholder="How many people working on this?"
                                min="1"
                                max="1000"
                                aria-invalid="{{ $errors->has('team_size') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('team_size') ? 'team-size-error' : '' }}">
                            @error('team_size')
                                <span id="team-size-error" class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 2: Goals & Context (Redesigned with Goal Cards) --}}
            @if ($currentStep === 2)
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold" style="color: var(--color-text-800); font-family: Tahoma;">What do you want to achieve?</h3>
                        <p class="mt-2" style="color: var(--color-text-600); font-family: Montserrat;">
                            Define your project goals with priorities. Our AI will help structure everything for you.
                        </p>
                    </div>

                    {{-- Goals Section (Multiple Goal Cards) --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                Project Goals <span style="color: var(--color-bittersweet);">*</span>
                            </label>
                            <button type="button" wire:click="addGoal"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors hover:opacity-80"
                                style="background-color: var(--color-glaucous); color: white;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Goal
                            </button>
                        </div>

                        {{-- Goal Cards --}}
                        <div class="space-y-4">
                            @foreach($goals as $index => $goal)
                                <div class="p-4 rounded-lg border space-y-3"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50);">

                                    {{-- Goal Header with Priority and Remove --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium text-white"
                                                style="background-color: var(--color-glaucous);">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="text-sm font-semibold" style="color: var(--color-text-700);">Goal {{ $index + 1 }}</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            {{-- Priority Dropdown --}}
                                            <select wire:model="goals.{{ $index }}.priority"
                                                class="px-2 py-1 text-xs rounded border focus:outline-none focus:ring-2"
                                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-700); --tw-ring-color: rgba(var(--glaucous), 0.2);">
                                                <option value="high">High Priority</option>
                                                <option value="medium">Medium Priority</option>
                                                <option value="low">Low Priority</option>
                                            </select>

                                            {{-- Remove Button (only show if more than 1 goal) --}}
                                            @if(count($goals) > 1)
                                                <button type="button"
                                                    wire:click="removeGoal({{ $goal['id'] }})"
                                                    class="p-1 hover:opacity-80 flex-shrink-0"
                                                    style="color: var(--color-bittersweet);"
                                                    aria-label="Remove goal">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Goal Title --}}
                                    <div class="space-y-1">
                                        <input type="text"
                                            wire:model.live="goals.{{ $index }}.title"
                                            class="w-full px-3 py-2 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                            style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-100);"
                                            placeholder="Goal title (e.g., Increase user engagement)"
                                            maxlength="100"
                                            aria-required="true">
                                        @error("goals.{$index}.title")
                                            <span class="text-xs" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                        @enderror
                                        <p class="text-xs text-right" style="color: var(--color-text-500);">{{ strlen($goal['title'] ?? '') }}/100</p>
                                    </div>

                                    {{-- Goal Description --}}
                                    <div class="space-y-1">
                                        <textarea
                                            wire:model.live="goals.{{ $index }}.description"
                                            rows="3"
                                            class="w-full px-3 py-2 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                            style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-accent-100);"
                                            placeholder="Describe this goal in detail..."
                                            maxlength="500"
                                            aria-required="true"></textarea>
                                        @error("goals.{$index}.description")
                                            <span class="text-xs" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                        @enderror
                                        <p class="text-xs text-right" style="color: var(--color-text-500);">{{ strlen($goal['description'] ?? '') }}/500</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- AI Hint Callout --}}
                        <div class="flex items-start gap-3 p-4 rounded-lg border"
                            style="border-color: var(--color-accent-200); background-color: var(--color-accent-50);">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: var(--color-glaucous);" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                                </path>
                            </svg>
                            <p class="text-sm" style="color: var(--color-text-600); font-family: Montserrat;">
                                Our AI will break down your goals into actionable tasks and suggest measurements for you.
                            </p>
                        </div>
                    </div>

                    {{-- Reference Documents Section (NEW) --}}
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                Reference Documents <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                            </label>
                            <p class="text-xs mt-1" style="color: var(--color-text-500); font-family: Montserrat;">
                                Upload documents for AI to learn from (specs, requirements, examples)
                            </p>
                        </div>

                        {{-- File Upload Dropzone --}}
                        <div class="border-2 border-dashed rounded-lg p-6 transition-colors hover:border-glaucous"
                            style="border-color: var(--color-background-300); background-color: var(--color-background-50);"
                            x-data="{ isDragging: false }"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $wire.uploadMultiple('referenceDocuments', $refs.fileInput.files)"
                            :style="isDragging ? 'border-color: var(--color-glaucous); background-color: var(--color-accent-50);' : ''">

                            <input type="file"
                                x-ref="fileInput"
                                wire:model="referenceDocuments"
                                multiple
                                accept=".pdf,.doc,.docx,.txt,.md"
                                class="hidden"
                                id="file-upload">

                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 mb-3" style="color: var(--color-text-400);" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <label for="file-upload" class="cursor-pointer">
                                    <span class="text-sm font-medium" style="color: var(--color-glaucous);">Click to upload</span>
                                    <span class="text-sm" style="color: var(--color-text-600);"> or drag and drop</span>
                                </label>
                                <p class="text-xs mt-1" style="color: var(--color-text-500);">
                                    PDF, DOC, DOCX, TXT, MD up to 10MB each
                                </p>
                            </div>

                            {{-- Upload Progress --}}
                            <div wire:loading wire:target="referenceDocuments" class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-glaucous h-2 rounded-full animate-pulse" style="width: 50%; background-color: var(--color-glaucous);"></div>
                                </div>
                                <p class="text-xs text-center mt-2" style="color: var(--color-text-600);">Uploading...</p>
                            </div>
                        </div>

                        {{-- Uploaded Files List --}}
                        @if(!empty($referenceDocuments))
                            <div class="space-y-2">
                                <p class="text-sm font-medium" style="color: var(--color-text-700);">Uploaded Files</p>
                                @foreach($referenceDocuments as $index => $file)
                                    <div class="flex items-center justify-between p-3 rounded-lg border"
                                        style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            {{-- File Icon --}}
                                            <svg class="w-5 h-5 flex-shrink-0" style="color: var(--color-glaucous);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>

                                            {{-- File Info --}}
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium truncate" style="color: var(--color-text-900);">
                                                    {{ $file->getClientOriginalName() }}
                                                </p>
                                                <p class="text-xs" style="color: var(--color-text-500);">
                                                    {{ number_format($file->getSize() / 1024, 2) }} KB
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Remove Button --}}
                                        <button type="button"
                                            wire:click="removeFile({{ $index }})"
                                            class="p-1 rounded hover:bg-red-100 flex-shrink-0"
                                            style="color: var(--color-bittersweet);"
                                            aria-label="Remove file">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Collapsible Optional Fields Section --}}
                        <div class="space-y-3" x-data="{ expanded: @entangle('optionalFieldsExpanded') }">
                            {{-- Toggle Button --}}
                            <button type="button"
                                @click="expanded = !expanded"
                                class="flex items-center justify-between w-full p-3 rounded-lg border transition-all hover:bg-opacity-50"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50);">
                                <span class="text-sm font-medium" style="color: var(--color-text-700);">
                                    Optional Fields (Success Metrics & Constraints)
                                </span>
                                <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': expanded }"
                                    style="color: var(--color-text-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            {{-- Collapsible Content --}}
                            <div x-show="expanded"
                                x-collapse
                                class="space-y-4 pt-2">

                                {{-- Success Metrics --}}
                                <div class="space-y-2">
                                    <label for="success-metrics" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                        Success Metrics <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                                    </label>
                                    <textarea id="success-metrics" wire:model="successMetrics" rows="3"
                                        class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                        style="border-color: var(--color-background-300); background-color: white; color: var(--color-text-900); --tw-ring-color: var(--color-glaucous); font-family: Montserrat;"
                                        placeholder="How will you measure success? e.g., 10k users in first month, 4.5+ star rating"
                                        maxlength="1000"></textarea>
                                    <div class="flex justify-between items-center">
                                        @error('successMetrics')
                                            <span class="text-xs" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                        @else
                                            <span></span>
                                        @enderror
                                        <span class="text-xs" style="color: var(--color-text-500);">{{ strlen($successMetrics) }}/1000</span>
                                    </div>
                                </div>

                                {{-- Constraints --}}
                                <div class="space-y-2">
                                    <label for="constraints" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                        Constraints <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                                    </label>
                                    <textarea id="constraints" wire:model="constraints" rows="3"
                                        class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                        style="border-color: var(--color-background-300); background-color: white; color: var(--color-text-900); --tw-ring-color: var(--color-glaucous); font-family: Montserrat;"
                                        placeholder="Any limitations? e.g., Budget: $10k, Must use Python, Timeline: 3 months"
                                        maxlength="1000"></textarea>
                                    <div class="flex justify-between items-center">
                                        @error('constraints')
                                            <span class="text-xs" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                        @else
                                            <span></span>
                                        @enderror
                                        <span class="text-xs" style="color: var(--color-text-500);">{{ strlen($constraints) }}/1000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 3: Generate Tasks with Split-View Layout --}}
            @if ($currentStep === 3)
                <div
                    @if ($isGenerating && empty($tasks))
                        wire:poll.3s="checkGenerationStatus"
                    @endif
                >
                    <h2 class="text-2xl font-bold mb-2" style="color: var(--color-text-900); font-family: Tahoma;">
                        Generate Tasks</h2>
                    <p class="mb-6" style="color: var(--color-text-600);">AI-generated tasks based on your project details</p>

                    @if ($isGenerating)
                        {{-- Streaming Animation --}}
                        <div class="flex flex-col items-center justify-center py-12 space-y-8"
                            x-data="{
                                currentStep: @entangle('currentStreamingStep').live,
                                init() {
                                    // Initialize to 1 if undefined
                                    if (this.currentStep === undefined || this.currentStep === null) {
                                        this.currentStep = 1;
                                    }
                                    // No fake timer - let the backend control progress via wire:poll
                                    console.log('Loading animation started, currentStreamingStep:', this.currentStep);

                                    // Listen for task streaming start event
                                    window.addEventListener('start-task-streaming', (event) => {
                                        const totalTasks = event.detail[0].totalTasks;
                                        this.startTaskStreaming(totalTasks);
                                    });
                                },
                                startTaskStreaming(totalTasks) {
                                    // Complete tasks one by one with delays
                                    let completedCount = 0;
                                    const completeInterval = setInterval(() => {
                                        if (completedCount < totalTasks) {
                                            $wire.call('completeTask', completedCount);
                                            completedCount++;
                                        } else {
                                            clearInterval(completeInterval);
                                        }
                                    }, 800); // Complete one task every 800ms
                                }
                            }"
                            @start-task-streaming.window="startTaskStreaming($event.detail[0].totalTasks)">

                            {{-- CF Loading Logo Animation --}}
                            <div class="relative">
                                <x-cf-logo size="xlarge" :animated="true" class="text-[var(--color-glaucous)]" />
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="h-40 w-40 rounded-full border-4 border-transparent border-t-[var(--color-bittersweet)] animate-spin"></div>
                                </div>
                            </div>

                            {{-- Status Text --}}
                            <div class="text-center space-y-2">
                                <h3 class="text-xl font-bold" style="color: var(--color-text-900); font-family: Tahoma;">
                                    <span x-show="currentStep < 4">AI is analyzing your project...</span>
                                    <span x-show="currentStep === 4">Tasks Generated Successfully!</span>
                                </h3>
                                <p class="text-sm" style="color: var(--color-text-600);">
                                    <span x-show="currentStep < 4">Generating tasks based on your goals and industry best practices</span>
                                    <span x-show="currentStep === 4">Loading your workflow...</span>
                                </p>
                            </div>

                            {{-- Loading Steps --}}
                            <div class="w-full max-w-md space-y-3">
                                {{-- Step 1: Analyzing project context --}}
                                <div class="flex items-center gap-3 rounded-lg border p-4 transition-all duration-300"
                                    :class="{
                                        'border-tea-green bg-opacity-10': currentStep > 1,
                                        'border-glaucous': currentStep === 1,
                                        'border-background-300': currentStep < 1
                                    }"
                                    :style="currentStep > 1 ? 'background-color: rgba(var(--color-tea-green-rgb, 96, 165, 137), 0.1); border-color: var(--color-tea-green);' :
                                            currentStep === 1 ? 'background-color: var(--color-accent-50); border-color: var(--color-glaucous);' :
                                            'background-color: var(--color-background-50); border-color: var(--color-background-300);'">
                                    <svg x-show="currentStep > 1" class="w-5 h-5 flex-shrink-0" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg x-show="currentStep === 1" class="w-5 h-5 flex-shrink-0 animate-spin" style="color: var(--color-glaucous);" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="currentStep < 1" class="w-5 h-5 flex-shrink-0" style="color: var(--color-text-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                    </svg>
                                    <span class="text-sm font-medium" :style="currentStep >= 1 ? 'color: var(--color-text-900);' : 'color: var(--color-text-500);'">
                                        Analyzing project context
                                    </span>
                                </div>

                                {{-- Step 2: Uploading project documents (conditionally shown) --}}
                                @php
                                    $showDocumentUpload = !empty($referenceDocuments);
                                @endphp
                                @if($showDocumentUpload)
                                <div class="flex items-center gap-3 rounded-lg border p-4 transition-all duration-300"
                                    :class="{
                                        'border-tea-green': currentStep > 2,
                                        'border-glaucous': currentStep === 2,
                                        'border-background-300': currentStep < 2
                                    }"
                                    :style="currentStep > 2 ? 'background-color: rgba(var(--color-tea-green-rgb, 96, 165, 137), 0.1); border-color: var(--color-tea-green);' :
                                            currentStep === 2 ? 'background-color: var(--color-accent-50); border-color: var(--color-glaucous);' :
                                            'background-color: var(--color-background-50); border-color: var(--color-background-300);'">
                                    <svg x-show="currentStep > 2" class="w-5 h-5 flex-shrink-0" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg x-show="currentStep === 2" class="w-5 h-5 flex-shrink-0 animate-spin" style="color: var(--color-glaucous);" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="currentStep < 2" class="w-5 h-5 flex-shrink-0" style="color: var(--color-text-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                    </svg>
                                    <span class="text-sm font-medium" :style="currentStep >= 2 ? 'color: var(--color-text-900);' : 'color: var(--color-text-500);'">
                                        Uploading documents to knowledge base
                                    </span>
                                </div>
                                @endif

                                {{-- Step 3 (was 2): Generating task breakdown --}}
                                @php
                                    $taskBreakdownStep = !empty($referenceDocuments) ? 3 : 2;
                                @endphp
                                <div class="flex items-center gap-3 rounded-lg border p-4 transition-all duration-300"
                                    :class="{
                                        'border-tea-green': currentStep > {{ $taskBreakdownStep }},
                                        'border-glaucous': currentStep === {{ $taskBreakdownStep }},
                                        'border-background-300': currentStep < {{ $taskBreakdownStep }}
                                    }"
                                    :style="currentStep > {{ $taskBreakdownStep }} ? 'background-color: rgba(var(--color-tea-green-rgb, 96, 165, 137), 0.1); border-color: var(--color-tea-green);' :
                                            currentStep === {{ $taskBreakdownStep }} ? 'background-color: var(--color-accent-50); border-color: var(--color-glaucous);' :
                                            'background-color: var(--color-background-50); border-color: var(--color-background-300);'">
                                    <svg x-show="currentStep > {{ $taskBreakdownStep }}" class="w-5 h-5 flex-shrink-0" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg x-show="currentStep === {{ $taskBreakdownStep }}" class="w-5 h-5 flex-shrink-0 animate-spin" style="color: var(--color-glaucous);" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="currentStep < {{ $taskBreakdownStep }}" class="w-5 h-5 flex-shrink-0" style="color: var(--color-text-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                    </svg>
                                    <span class="text-sm font-medium" :style="currentStep >= {{ $taskBreakdownStep }} ? 'color: var(--color-text-900);' : 'color: var(--color-text-500);'">
                                        Generating task breakdown
                                    </span>
                                </div>

                                {{-- Step 4 (was 3): Estimating effort --}}
                                @php
                                    $estimatingEffortStep = !empty($referenceDocuments) ? 4 : 3;
                                @endphp
                                <div class="flex items-center gap-3 rounded-lg border p-4 transition-all duration-300"
                                    :class="{
                                        'border-tea-green': currentStep > {{ $estimatingEffortStep }},
                                        'border-glaucous': currentStep === {{ $estimatingEffortStep }},
                                        'border-background-300': currentStep < {{ $estimatingEffortStep }}
                                    }"
                                    :style="currentStep > {{ $estimatingEffortStep }} ? 'background-color: rgba(var(--color-tea-green-rgb, 96, 165, 137), 0.1); border-color: var(--color-tea-green);' :
                                            currentStep === {{ $estimatingEffortStep }} ? 'background-color: var(--color-accent-50); border-color: var(--color-glaucous);' :
                                            'background-color: var(--color-background-50); border-color: var(--color-background-300);'">
                                    <svg x-show="currentStep > {{ $estimatingEffortStep }}" class="w-5 h-5 flex-shrink-0" style="color: var(--color-tea-green);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg x-show="currentStep === {{ $estimatingEffortStep }}" class="w-5 h-5 flex-shrink-0 animate-spin" style="color: var(--color-glaucous);" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="currentStep < {{ $estimatingEffortStep }}" class="w-5 h-5 flex-shrink-0" style="color: var(--color-text-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                    </svg>
                                    <span class="text-sm font-medium" :style="currentStep >= {{ $estimatingEffortStep }} ? 'color: var(--color-text-900);' : 'color: var(--color-text-500);'">
                                        Estimating effort
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Split-View Container: 40% Task List | 60% Flowchart --}}
                        <div class="split-view-container flex flex-col lg:flex-row gap-4"
                            x-data="{ selectedTaskId: null }"
                            @flowchart-node-clicked.window="selectedTaskId = $event.detail.taskId">
                            {{-- LEFT PANEL: Task List (40%) --}}
                            <div class="task-list-panel lg:w-2/5 flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold" style="color: var(--color-text-900); font-family: Montserrat;">
                                        Tasks ({{ count($tasks) }})
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="openAddTaskModal"
                                            class="px-3 py-1.5 rounded-lg border transition-colors text-sm flex items-center gap-2 hover:bg-opacity-10"
                                            style="border-color: var(--color-tea-green); color: var(--color-tea-green);">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Task
                                        </button>
                                        <button wire:click="generateTasks"
                                            class="px-3 py-1.5 rounded-lg border transition-colors text-sm flex items-center gap-2 hover:bg-opacity-10"
                                            style="border-color: var(--color-glaucous); color: var(--color-glaucous);">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            Regenerate
                                        </button>
                                    </div>
                                </div>

                                {{-- Scrollable Task List --}}
                                <div class="space-y-2 overflow-y-auto flex-1" style="max-height: 500px;">
                                    @forelse ($tasks as $index => $task)
                                        @php
                                            // Detect if this task has checkpoints
                                            $hasCheckpoints = false;
                                            $checkpointCount = 0;
                                            if (!empty($task['subtasks'])) {
                                                foreach ($task['subtasks'] as $subtask) {
                                                    if (isset($subtask['is_checkpoint']) && $subtask['is_checkpoint'] === true) {
                                                        $hasCheckpoints = true;
                                                        $checkpointCount++;
                                                    }
                                                }
                                            }
                                            $taskCardClass = '';
                                            if ($hasCheckpoints) {
                                                $taskCardClass = 'task-card--checkpoint';
                                            } elseif ($task['type'] === 'hitl') {
                                                $taskCardClass = 'task-card--hitl';
                                            } elseif ($task['type'] === 'ai') {
                                                $taskCardClass = 'task-card--ai';
                                            } elseif ($task['type'] === 'human') {
                                                $taskCardClass = 'task-card--human';
                                            }
                                        @endphp
                                        <div class="flex items-start justify-between p-3 rounded-lg border hover:shadow-sm transition-all cursor-pointer {{ $taskCardClass }}"
                                            style="background-color: var(--color-background-50); border-color: var(--color-background-300); position: relative;"
                                            data-task-id="{{ $task['id'] ?? '' }}"
                                            :style="selectedTaskId === '{{ $task['id'] ?? '' }}' ? 'border-color: var(--color-glaucous); background-color: var(--color-accent-50); box-shadow: 0 0 0 2px var(--color-glaucous);' : ''"
                                            @click="selectedTaskId = '{{ $task['id'] ?? '' }}'; $dispatch('task-list-item-clicked', { taskId: '{{ $task['id'] ?? '' }}' })"
                                            @dblclick="$wire.openTaskEditModal({{ $index }})"
                                            title="Double-click to edit task">

                                            {{-- Checkpoint Indicator Badge --}}
                                            @if($hasCheckpoints)
                                                <div class="checkpoint-indicator"></div>
                                            @endif

                                            @if(isset($task['status']) && $task['status'] === 'generating')
                                                {{-- Shimmer Loader for Generating Tasks --}}
                                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                                    <div class="shimmer shimmer-line" style="width: 50px; height: 24px; border-radius: 6px;"></div>
                                                    <div class="flex-1 min-w-0 space-y-2">
                                                        <div class="shimmer shimmer-line" style="width: 70%; height: 14px;"></div>
                                                        <div class="shimmer shimmer-line short" style="height: 12px;"></div>
                                                        <div class="shimmer shimmer-line" style="width: 40%; height: 12px;"></div>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Completed Task Display --}}
                                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                                    {{-- Type Badge with Checkpoint Indicator --}}
                                                    @if($hasCheckpoints)
                                                        <span class="px-2 py-1 text-xs font-medium rounded-lg flex-shrink-0 checkpoint-type-badge"
                                                            style="background-color: #FFD93D; color: #2D3748;">
                                                            🔒 {{ strtoupper($task['type']) }}
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-medium rounded-lg flex-shrink-0"
                                                            style="background-color: {{ $task['type'] === 'ai' ? 'var(--color-accent-100)' : ($task['type'] === 'human' ? 'var(--color-success-100)' : 'var(--color-secondary-100)') }};
                                                                   color: {{ $task['type'] === 'ai' ? 'var(--color-accent-700)' : ($task['type'] === 'human' ? 'var(--color-success-700)' : 'var(--color-secondary-700)') }};">
                                                            {{ strtoupper($task['type']) }}
                                                        </span>
                                                    @endif

                                                    {{-- Task Details --}}
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium" style="color: var(--color-text-900);">
                                                            {{ $task['name'] }}
                                                        </p>
                                                        @if(!empty($task['description']))
                                                            <p class="text-xs mt-1 line-clamp-2" style="color: var(--color-text-600);">
                                                                {{ $task['description'] }}
                                                            </p>
                                                        @endif
                                                        @if(!empty($task['estimated_hours']))
                                                            <span class="text-xs mt-1 inline-block" style="color: var(--color-text-500);">
                                                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                {{ $task['estimated_hours'] }}h
                                                            </span>
                                                        @endif

                                                        {{-- Subtasks Section --}}
                                                        @if(!empty($task['subtasks']))
                                                            <div class="mt-2">
                                                                <button
                                                                    wire:click.stop="toggleSubtasks({{ $index }})"
                                                                    class="text-xs flex items-center gap-1 hover:underline"
                                                                    style="color: var(--color-glaucous);">
                                                                    <svg class="w-3 h-3 transition-transform {{ isset($expandedSubtasks[$index]) ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                                    </svg>
                                                                    {{ count($task['subtasks']) }} subtask{{ count($task['subtasks']) > 1 ? 's' : '' }}
                                                                    @if($checkpointCount > 0)
                                                                        <span class="px-1.5 py-0.5 text-xs font-medium rounded" style="background-color: #FFD93D; color: #2D3748;">
                                                                            {{ $checkpointCount }} checkpoint{{ $checkpointCount > 1 ? 's' : '' }}
                                                                        </span>
                                                                    @endif
                                                                </button>

                                                                @if(isset($expandedSubtasks[$index]))
                                                                    <div class="mt-2 pl-4 space-y-1 border-l-2" style="border-color: var(--color-background-300);">
                                                                        @foreach($task['subtasks'] as $subIndex => $subtask)
                                                                            @php
                                                                                $isCheckpoint = isset($subtask['is_checkpoint']) && $subtask['is_checkpoint'] === true;
                                                                                $subtaskType = $isCheckpoint ? 'human' : ($subtask['type'] ?? 'ai');
                                                                            @endphp
                                                                            <div class="flex items-start gap-2 text-xs" style="color: var(--color-text-600);">
                                                                                {{-- Icon based on type --}}
                                                                                <span class="text-sm flex-shrink-0">
                                                                                    @if($isCheckpoint)
                                                                                        🔒
                                                                                    @elseif($subtaskType === 'human')
                                                                                        👤
                                                                                    @else
                                                                                        🤖
                                                                                    @endif
                                                                                </span>

                                                                                <span class="flex-1">{{ $subtask['name'] ?? $subtask['title'] ?? 'Untitled subtask' }}</span>

                                                                                <button wire:click.stop="removeSubtask({{ $index }}, {{ $subIndex }})"
                                                                                    class="p-0.5 hover:bg-red-100 rounded"
                                                                                    style="color: var(--color-bittersweet);">
                                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                                    </svg>
                                                                                </button>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        {{-- Add Subtask Button --}}
                                                        <button
                                                            wire:click.stop="addSubtask({{ $index }})"
                                                            class="mt-2 text-xs flex items-center gap-1 hover:underline"
                                                            style="color: var(--color-glaucous);">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                            Add subtask
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Remove Button (only for completed tasks) --}}
                                                <button wire:click.stop="removeTask({{ $index }})"
                                                    class="p-1 rounded hover:bg-red-100 flex-shrink-0 ml-2"
                                                    style="color: var(--color-bittersweet);"
                                                    aria-label="Remove task">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-center py-8 border-2 border-dashed rounded-lg"
                                            style="border-color: var(--color-background-300); color: var(--color-text-500);">
                                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            <p class="text-sm">No tasks generated yet</p>
                                            <p class="text-xs mt-1">Click "Regenerate" to create tasks</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- RIGHT PANEL: Flowchart Visualization (60%) --}}
                            <div class="flowchart-panel lg:w-3/5 flex flex-col">
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold" style="color: var(--color-text-900); font-family: Montserrat;">
                                        Workflow Visualization
                                    </h3>
                                </div>

                                @if(count($tasks) > 0)
                                    {{-- React Flowchart Component --}}
                                    <div class="rounded-xl shadow-sm flex-1" style="background-color: var(--color-background-100); border: 1px solid var(--color-background-300); min-height: 500px;" id="flowchart-wrapper-step3">
                                        <div
                                            x-data="{
                                                ...flowchartBridge(),
                                                tasks: @js($tasks),
                                                readOnly: false,
                                                layoutDirection: 'vertical',
                                                selectedTaskId: null
                                            }"
                                            wire:ignore
                                            class="relative h-full"
                                            id="flowchart-container-step3"
                                            @task-list-item-clicked.window="selectedTaskId = $event.detail.taskId"
                                        >
                                            <div x-ref="flowchartContainer" class="w-full h-full" style="min-height: 500px;"></div>

                                            <div x-show="!mounted" class="absolute inset-0 flex items-center justify-center rounded-xl" style="background-color: var(--color-background-50);">
                                                <div class="text-center space-y-4">
                                                    <x-cf-loading-logo loading="true" size="large" />
                                                    <p style="color: var(--color-text-600);">Loading workflow visualization...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Empty State for Flowchart --}}
                                    <div class="rounded-xl shadow-sm flex-1 flex items-center justify-center"
                                        style="background-color: var(--color-background-100); border: 1px solid var(--color-background-300); min-height: 500px;">
                                        <div class="text-center">
                                            <svg class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            <p style="color: var(--color-text-600);">Generate tasks to see workflow visualization</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Helper Text --}}
                        <div class="mt-4 p-4 rounded-lg" style="background-color: var(--color-accent-50); border: 1px solid var(--color-accent-200);">
                            <p class="text-sm" style="color: var(--color-text-600); font-family: Montserrat;">
                                <strong>💡 Tip:</strong> Review the generated tasks on the left and their workflow visualization on the right. You can regenerate tasks or proceed to the next step to review and create your project.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Step 4: Final Review & Create --}}
            @if ($currentStep === 4)
                <div class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold mb-2" style="color: var(--color-text-900); font-family: Tahoma;">
                            Review & Create</h2>
                        <p class="mb-6" style="color: var(--color-text-600);">Review your project details before creating</p>
                    </div>

                    {{-- Project Summary --}}
                    <div class="p-4 rounded-lg" style="background-color: var(--color-accent-50);">
                        <h3 class="font-bold mb-2" style="color: var(--color-text-900);">{{ $name }}</h3>
                        <p class="text-sm mb-3" style="color: var(--color-text-700);">{{ $description }}</p>
                        <div class="flex gap-4 text-sm">
                            <span style="color: var(--color-text-600);"><strong>Domain:</strong>
                                {{ ucfirst(str_replace('_', ' ', $domain)) }}</span>
                            <span style="color: var(--color-text-600);"><strong>Timeline:</strong>
                                {{ ucfirst(str_replace('_', ' ', $timeline)) }}</span>
                        </div>
                    </div>

                    {{-- Task Breakdown and Mini Flowchart Preview --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Task Breakdown --}}
                        <div>
                            <h3 class="font-bold mb-3" style="color: var(--color-text-900); font-family: Montserrat;">Task Breakdown</h3>
                            <div class="space-y-3">
                                <div class="p-4 rounded-lg"
                                    style="background-color: var(--color-accent-100);">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-medium" style="color: var(--color-accent-700);">AI Tasks</div>
                                        <div class="text-2xl font-bold" style="color: var(--color-accent-700);">
                                            {{ $taskBreakdown['ai'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 rounded-lg"
                                    style="background-color: var(--color-success-100);">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-medium" style="color: var(--color-success-700);">Human Tasks</div>
                                        <div class="text-2xl font-bold" style="color: var(--color-success-700);">
                                            {{ $taskBreakdown['human'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 rounded-lg"
                                    style="background-color: var(--color-secondary-100);">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-medium" style="color: var(--color-secondary-700);">HITL Tasks</div>
                                        <div class="text-2xl font-bold" style="color: var(--color-secondary-700);">
                                            {{ $taskBreakdown['hitl'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center p-4 rounded-lg"
                                    style="background-color: var(--color-background-100);">
                                    <span class="text-lg font-bold" style="color: var(--color-text-900);">Total: {{ count($tasks) }} tasks</span>
                                </div>
                            </div>
                        </div>

                        {{-- Mini Flowchart Preview --}}
                        <div>
                            <h3 class="font-bold mb-3" style="color: var(--color-text-900); font-family: Montserrat;">Workflow Preview</h3>
                            @if(count($tasks) > 0)
                                <div class="rounded-xl shadow-sm overflow-hidden" style="background-color: var(--color-background-100); border: 1px solid var(--color-background-300); height: 400px;">
                                    <div
                                        x-data="{
                                            ...flowchartBridge(),
                                            tasks: @js($tasks),
                                            readOnly: true,
                                            layoutDirection: 'vertical',
                                            hideValidationPanel: true
                                        }"
                                        wire:ignore
                                        class="relative w-full h-full overflow-hidden"
                                        id="flowchart-container-step4"
                                    >
                                        <div x-ref="flowchartContainer" class="w-full h-full"></div>

                                        <div x-show="!mounted" class="absolute inset-0 flex items-center justify-center rounded-xl" style="background-color: var(--color-background-50);">
                                            <div class="text-center space-y-4">
                                                <x-cf-loading-logo loading="true" size="large" />
                                                <p style="color: var(--color-text-600);">Loading workflow preview...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-xl shadow-sm flex items-center justify-center"
                                    style="background-color: var(--color-background-100); border: 1px solid var(--color-background-300); height: 400px;">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-300);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <p class="text-sm" style="color: var(--color-text-600);">No workflow to preview</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Navigation Buttons (Outside form container) --}}
        <div class="flex items-center justify-between mt-6" role="navigation" aria-label="Wizard navigation">
            <button
                type="button"
                wire:click="previousStep"
                wire:loading.attr="disabled"
                wire:target="previousStep"
                onclick="console.log('Back button clicked, current step:', {{ $currentStep }})"
                @if ($currentStep === 1) disabled @endif
                class="flex items-center gap-2 px-6 py-2.5 rounded-lg transition-all {{ $currentStep === 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                style="color: var(--color-text-600); {{ $currentStep !== 1 ? 'background-color: var(--color-background-100);' : '' }}"
                aria-label="Go to previous step">
                <svg wire:loading.remove wire:target="previousStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <svg wire:loading wire:target="previousStep" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="previousStep">Back</span>
                <span wire:loading wire:target="previousStep">Loading...</span>
            </button>

            @if ($currentStep < $totalSteps)
                <div class="flex items-center gap-3">
                    {{-- Regenerate Workflow Button (Step 4 only) --}}
                    @if ($currentStep === 4)
                        <button
                            type="button"
                            wire:click="confirmRegenerateWorkflow"
                            wire:loading.attr="disabled"
                            wire:target="regenerateWorkflow"
                            class="flex items-center gap-2 px-6 py-2.5 rounded-lg transition-all hover:opacity-90 border-2"
                            style="border-color: var(--color-glaucous); color: var(--color-glaucous); background-color: transparent;"
                            aria-label="Regenerate workflow with AI">
                            <svg wire:loading.remove wire:target="regenerateWorkflow" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <svg wire:loading wire:target="regenerateWorkflow" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="regenerateWorkflow">Regenerate Workflow</span>
                            <span wire:loading wire:target="regenerateWorkflow">Regenerating...</span>
                        </button>
                    @endif

                    {{-- Continue Button --}}
                    <button
                        type="button"
                        wire:click="nextStep"
                        wire:loading.attr="disabled"
                        wire:target="nextStep"
                        onclick="console.log('Continue button clicked, current step:', {{ $currentStep }})"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-lg transition-all hover:opacity-90"
                        style="background-color: var(--color-bittersweet); color: white;"
                        aria-label="Continue to next step">
                        <span wire:loading.remove wire:target="nextStep">Continue</span>
                        <span wire:loading wire:target="nextStep">Processing...</span>
                        <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                        <svg wire:loading wire:target="nextStep" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            @else
                <button
                    type="button"
                    wire:click="createProject"
                    wire:loading.attr="disabled"
                    wire:target="createProject"
                    onclick="console.log('Create Project button clicked')"
                    class="px-6 py-2.5 rounded-lg transition-all hover:opacity-90"
                    style="background-color: var(--color-bittersweet); color: white;"
                    aria-label="Create project">
                    <span wire:loading.remove wire:target="createProject">Create Project</span>
                    <span wire:loading wire:target="createProject">Creating...</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Regenerate Workflow Confirmation Modal --}}
    @if ($showRegenerateConfirmation)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="cancelRegenerate">
            <div class="rounded-xl max-w-md w-full mx-4 p-6 shadow-2xl" style="background-color: var(--color-background-100);" wire:click.stop>
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: rgba(251, 191, 36, 0.2);">
                        <svg class="w-6 h-6" style="color: #d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-2" style="color: var(--color-text-900);">Regenerate Workflow?</h3>
                        <p class="text-sm mb-4" style="color: var(--color-text-600);">
                            This will generate a new set of tasks and dependencies using AI. You can choose to preserve your manual edits or start fresh.
                        </p>

                        {{-- Preserve Manual Edits Option --}}
                        <label class="flex items-start gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all"
                               style="border-color: {{ $preserveManualEdits ? 'var(--color-glaucous)' : 'var(--color-background-300)' }}; background-color: {{ $preserveManualEdits ? 'rgba(92, 128, 188, 0.1)' : 'transparent' }};"
                               onmouseover="if (!{{ $preserveManualEdits ? 'true' : 'false' }}) this.style.borderColor='rgba(92, 128, 188, 0.5)'"
                               onmouseout="if (!{{ $preserveManualEdits ? 'true' : 'false' }}) this.style.borderColor='var(--color-background-300)'">
                            <input
                                type="checkbox"
                                wire:model="preserveManualEdits"
                                class="mt-1 w-4 h-4 rounded"
                                style="accent-color: var(--color-glaucous);"
                            />
                            <div class="flex-1">
                                <div class="font-medium" style="color: var(--color-text-900);">Preserve Manual Edits</div>
                                <div class="text-xs mt-1" style="color: var(--color-text-600);">
                                    Keep custom node positions and manual connections. Only regenerate task structure and AI-generated dependencies.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 mt-6">
                    <button
                        type="button"
                        wire:click="cancelRegenerate"
                        class="flex-1 px-4 py-2.5 border-2 rounded-lg font-medium transition-colors"
                        style="border-color: var(--color-background-300); color: var(--color-text-700); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--color-background-200)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="regenerateWorkflow"
                        wire:loading.attr="disabled"
                        wire:target="regenerateWorkflow"
                        class="flex-1 px-4 py-2.5 rounded-lg font-medium text-white transition-colors"
                        style="background-color: var(--color-glaucous);"
                        onmouseover="this.style.opacity='0.9'"
                        onmouseout="this.style.opacity='1'"
                    >
                        <span wire:loading.remove wire:target="regenerateWorkflow">Regenerate</span>
                        <span wire:loading wire:target="regenerateWorkflow">Regenerating...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Task Modal --}}
    @if($showAddTaskModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="closeAddTaskModal"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="relative inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                    style="background-color: var(--color-background-50);"
                    x-data="{
                        taskName: @entangle('newTask.name'),
                        taskDescription: @entangle('newTask.description'),
                        taskType: @entangle('newTask.type'),
                        estimatedHours: @entangle('newTask.estimated_hours'),

                        calculateQualityScore() {
                            let score = 0;
                            const name = this.taskName || '';
                            const description = this.taskDescription || '';
                            const hours = this.estimatedHours || 0;

                            // Check 1: Has action verb at start (25 points)
                            const actionVerbs = ['create', 'build', 'design', 'implement', 'develop', 'test', 'review', 'analyze', 'setup', 'configure', 'write', 'deploy', 'fix', 'update', 'integrate', 'optimize', 'refactor'];
                            const firstWord = name.toLowerCase().split(' ')[0];
                            if (actionVerbs.includes(firstWord)) score += 25;

                            // Check 2: Avoid vague terms (25 points if no vague terms)
                            const vagueTerms = ['stuff', 'things', 'some', 'various', 'etc', 'misc', 'other'];
                            const hasVagueTerms = vagueTerms.some(term => name.toLowerCase().includes(term) || description.toLowerCase().includes(term));
                            if (!hasVagueTerms) score += 25;

                            // Check 3: Has sufficient description (25 points)
                            if (description.length >= 20) {
                                score += 25;
                            } else if (description.length >= 10) {
                                score += 15;
                            } else if (description.length > 0) {
                                score += 5;
                            }

                            // Check 4: Has time estimate (25 points)
                            if (hours > 0) {
                                score += 25;
                            }

                            return Math.min(100, score);
                        },

                        get qualityScore() {
                            return this.calculateQualityScore();
                        },

                        get scoreColor() {
                            const score = this.qualityScore;
                            if (score >= 90) return 'var(--color-tea-green)';
                            if (score >= 70) return 'var(--color-glaucous)';
                            if (score >= 50) return '#f59e0b';
                            return 'var(--color-bittersweet)';
                        },

                        get scoreLabel() {
                            const score = this.qualityScore;
                            if (score >= 90) return 'Excellent';
                            if (score >= 70) return 'Good';
                            if (score >= 50) return 'Needs Work';
                            return 'Poor';
                        }
                    }">

                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b" style="border-color: var(--color-background-300);">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold" style="color: var(--color-text-900); font-family: Montserrat;">
                                Add Manual Task
                            </h3>
                            <button wire:click="closeAddTaskModal" class="p-1 rounded-lg hover:bg-gray-100" style="color: var(--color-text-600);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- Task Name --}}
                        <div class="space-y-2">
                            <label for="new-task-name" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                Task Name <span style="color: var(--color-bittersweet);">*</span>
                            </label>
                            <input type="text" id="new-task-name" wire:model.live="newTask.name"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                                placeholder="e.g., Design user interface"
                                maxlength="200">
                            <div class="flex justify-between items-center">
                                @error('newTask.name')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @else
                                    <span></span>
                                @enderror
                                <span class="text-xs" style="color: var(--color-text-500);">{{ strlen($newTask['name'] ?? '') }}/200</span>
                            </div>
                        </div>

                        {{-- Task Description --}}
                        <div class="space-y-2">
                            <label for="new-task-description" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                Description <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                            </label>
                            <textarea id="new-task-description" wire:model.live="newTask.description" rows="4"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                                placeholder="Provide additional details about this task..."
                                maxlength="500"></textarea>
                            <div class="flex justify-between items-center">
                                @error('newTask.description')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @else
                                    <span></span>
                                @enderror
                                <span class="text-xs" style="color: var(--color-text-500);">{{ strlen($newTask['description'] ?? '') }}/500</span>
                            </div>
                        </div>

                        {{-- Task Type and Estimated Hours (Row) --}}
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Task Type --}}
                            <div class="space-y-2">
                                <label for="new-task-type" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                    Task Type <span style="color: var(--color-bittersweet);">*</span>
                                </label>
                                <select id="new-task-type" wire:model="newTask.type"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);">
                                    <option value="human">Human - Manual</option>
                                    <option value="ai">AI - Automated</option>
                                    <option value="hitl">HITL - Human-in-the-Loop</option>
                                </select>
                                @error('newTask.type')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Estimated Hours --}}
                            <div class="space-y-2">
                                <label for="new-task-hours" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                    Estimated Hours <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                                </label>
                                <input type="number" id="new-task-hours" wire:model="newTask.estimated_hours"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                                    placeholder="e.g., 8"
                                    step="1"
                                    min="0"
                                    max="1000">
                                @error('newTask.estimated_hours')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Validation Score (Live) --}}
                        <div class="p-4 rounded-lg" style="background-color: var(--color-accent-50); border: 1px solid var(--color-accent-200);">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium" style="color: var(--color-text-700);">Task Quality Score</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium px-2 py-1 rounded"
                                        :style="`background-color: ${scoreColor}20; color: ${scoreColor};`"
                                        x-text="scoreLabel"></span>
                                    <span class="text-sm font-bold" :style="`color: ${scoreColor};`" x-text="`${qualityScore}/100`"></span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300"
                                    :style="`background-color: ${scoreColor}; width: ${qualityScore}%;`"></div>
                            </div>
                            <p class="text-xs mt-2" style="color: var(--color-text-600);">
                                <strong>Tips:</strong> Start with an action verb, provide detailed description, and estimate hours for higher scores.
                            </p>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 border-t flex items-center justify-end gap-3" style="border-color: var(--color-background-300);">
                        <button wire:click="closeAddTaskModal"
                            class="px-4 py-2.5 rounded-lg border transition-colors"
                            style="border-color: var(--color-background-300); color: var(--color-text-700);">
                            Cancel
                        </button>
                        <button wire:click="addManualTask"
                            class="px-4 py-2.5 rounded-lg font-medium text-white transition-colors"
                            style="background-color: var(--color-tea-green);"
                            onmouseover="this.style.opacity='0.9'"
                            onmouseout="this.style.opacity='1'">
                            <span wire:loading.remove wire:target="addManualTask">Add Task</span>
                            <span wire:loading wire:target="addManualTask">Adding...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Task Edit Modal --}}
    @if($showTaskEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="closeTaskEditModal"></div>

            {{-- Modal Container --}}
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="relative inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                    style="background-color: var(--color-background-50);"
                    x-data="{
                        taskName: @entangle('editingTask.name'),
                        taskDescription: @entangle('editingTask.description'),
                        taskType: @entangle('editingTask.type'),
                        estimatedHours: @entangle('editingTask.estimated_hours'),

                        calculateQualityScore() {
                            let score = 0;
                            const name = this.taskName || '';
                            const description = this.taskDescription || '';
                            const hours = this.estimatedHours || 0;

                            // Check 1: Has action verb at start (25 points)
                            const actionVerbs = ['create', 'build', 'design', 'implement', 'develop', 'test', 'review', 'analyze', 'setup', 'configure', 'write', 'deploy', 'fix', 'update', 'integrate', 'optimize', 'refactor'];
                            const firstWord = name.toLowerCase().split(' ')[0];
                            if (actionVerbs.includes(firstWord)) score += 25;

                            // Check 2: Avoid vague terms (25 points if no vague terms)
                            const vagueTerms = ['stuff', 'things', 'some', 'various', 'etc', 'misc', 'other'];
                            const hasVagueTerms = vagueTerms.some(term => name.toLowerCase().includes(term) || description.toLowerCase().includes(term));
                            if (!hasVagueTerms) score += 25;

                            // Check 3: Has sufficient description (25 points)
                            if (description.length >= 20) {
                                score += 25;
                            } else if (description.length >= 10) {
                                score += 15;
                            } else if (description.length > 0) {
                                score += 5;
                            }

                            // Check 4: Has time estimate (25 points)
                            if (hours > 0) {
                                score += 25;
                            }

                            return Math.min(100, score);
                        },

                        get qualityScore() {
                            return this.calculateQualityScore();
                        },

                        get scoreColor() {
                            const score = this.qualityScore;
                            if (score >= 90) return 'var(--color-tea-green)';
                            if (score >= 70) return 'var(--color-glaucous)';
                            if (score >= 50) return '#f59e0b';
                            return 'var(--color-bittersweet)';
                        },

                        get scoreLabel() {
                            const score = this.qualityScore;
                            if (score >= 90) return 'Excellent';
                            if (score >= 70) return 'Good';
                            if (score >= 50) return 'Needs Work';
                            return 'Poor';
                        }
                    }">

                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b" style="border-color: var(--color-background-300);">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold" style="color: var(--color-text-900); font-family: Montserrat;">
                                Edit Task
                            </h3>
                            <button wire:click="closeTaskEditModal" class="p-1 rounded-lg hover:bg-gray-100" style="color: var(--color-text-600);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- Task Name --}}
                        <div class="space-y-2">
                            <label for="edit-task-name" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                Task Name <span style="color: var(--color-bittersweet);">*</span>
                            </label>
                            <input type="text" id="edit-task-name" wire:model.live="editingTask.name"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                                placeholder="e.g., Design user interface"
                                maxlength="200">
                            <div class="flex justify-between items-center">
                                @error('editingTask.name')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @else
                                    <span></span>
                                @enderror
                                <span class="text-xs" style="color: var(--color-text-500);">{{ strlen($editingTask['name'] ?? '') }}/200</span>
                            </div>
                        </div>

                        {{-- Task Description --}}
                        <div class="space-y-2">
                            <label for="edit-task-description" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                Description <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                            </label>
                            <textarea id="edit-task-description" wire:model.live="editingTask.description" rows="4"
                                class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                                placeholder="Provide additional details about this task..."
                                maxlength="1000"></textarea>
                            <div class="flex justify-between items-center">
                                @error('editingTask.description')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @else
                                    <span></span>
                                @enderror
                                <span class="text-xs" style="color: var(--color-text-500);">{{ strlen($editingTask['description'] ?? '') }}/1000</span>
                            </div>
                        </div>

                        {{-- Task Type and Estimated Hours (Row) --}}
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Task Type --}}
                            <div class="space-y-2">
                                <label for="edit-task-type" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                    Task Type <span style="color: var(--color-bittersweet);">*</span>
                                </label>
                                <select id="edit-task-type" wire:model="editingTask.type"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);">
                                    <option value="ai">AI - Automated</option>
                                    <option value="human">Human - Manual</option>
                                    <option value="hitl">HITL - Human-in-the-Loop</option>
                                </select>
                                @error('editingTask.type')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Estimated Hours --}}
                            <div class="space-y-2">
                                <label for="edit-task-hours" class="text-sm font-medium" style="color: var(--color-text-700); font-family: Montserrat;">
                                    Estimated Hours <span class="text-sm font-normal" style="color: var(--color-text-500);">(Optional)</span>
                                </label>
                                <input type="number" id="edit-task-hours" wire:model="editingTask.estimated_hours"
                                    class="w-full px-4 py-2.5 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    style="border-color: var(--color-background-300); background-color: var(--color-background-50); color: var(--color-text-900); --tw-ring-color: var(--color-glaucous);"
                                    placeholder="e.g., 8"
                                    step="0.5"
                                    min="0.5"
                                    max="999">
                                @error('editingTask.estimated_hours')
                                    <span class="text-sm" style="color: var(--color-bittersweet);" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Validation Score (Live) --}}
                        <div class="p-4 rounded-lg" style="background-color: var(--color-accent-50); border: 1px solid var(--color-accent-200);">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium" style="color: var(--color-text-700);">Task Quality Score</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium px-2 py-1 rounded"
                                        :style="`background-color: ${scoreColor}20; color: ${scoreColor};`"
                                        x-text="scoreLabel"></span>
                                    <span class="text-sm font-bold" :style="`color: ${scoreColor};`" x-text="`${qualityScore}/100`"></span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300"
                                    :style="`background-color: ${scoreColor}; width: ${qualityScore}%;`"></div>
                            </div>
                            <p class="text-xs mt-2" style="color: var(--color-text-600);">
                                <strong>Tips:</strong> Start with an action verb, provide detailed description, and estimate hours for higher scores.
                            </p>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 border-t flex items-center justify-between gap-3" style="border-color: var(--color-background-300);">
                        <button wire:click="regenerateSingleTask({{ $editingTaskIndex }})"
                            class="px-4 py-2.5 rounded-lg border transition-colors text-sm flex items-center gap-2"
                            style="border-color: var(--color-glaucous); color: var(--color-glaucous);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Regenerate Task
                        </button>

                        <div class="flex items-center gap-3">
                            <button wire:click="closeTaskEditModal"
                                class="px-4 py-2.5 rounded-lg border transition-colors"
                                style="border-color: var(--color-background-300); color: var(--color-text-700);">
                                Cancel
                            </button>
                            <button wire:click="saveTaskEdit"
                                class="px-4 py-2.5 rounded-lg font-medium text-white transition-colors"
                                style="background-color: var(--color-glaucous);"
                                onmouseover="this.style.opacity='0.9'"
                                onmouseout="this.style.opacity='1'">
                                <span wire:loading.remove wire:target="saveTaskEdit">Save Changes</span>
                                <span wire:loading wire:target="saveTaskEdit">Saving...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
