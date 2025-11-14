<x-collabflow.layout title="Task Details - CollabFlow">
    <div class="max-w-4xl">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm mb-2" style="color: var(--color-text-600);">
                <a href="{{ route('tasks.index') }}" wire:navigate class="hover:underline">All Tasks</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span>Task #{{ $id }}</span>
            </div>
            <h1 class="text-3xl font-bold" style="font-family: Tahoma; color: var(--color-text-900);">
                Task Details
            </h1>
        </div>

        {{-- Task Detail Card --}}
        <div class="rounded-xl border p-8" style="background-color: var(--color-background-50); border-color: var(--color-background-300);">
            <div class="text-center py-12">
                <svg class="h-16 w-16 mx-auto mb-4" style="color: var(--color-glaucous); opacity: 0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h2 class="text-xl font-bold mb-2" style="font-family: Tahoma; color: var(--color-text-800);">Task Detail View</h2>
                <p class="text-sm mb-6" style="color: var(--color-text-600);">
                    Viewing task ID: <span class="font-mono font-semibold" style="color: var(--color-glaucous);">{{ $id }}</span>
                </p>
                <div class="inline-flex flex-col gap-2 text-left p-6 rounded-lg border" style="background-color: var(--color-background-100); border-color: var(--color-background-300);">
                    <p class="text-xs font-medium mb-2" style="color: var(--color-text-600);">This page will display:</p>
                    <div class="flex items-start gap-2 text-sm" style="color: var(--color-text-700);">
                        <span style="color: var(--color-glaucous);">•</span>
                        <span>Task title and description</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm" style="color: var(--color-text-700);">
                        <span style="color: var(--color-glaucous);">•</span>
                        <span>Task type (AI, Human, HITL)</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm" style="color: var(--color-text-700);">
                        <span style="color: var(--color-glaucous);">•</span>
                        <span>Status and progress tracking</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm" style="color: var(--color-text-700);">
                        <span style="color: var(--color-glaucous);">•</span>
                        <span>Project association</span>
                    </div>
                    <div class="flex items-start gap-2 text-sm" style="color: var(--color-text-700);">
                        <span style="color: var(--color-glaucous);">•</span>
                        <span>Due dates and assignees</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-collabflow.layout>
