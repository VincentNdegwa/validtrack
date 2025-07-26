<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="description" content="Track, manage, and ensure compliance for all your documents easily with ValidTrack.">
        <meta name="keywords" content="document tracking, compliance, management, ValidTrack, audit, security">
        <meta property="og:title" content="Document Track & Compliance System | ValidTrack">
        <meta property="og:description" content="Track, manage, and ensure compliance for all your documents easily with ValidTrack.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="http://validtrack.tech360.systems/">
        <meta property="og:image" content="/logo-blue.svg">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Document Track & Compliance System | ValidTrack">
        <meta name="twitter:description" content="Track, manage, and ensure compliance for all your documents easily with ValidTrack.">
        <meta name="twitter:image" content="/logo-blue.svg">
        <link rel="canonical" href="http://validtrack.tech360.systems/">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: hsl(220 25% 98%);
            }

            html.dark {
                background-color: hsl(230 35% 7%);
            }
        </style>

        <title inertia>{{ config('app.name', 'ValidTrack') }}</title>

        @inertiaHead
        {{-- Placeholder for dynamic JSON-LD structured data injected by Vue pages --}}
        @if (isset($jsonLd))
            <script type="application/ld+json">{!! $jsonLd !!}</script>
        @endif

        {{-- <link rel="icon" href="/favicon.ico" sizes="any"> --}}
        <link rel="icon" href="/logo-round.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
        @paddleJS

    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
