@extends('layouts.app')

@section('title', 'Mood Lifting Thoughts')
@section('body-class', 'moodlifting-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booster.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cross-off.css') }}">
    <style>
        .ml-header { margin-bottom: 15px; position: relative; }
        .thought-card {
            background: rgba(190, 210, 190, 0.52);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 18px;
            padding: 28px 22px;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.96rem;
            font-weight: 600;
            line-height: 1.45;
            color: #1a3520;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .thought-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); background: rgba(210, 228, 210, 0.75); }
        .thought-card.crossed-off { padding-right: 35px; }
    </style>
@endpush

@section('content')
<div class="page-content">
    <a href="{{ url('moodbooster') }}" class="btn-back">&#8592; Back to Mood Boosters</a>

    <div class="ml-header">
        <h1>Mood Lifting Thoughts</h1>
        <span class="ml-header-emoji">❄️</span>
        <img src="{{ asset('moodlifting.jpeg') }}" alt="Mood Lifting" class="ml-header-icon">
    </div>

    <div class="progress-text" id="progressText">
        You’ve completed 0 / 0 tasks 💚
    </div>

    <div class="ml-grid">
        @php
            $thoughts = [
                'Recall a happy memory',
                'Think of your favourite food',
                "You're still here.<br>That's enough.",
                "What's one thing you did okay today?",
                'Tell yourself: "This feeling is okay, and it will pass."',
                "If you're tired, what's one tiny rest you can take?"
            ];
        @endphp
        @foreach($thoughts as $thought)
        <div class="thought-card cross-off-item">
            <span class="cross-off-text">{!! $thought !!}</span>
        </div>
        @endforeach
    </div>

    <div class="cross-off-footer">
        <button class="btn-outline cross-off-reset-btn">Reset All Thoughts</button>
    </div>
</div>

<img src="{{ asset('logo.png') }}" alt="Butterfly" class="ml-butterfly">

<div class="mood-toast" id="moodToast">
    <div class="mood-toast-body">
        <p class="mood-toast-title" id="toastTitle"></p>
        <p class="mood-toast-msg" id="toastMsg"></p>
        <p class="mood-toast-tip" id="toastTip"></p>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/booster.js') }}"></script>
<script src="{{ asset('js/progress.js') }}"></script>
<script src="{{ asset('js/aire-data.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const keys = ["minitasks-progress", "bodybooster-progress", "mindreset-progress", "moodlifting-progress", "grounding-progress-inputs", "grounding-progress-steps"];
    checkDailyReset(keys);
    scheduleMidnightReset(keys);

    initProgress({
        selector: ".thought-card",
        storageKey: "moodlifting-progress",
        activeClass: "crossed-off"
    });
    
    document.querySelectorAll(".thought-card").forEach(card => {
        card.addEventListener("click", () => {
            showEncouragement("moodlifting");
            setTimeout(() => {
                updateProgress();
                if(typeof saveMoodLiftingProgress === 'function') saveMoodLiftingProgress();
                if(typeof checkDailyCompletion === 'function') checkDailyCompletion();
                if(typeof showSparkles === 'function') showSparkles();
            }, 50);
        });
    });

    const resetBtn = document.querySelector(".cross-off-reset-btn");
    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            document.querySelectorAll(".thought-card").forEach(item => item.classList.remove("crossed-off"));
            localStorage.removeItem("moodlifting-progress");
            updateProgress();
        });
    }
});
</script>
@endpush