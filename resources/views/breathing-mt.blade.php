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
        .main-screen { background: transparent; }
        .main-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(8px); }
    </style>
@endpush

@section('content')
<a href="{{ route('home') }}" class="back-home-btn">&#8592; Back to Home</a>
<main class="main-screen">
    <div class="breathing-sidebar">
        <div class="sidebar-card"><button class="sidebar-toggle" onclick="toggleSidebar('progress')">BREATHING DASHBOARD <span class="toggle-arrow" id="progressArrow">▼</span></button><div class="sidebar-body" id="progressBody"><div class="progress-bars" id="progressBars"></div><p class="progress-label" id="progressLabel">This week's Breathings:<br><strong>0 Cycles Completed</strong></p></div></div>
    </div>
    <div class="breathing-main">
        <div class="main-card" id="main-card">
            <div class="toggle-container"><button class="tab-btn active" id="btn-breathing" onclick="switchTab('breathing')">🫁 Breathing</button><button class="tab-btn" id="btn-mood" onclick="switchTab('mood')">😊 Mood Tracking</button></div>
            <div id="breathing-section" class="section-content">
                <div class="exercise-area">@for ($i = 1; $i <= 7; $i++)<img src="{{ asset('images/breathing-leaf.png') }}" class="leaf l{{ $i }}" alt="">@endfor<div class="timer-container"><div class="circle phase-inhale" id="breath-circle"><h2 id="timer">4</h2><p id="phase">INHALE</p></div><p class="cycle-text">CYCLE: <span id="cycle">1</span></p></div></div>
            </div>
        </div>
    </div>
</main>
<div class="mood-toast" id="moodToast"><div class="mood-toast-img-wrap"><img id="toastPetImg" src="{{ asset('images/egg.png') }}" /></div><div class="mood-toast-body"><p id="toastTitle"></p><p id="toastMsg"></p><p id="toastTip"></p></div><button id="toastClose">✕</button></div>
@endsection

@push('scripts')
<script src="{{ asset('js/aire-data.js') }}"></script>
<script src="{{ asset('js/breathing-mt.js') }}"></script>
@endpush