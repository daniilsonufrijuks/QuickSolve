<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2563eb">
        <meta name="application-name" content="{{ config('app.name', 'QuickSolve') }}">
        <meta name="robots" content="{{ $robots ?? 'index,follow' }}">

        <title inertia>{{ $metaTitle ?? config('app.name', 'QuickSolve') }}</title>
        <meta name="description" content="{{ $metaDescription ?? 'Small tools for invoices, profit, and practical business downloads.' }}">
        <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
        <link rel="icon" href="{{ url('/favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ url('/logo.svg') }}">

        <meta property="og:title" content="{{ $metaTitle ?? config('app.name', 'QuickSolve') }}">
        <meta property="og:description" content="{{ $metaDescription ?? 'Small tools for invoices, profit, and practical business downloads.' }}">
        <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
        <meta property="og:type" content="{{ $ogType ?? 'website' }}">
        <meta property="og:site_name" content="{{ config('app.name', 'QuickSolve') }}">
        <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
        <meta property="og:image" content="{{ $ogImage ?? url('/images/og.svg') }}">
        <meta property="og:image:alt" content="{{ $ogImageAlt ?? config('app.name', 'QuickSolve') }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle ?? config('app.name', 'QuickSolve') }}">
        <meta name="twitter:description" content="{{ $metaDescription ?? 'Small tools for invoices, profit, and practical business downloads.' }}">
        <meta name="twitter:image" content="{{ $ogImage ?? url('/images/og.svg') }}">

        @if (! empty($jsonLd))
            <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
