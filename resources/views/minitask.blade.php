@extends('layouts.app')

@section('title', 'Quick Mini-Tasks – Airé')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booster.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cross-off.css') }}">
@endpush

@section('content')
<div class="page-content">
    <a href="{{ route('moodbooster') }}" class="btn-back">&#8592; Back to Mood Boosters</a>

    <div class="mt-header">
        <div class="mt-header-text">
            <h1>Quick Mini-Tasks</h1>
            <p>Small steps, Big difference</p>
        </div>
        <img src="{{ asset('assets/task.jpeg') }}" alt="Tasks icon" class="mt-header-icon">
    </div>

    <div class="progress-text" id="progressText">
        You’ve completed 0 / 0 tasks 💚
    </div>
    
    <div class="mt-layout">
        <div class="mt-col">
            <div class="task-card">
                <div class="task-row">
                    <span class="task-leaf">🌿</span>
                    <span class="task-text">Take 3 slow breaths</span>
                </div>
                <button class="btn btn-green task-start-btn breathing-task" data-duration="15">Start</button>
            </div>
            </div>

        <div class="mt-center-card">
            <img src="{{ asset('assets/daisybutterfly.png') }}" alt="Butterfly" id="butterfly">
        </div>

        <div class="mt-col">
            <div class="task-card">
                <div class="task-row">
                    <span class="task-leaf">🌿</span>
                    <span class="task-text">Stretch your neck gently</span>
                </div>
                <button class="btn btn-green task-start-btn" data-duration="15">Start</button>
            </div>
            </div>
    </div>

    <div class="cross-off-footer">
        <button class="btn-outline reset-all-btn">Reset All Items</button>
    </div>
</div>

<script src="{{ asset('js/booster.js') }}"></script>
<script src="{{ asset('js/progress.js') }}"></script>
<script>
    // Include the JS logic provided in your HTML file here
</script>
@endsection