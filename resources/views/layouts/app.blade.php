<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airé • @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
    <script src="{{ asset('js/navbar.js') }}" defer></script>
</head>
<body class="@yield('body-class')">
    <header id="navbar"></header>
    
    @yield('content')

    @stack('scripts')
</body>
</html>