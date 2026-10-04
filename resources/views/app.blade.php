<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="dark">
        <meta name="description" content="{{ $portfolioMeta['description'] }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ $portfolioMeta['url'] }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Holo Resume">
        <meta property="og:title" content="{{ $portfolioMeta['fullTitle'] }}">
        <meta property="og:description" content="{{ $portfolioMeta['description'] }}">
        <meta property="og:url" content="{{ $portfolioMeta['url'] }}">
        <meta property="og:image" content="{{ $portfolioMeta['image'] }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $portfolioMeta['fullTitle'] }}">
        <meta name="twitter:description" content="{{ $portfolioMeta['description'] }}">
        <meta name="twitter:image" content="{{ $portfolioMeta['image'] }}">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <title>{{ $portfolioMeta['fullTitle'] }}</title>
        <script type="application/ld+json">
            {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
        </script>
        <style>
            html, body { background: #07080f; color: #f3eee3; }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="antialiased">
        @inertia
        @include('portfolio.static')
    </body>
</html>
