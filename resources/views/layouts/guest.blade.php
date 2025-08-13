<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UUM Press Inventory') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
            <div class="mb-8">
                <a href="/" class="block">
                    <x-application-logo class="w-32 h-32 mx-auto drop-shadow-lg hover:drop-shadow-xl transition-all duration-300" />
                </a>
                <h1 class="text-center text-2xl font-bold text-gray-800 dark:text-white mt-4">UUM Press Inventory</h1>
                <p class="text-center text-gray-600 dark:text-gray-300 mt-2">Welcome to the inventory management system</p>
            </div>

            <div class="w-full sm:max-w-lg mt-6 px-8 py-8 bg-white dark:bg-gray-800 shadow-2xl overflow-hidden sm:rounded-2xl border border-gray-200 dark:border-gray-700">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
