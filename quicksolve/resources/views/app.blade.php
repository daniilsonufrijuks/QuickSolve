<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ $metaTitle ?? config('app.name', 'QuickSolve') }}</title>
        <meta name="description" content="{{ $metaDescription ?? 'Small tools. Smarter business.' }}">
        <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
        <meta property="og:title" content="{{ $metaTitle ?? config('app.name', 'QuickSolve') }}">
        <meta property="og:description" content="{{ $metaDescription ?? 'Small tools. Smarter business.' }}">
        <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name', 'QuickSolve') }}">

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
