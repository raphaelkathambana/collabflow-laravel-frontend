<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <title>{{ $title ?? 'CollabFlow - AI-Powered Project Management' }}</title>
    </head>
    <body class="min-h-screen antialiased" style="background-color: var(--color-background-50);">
        <div class="min-h-screen flex flex-col" style="background-color: var(--color-background-50);">
            {{-- Header --}}
            <div class="border-b py-4 px-8" style="border-color: var(--color-background-300);">
                <a href="{{ route('home') }}" class="flex items-center gap-2 w-fit">
                    <x-cf-logo size="8" class="text-[var(--glaucous)]" />
                    <span class="font-bold text-xl" style="color: var(--color-text-900);">CollabFlow</span>
                </a>
            </div>

            {{-- Main Content --}}
            <div class="flex-1 flex items-center justify-center px-8 py-12">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @livewireScripts
        @fluxScripts
    </body>
</html>
