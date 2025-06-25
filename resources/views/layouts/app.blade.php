<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Bootstrap 5 JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- Vite (for your compiled app CSS/JS, if needed) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Optional navbar --}}
    @include('layouts.navigation')

    {{-- Optional heading --}}
    @hasSection('header')
        <header class="bg-white shadow-sm border-bottom mb-4">
            <div class="container-lg py-3">
                @yield('header')
            </div>
        </header>
    @endif

    {{-- Main content --}}
    <main class="container-lg my-5">
        @yield('content')
    </main>
</body>
</html>
