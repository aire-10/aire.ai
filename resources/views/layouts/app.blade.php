<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Airé • @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
    @stack('head-scripts')
</head>
<body class="@yield('body-class')">
    <header class="landing-navbar">

    <!-- Logo -->
    <a href="{{ url('/home') }}" class="landing-logo">
        <img src="{{ asset('images/logo.png') }}" alt="Airé Logo" />
        <span>Airé</span>
    </a>

    <!-- Nav Links -->
    <nav class="landing-navlinks">

        @auth
        <a href="{{ url('/home') }}" class="nav-link">Home</a>
        <a href="{{ url('/chat') }}" class="nav-link">Airé</a>
        <a href="{{ url('/journal') }}" class="nav-link">Journal</a>

        <!-- Self Care Dropdown -->
        <div class="nav-dropdown">
            <button class="nav-dropbtn" type="button">Self Care ▾</button>
            <div class="nav-dropdown-menu">
            <a href="{{ url('/grounding') }}">Grounding</a>
            <a href="{{ url('/breathing') }}">Breathing</a>
            <a href="{{ url('/moodbooster') }}">Mood Booster</a>
            </div>
        </div>

        <a href="{{ url('/growth') }}">Butterfly Pet</a>

        <!-- Profile -->
        <div class="nav-profile">
            <img 
                src="{{ Auth::user()->profile_photo 
                    ? asset('storage/' . Auth::user()->profile_photo) 
                    : asset('images/profile.jpeg') }}" 
                alt="Profile"
            />

            <div class="profile-dropdown">
            <a href="{{ url('/profile') }}">Profile</a>
            <a href="#" id="hotlineBtn">Hotline</a>

            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="dropdown-link">Logout</button>
            </form>
            </div>
        </div>

        @else
        <a href="{{ url('/') }}" class="nav-link">About</a>
        <a href="{{ url('/login') }}" class="nav-link">Login</a>
        <a href="{{ url('/signup') }}" class="nav-link">Sign Up</a>
        @endauth

    </nav>
    </header>
    
    @yield('content')
    
    <div id="hotlineModal" class="modal-overlay hidden">
    <div class="modal-card">
        <h2>📞 Hotline Support</h2>
        <p>You’re not alone 💚</p>
        <p><strong>Talian Harapan:</strong> 145</p>
        <p><strong>Emergency:</strong> 991</p>
        <button id="closeHotline" class="btn btn-filled">Close</button>
    </div>
    </div>

    @stack('scripts')
    <script>
    document.addEventListener("DOMContentLoaded", function () {

    const profile = document.querySelector(".nav-profile");
    const dropdown = document.querySelector(".profile-dropdown");

    if (profile && dropdown) {
        profile.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdown.classList.toggle("show");
        });

        document.addEventListener("click", () => {
        dropdown.classList.remove("show");
        });
    }

    const selfCareBtn = document.querySelector(".nav-dropbtn");
    const selfCareMenu = document.querySelector(".nav-dropdown-menu");

    if (selfCareBtn && selfCareMenu) {
        selfCareBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        selfCareMenu.classList.toggle("show");
        });

        document.addEventListener("click", () => {
        selfCareMenu.classList.remove("show");
        });
    }

    });

    // Hotline open
    const hotlineBtn = document.getElementById("hotlineBtn");
    const modal = document.getElementById("hotlineModal");
    const closeBtn = document.getElementById("closeHotline");

    if (hotlineBtn && modal) {
    hotlineBtn.addEventListener("click", (e) => {
        e.preventDefault();
        modal.classList.remove("hidden");
    });
    }

    // Close button
    if (closeBtn && modal) {
    closeBtn.addEventListener("click", () => {
        modal.classList.add("hidden");
    });
    }

    // Click outside to close
    window.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.classList.add("hidden");
    }
    });

    </script>
</body>
</html>