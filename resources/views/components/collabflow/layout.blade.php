{{-- CollabFlow Main Layout Wrapper --}}
@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen" style="background-color: var(--color-background-50); color: var(--color-text-900);">
        {{-- Sidebar --}}
        <x-collabflow.sidebar />

        {{-- Header --}}
        <x-collabflow.header />

        {{-- Main Content --}}
        <main class="ml-[280px] mt-14 p-8 min-h-[calc(100vh-3.5rem)]" style="background-color: var(--color-background-50);">
            <div class="mx-auto max-w-[1400px]">
                {{ $slot }}
            </div>
        </main>

        {{-- Notification Panel --}}
        @livewire('notifications.notification-panel')

        {{-- Command Palette --}}
        @livewire('command-palette')

        @livewireScripts
        @fluxScripts
        <script>
            // Debug: Check if Livewire is loaded
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Livewire loaded:', typeof window.Livewire !== 'undefined');
                console.log('Alpine loaded:', typeof window.Alpine !== 'undefined');

                // Check if Livewire components are registered
                setTimeout(() => {
                    const livewireComponents = document.querySelectorAll('[wire\\:id]');
                    console.log('Livewire components found:', livewireComponents.length);
                    livewireComponents.forEach((el, index) => {
                        console.log(`Component ${index}:`, el.getAttribute('wire:id'));
                    });
                }, 1000);
            });

            // Listen for Livewire events
            document.addEventListener('livewire:initialized', () => {
                console.log('Livewire initialized successfully!');
            });

            document.addEventListener('livewire:navigating', () => {
                console.log('Livewire navigating...');
            });
        </script>
    </body>
</html>
