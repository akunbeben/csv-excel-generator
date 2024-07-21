<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="Generate synthetic data for testing and development with this Laravel application. Supports customizable columns, locales, and multiple output formats (CSV, Excel).">

    <meta property="og:url" content="https://seed.beben.space">
    <meta property="og:type" content="website">
    <meta property="og:title" content="CSV Excel Data Generator Tool">
    <meta property="og:description" content="Generate synthetic data for testing and development with this Laravel application. Supports customizable columns, locales, and multiple output formats (CSV, Excel).">
    <meta property="og:image" content="{{ asset('card.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:domain" content="seed.beben.space">
    <meta property="twitter:url" content="https://seed.beben.space">
    <meta name="twitter:title" content="CSV Excel Data Generator Tool">
    <meta name="twitter:description" content="Generate synthetic data for testing and development with this Laravel application. Supports customizable columns, locales, and multiple output formats (CSV, Excel).">
    <meta name="twitter:image" content="{{ asset('card.png') }}">

    <link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body
    class="flex justify-center items-center font-sans antialiased dark:bg-black dark:text-white/50 min-h-screen max-w-7xl mx-auto bg-gray-100">
    <main class="w-full">
        {{ $slot }}
    </main>

    @filamentScripts
    @vite('resources/js/app.js')
</body>

</html>
