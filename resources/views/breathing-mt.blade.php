@extends('layouts.app')

@section('title', 'Airé — Breathing & Mood')
@section('body-class', 'breathing-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/breathing-mt.css') }}">
<style>
body.breathing-page {
    background-image: url('{{ asset("images/landing-butterfly-right.jpeg") }}');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}
.main-card {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(8px);
}
</style>
@endpush

@section('content')

<a href="{{ route('home') }}" class="back-home-btn">← Back to Home</a>

<main class="main-screen">

    <!-- SIDEBAR -->
    <div class="breathing-sidebar">
        <div class="sidebar-card">

            <!-- HOW SECTION (KEPT) -->
            <button class="sidebar-toggle" onclick="toggleSidebar('how')">
                HOW?
                <span class="toggle-arrow" id="howArrow">▼</span>
            </button>

            <div class="sidebar-body" id="howBody">
                <p class="how-title">How it works</p>
                <ul class="how-list">
                    <li>Inhale slowly (4 sec)</li>
                    <li>Hold your breath (7 sec)</li>
                    <li>Exhale gently (8 sec)</li>
                    <li>Repeat for selected cycles</li>
                </ul>
            </div>

            <!-- CYCLE SELECTOR (KEPT) -->
            <div class="cycle-selector-wrap">
                <p class="cycle-selector-label">🎯 Select Number of Cycles</p>

                <div class="cycle-selector">
                    @for ($i = 1; $i <= 5; $i++)
                        <button class="cycle-dot {{ $i == 4 ? 'active' : '' }}" data-val="{{ $i }}">{{ $i }}</button>
                    @endfor
                </div>

                <p class="cycle-chosen">
                    Selected: <strong id="cycleChosenVal">4</strong> cycles
                </p>
            </div>

        </div>
    </div>

    <!-- MAIN -->
    <div class="breathing-main">

        <div class="main-card">

            <!-- TAB SWITCH -->
            <div class="toggle-container">
                <button id="btn-breathing" class="tab-btn active" onclick="switchTab('breathing')">🫁 Breathing</button>
                <button id="btn-mood" class="tab-btn" onclick="switchTab('mood')">😊 Mood Tracking</button>
            </div>

            <!-- ========================= -->
            <!-- BREATHING SECTION -->
            <!-- ========================= -->
            <div id="breathing-section" class="section-content">

                <p class="subtitle">Breathe and find your calm</p>

                <div class="exercise-area">

                    <!-- LEAVES -->
                    @for ($i = 1; $i <= 7; $i++)
                        <img src="{{ asset('images/breathing-leaf.png') }}" class="leaf l{{ $i }}">
                    @endfor

                    <!-- TIMER -->
                    <div class="timer-container">
                        <div class="circle phase-inhale" id="breath-circle">
                            <h2 id="timer">4</h2>
                            <p id="phase">INHALE</p>
                        </div>

                        <p class="cycle-text">
                            CYCLE: <span id="cycle">1</span>
                        </p>
                    </div>

                </div>

                <!-- BUTTONS -->
                <div class="ctrl-buttons">
                    <button class="ctrl-btn" onclick="startBreathing()">▶ Start</button>
                    <button class="ctrl-btn" onclick="restartBreathing()">⟳ Restart</button>
                </div>

            </div>

            <!-- ========================= -->
            <!-- MOOD TRACKING SECTION -->
            <!-- ========================= -->
            <div id="mood-section" class="section-content hidden">

                <div class="mood-content">

                    <!-- LEFT -->
                    <div class="mood-left">

                        <!-- EMOJIS -->
                        <div class="emoji-row">

                            <div class="emoji-item" onclick="selectMood(this,'😄')">
                                <div class="emoji-circle">😄</div>
                                <p>Joyful</p>
                            </div>

                            <div class="emoji-item" onclick="selectMood(this,'😊')">
                                <div class="emoji-circle">😊</div>
                                <p>Happy</p>
                            </div>

                            <div class="emoji-item" onclick="selectMood(this,'😐')">
                                <div class="emoji-circle">😐</div>
                                <p>Neutral</p>
                            </div>

                            <div class="emoji-item" onclick="selectMood(this,'😰')">
                                <div class="emoji-circle">😰</div>
                                <p>Anxious</p>
                            </div>

                            <div class="emoji-item" onclick="selectMood(this,'😔')">
                                <div class="emoji-circle">😔</div>
                                <p>Sad</p>
                            </div>

                        </div>

                        <!-- TEXT -->
                        <textarea id="mood-note" placeholder="*optional note: Add context to your mood."></textarea>

                        <!-- SAVE -->
                        <div class="save-container">
                            <button class="save-btn" onclick="saveMood()">Save Mood</button>
                        </div>

                    </div>

                    <!-- DIVIDER -->
                    <div class="mood-divider"></div>

                    <!-- RIGHT -->
                    <div class="mood-right">
                        <h3>Mood Entries</h3>
                        <div id="entries-list" class="entries-list"></div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</main>

<!-- TOAST -->
<div class="mood-toast" id="moodToast">
    <div class="mood-toast-img-wrap">
        <img id="toastPetImg" src="{{ asset('images/egg.png') }}">
    </div>
    <div class="mood-toast-body">
        <p id="toastTitle"></p>
        <p id="toastMsg"></p>
        <p id="toastTip"></p>
    </div>
    <button id="toastClose">✕</button>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/aire-data.js') }}"></script>
<script src="{{ asset('js/breathing-mt.js') }}"></script>
@endpush