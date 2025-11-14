@props([
    'title',
    'description',
])

<div class="mb-8">
    <h1 class="text-3xl font-bold mb-2" style="color: var(--color-text-900);">{{ $title }}</h1>
    <p style="color: var(--color-text-600);">{{ $description }}</p>
</div>
