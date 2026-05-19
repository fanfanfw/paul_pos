<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="kasirku">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'KasirKu') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-surface min-h-screen bg-base-200 font-sans antialiased">
    <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">
        <x-sidebar />

        <div class="min-h-screen lg:pl-64">
            <x-topbar :title="$header ?? null" />

            <main class="p-4 pb-24 lg:p-6">
                <x-alert />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
