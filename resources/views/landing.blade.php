@extends('layouts.app')

@section('title', 'Airé • Your AI-powered mental wellness companion')

@push('styles')
<style>
    .landing-page-container {
        background-image: url('{{ asset("images/landing-butterfly-right.jpeg") }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
    }
    
    .landing-hero {
        background: transparent;
    }
    
    .landing-section, .landing-footer {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(5px);
        margin: 0;
    }
</style>
@endpush

@section('content')
<div class="landing-page-container">  <!-- ← ADD THIS -->
    <main class="landing-hero split-layout">
        <div class="hero-card">
            <p class="hero-tag">AI-powered mental wellness support</p>
            <h1 class="hero-title">Find your peace.</h1>
            <p class="hero-emotional">You don’t have to go through it alone.</p>
            <p class="hero-subtitle">Talk, reflect, and feel better — anytime.</p>

            <div class="hero-actions">
                <a class="btn btn-filled btn-lg" href="{{ route('login') }}">Start Chatting</a>
                <a class="btn btn-outline btn-lg" href="#features">Start Your Journey</a>
            </div>

            <div class="chat-preview">
                <p><strong>Airé:</strong> Hi, I'm Airé🍀. How are you feeling today?</p>
            </div>
        </div>

        <div class="growth-card">
            <h3>Your Growth Journey 🌱</h3>
            <img id="growthImage" src="{{ asset('images/egg.png') }}" alt="growth stage" />
            <p id="growthText">Start your journey 🌱</p>
        </div>
    </main>

    <section id="features" class="landing-section">
        <div class="section-inner">
            <p class="section-label">FEATURES</p>
            <div class="feature-grid">
                <article class="feature-card">
                    <h3>AI Chatbot: Airé</h3>
                    <p>Chat anytime for support and guidance.</p>
                </article>
                <article class="feature-card">
                    <h3>Smart Journal</h3>
                    <p>Write, reflect, and understand your emotions.</p>
                </article>
                <article class="feature-card">
                    <h3>Self-Care Tools</h3>
                    <p>Quick tools to calm your mind.</p>
                </article>
            </div>
            <div class="center">
                <a class="btn btn-filled btn-lg" href="{{ route('login') }}">Explore Features</a>
            </div>
        </div>
    </section>

    <section id="about" class="landing-section landing-section-alt">
        <div class="section-inner">
            <p class="section-label">ABOUT</p>
            <div class="about-grid">
                <div class="about-card">
                    <h3>Our Mission</h3>
                    <p>To make mental well-being more accessible through a calm, supportive digital companion.</p>
                </div>
                <div class="about-card">
                    <h3>Our Technology</h3>
                    <p>Airé understands your emotions and provides gentle, real-time support when you need it.</p>
                </div>
                <div class="about-card">
                    <h3>Data & Privacy</h3>
                    <p>Your conversations should feel safe. We design for user control, security, and respect.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <p>© <span id="year"></span> Airé • FYP Project</p>
    </footer>
</div>  <!-- ← CLOSE IT HERE -->

<script>
    document.getElementById("year").textContent = new Date().getFullYear();
    
    let streak = 0; 
    let mood = "neutral"; 

    function updateGrowth() {
        const image = document.getElementById("growthImage");
        const text = document.getElementById("growthText");
        if (!image || !text) return;

        if (streak === 0) {
            image.src = "{{ asset('images/egg.png') }}";
            text.innerText = "🐛 A new beginning 🦋";
        } else if (streak <= 2) {
            image.src = "{{ asset('images/pupa.png') }}";
            text.innerText = "Growing slowly";
        } else if (streak <= 4) {
            image.src = "{{ asset('images/caterpillar.png') }}";
            text.innerText = "Making progress";
        } else {
            image.src = "{{ asset('images/adult_glow.png') }}";
            text.innerText = "You are thriving 🦋";
        }

        if (mood === "declining") {
            image.src = "{{ asset('images/surviving.jpeg') }}";
            text.innerText = "You're still trying — and that matters";
        }
        if (mood === "improving") {
            image.src = "{{ asset('images/struggling.jpeg') }}";
            text.innerText = "Healing takes time 💛";
        }
    }
    updateGrowth();
</script>
@endsection