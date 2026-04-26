@extends('layouts.app')

@section('title', 'Body-Based Boosters')
@section('body-class', 'booster-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booster.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cross-off.css') }}">
@endpush

@section('content')
<div class="page-content">
    <a href="{{ url('moodbooster') }}" class="btn-back">&#8592; Back to Mood Boosters</a>

    <div class="bb-header">
        <div class="bb-header-text">
            <h1>Body-Based Boosters</h1>
            <p>Move and refresh yourself</p>
        </div>
        <img src="{{ asset('/images/dumbell.jpeg') }}" alt="Dumbbell" class="bb-header-img">
    </div>
    
    <div class="progress-text" id="progressText">
        You’ve completed 0 / 0 tasks 💚
    </div>

    <div class="bb-grid">
        @php
            $tasks = [
                ['name' => 'Stretch your arms overhead', 'dur' => 10, 'id' => 'stretch'],
                ['name' => 'Walk around the room', 'dur' => 15, 'id' => 'walk'],
                ['name' => 'Gently shake your hands', 'dur' => 10, 'id' => 'hands'],
                ['name' => 'Touch something cool (bottle, figurine)', 'dur' => 10, 'id' => 'cool'],
                ['name' => 'Rotate your ankle', 'dur' => 15, 'id' => 'ankle'],
                ['name' => 'Place your hand on your chest and breathe', 'dur' => 15, 'id' => 'chest'],
            ];
        @endphp

        @foreach($tasks as $task)
        <div class="bb-card">
            <div class="bb-card-top">
                <div class="bb-task-info">
                    <div class="bb-task-name"><span>🌿</span> {{ $task['name'] }}</div>
                    <div class="bb-duration">{{ $task['dur'] }} seconds</div>
                </div>
                <div class="bb-check-circle" id="circle-{{ $task['id'] }}"></div>
            </div>
            <div class="bb-card-footer">
                <button class="btn btn-green task-start-btn" data-duration="{{ $task['dur'] }}">Start</button>
            </div>
        </div>
        @endforeach
    </div>

    <div class="cross-off-footer">
        <button class="btn-outline reset-all-btn">Reset All Items</button>
    </div>
</div>

<div class="particles">
    @for($i = 0; $i < 5; $i++)
        <span style="left:{{ 10 + ($i*20) }}%; animation-delay:{{ rand(0, 6) }}s;"></span>
    @endfor
</div>

<div class="mood-toast" id="moodToast">
    <div class="mood-toast-body">
        <p class="mood-toast-title" id="toastTitle"></p>
        <p class="mood-toast-msg" id="toastMsg"></p>
        <p class="mood-toast-tip" id="toastTip"></p>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/progress.js') }}"></script>
<script src="{{ asset('js/booster.js') }}"></script>
<script src="{{ asset('js/aire-data.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const keys = ["minitasks-progress", "bodybooster-progress", "mindreset-progress", "moodlifting-progress", "grounding-progress-inputs", "grounding-progress-steps"];
    checkDailyReset(keys);
    scheduleMidnightReset(keys);

    initProgress({
        selector: ".bb-card",
        storageKey: "bodybooster-progress",
        activeClass: "completed"
    });

    const resetBtn = document.querySelector(".reset-all-btn");
    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            document.querySelectorAll(".bb-card").forEach(card => {
                card.classList.remove("completed");
                const btn = card.querySelector(".btn");
                if (btn) { btn.disabled = false; btn.className = "btn btn-green task-start-btn"; btn.textContent = "Start"; }
                const circle = card.querySelector(".bb-check-circle");
                if (circle) { circle.classList.remove("done"); circle.textContent = ""; }
                const leaf = card.querySelector(".bb-task-name span");
                if (leaf) leaf.textContent = "🌿";
            });
            localStorage.removeItem("bodybooster-progress");
            updateProgress();
            rebindStartButtons();
            showEncouragement("bodybooster");
        });
    }

    function rebindStartButtons() {
        document.querySelectorAll('.task-start-btn').forEach(btn => {
            btn.onclick = () => {
                const card = btn.closest('.bb-card');
                const duration = parseInt(btn.getAttribute('data-duration')) || 10;
                startTaskTimer(btn, card, duration);
            };
        });
    }
});
</script>
@endpush