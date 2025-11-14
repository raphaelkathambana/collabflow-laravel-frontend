<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

{{-- CollabFlow Favicon - Modern browsers will use SVG --}}
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- CollabFlow Fonts: Montserrat for body, Tahoma for headings --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">

{{-- CRITICAL: Load order matters for Islands Architecture --}}
{{-- 1. Load CSS first --}}
@vite(['resources/css/app.css'])

{{-- 2. Load React vendor bundle (creates window.React) --}}
@vite(['resources/js/vendor-react.js'])

{{-- 3. Load main app (Alpine bridge will dynamically load React components) --}}
@vite(['resources/js/app.js'])

@fluxAppearance()
@livewireStyles
