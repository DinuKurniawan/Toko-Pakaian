<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $seo = $page['props']['seo'] ?? [];
            $siteName = $seo['siteName'] ?? config('app.name', 'LUMIÈRE');
            $metaTitle = $seo['title'] ?? $siteName;
            $metaDescription = $seo['description'] ?? 'Belanja pakaian modern pria, wanita, dan unisex di LUMIÈRE.';
            $metaImage = $seo['image'] ?? asset('favicon.ico');
            $metaUrl = $seo['url'] ?? url()->current();
            $metaType = $seo['type'] ?? 'website';
            $metaRobots = $seo['robots'] ?? 'index,follow';
            $structuredData = $seo['structuredData'] ?? null;
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="robots" content="{{ $metaRobots }}">
        <meta name="theme-color" content="#4f46e5">
        <link rel="canonical" href="{{ $metaUrl }}">

        <title inertia>{{ $metaTitle }}</title>

        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $metaTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:image" content="{{ $metaImage }}">
        <meta property="og:url" content="{{ $metaUrl }}">
        <meta property="og:type" content="{{ $metaType }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $metaImage }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @if ($structuredData)
            <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
