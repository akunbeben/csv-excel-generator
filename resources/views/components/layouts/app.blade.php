<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

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
    <body class="flex justify-center items-center font-sans antialiased dark:bg-black dark:text-white/50 min-h-screen max-w-7xl mx-auto bg-gray-100">
        <main class="w-full">
            {{ $slot }}
        </main>

        @filamentScripts
        @vite('resources/js/app.js')
    </body>
</html>
