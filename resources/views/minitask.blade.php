@extends('layouts.app')

@section('title', 'Mini Tasks')

@section('body-class', 'minitask-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/booster.css') }}">
<link rel="stylesheet" href="{{ asset('css/cross-off.css') }}">
@endpush

@section('content')

<div class="page-content">

<a href="{{ route('moodbooster') }}" class="btn-back">
← Back to Mood Boosters
</a>

<div class="mt-header">
  <div class="mt-header-text">
    <h1>Quick Mini-Tasks</h1>
    <p>Small steps, Big difference</p>
  </div>
  <img src="{{ asset('images/task.jpeg') }}" class="mt-header-icon">
</div>

<div id="progressText">You’ve completed 0 / 8 tasks 💚</div>

{{-- ✅ INLINE layout (DO NOT include for now) --}}
<div class="mt-layout">

    <!-- LEFT -->
    <div class="mt-col">
        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Take 3 slow breaths</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>

        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Roll your shoulders back 10x</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>

        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Raise & relax your eyebrows 5x</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>

        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Blink slowly 5x</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>
    </div>

    <!-- CENTER -->
    <div class="mt-center-card">
        <img src="{{ asset('images/daisybutterfly.png') }}">
    </div>

    <!-- RIGHT -->
    <div class="mt-col">
        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Stretch your neck gently</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>

        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Sit up straight for 10 seconds</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>

        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Force a laugh for 30 seconds</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>

        <div class="task-card">
            <div class="task-row">
                <span class="task-leaf">🌿</span>
                <span class="task-text">Force a smile for 20 seconds</span>
            </div>
            <div class="task-progress">
            <div class="task-bar">
            <div class="task-bar-fill"></div>
            </div>
            </div>

            <button class="btn btn-green task-start-btn">Start</button>
        </div>
    </div>

</div>

<div class="cross-off-footer">
    <button class="btn-outline reset-all-btn">
    Reset All Items
    </button>
</div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/aire-data.js') }}"></script>
<script src="{{ asset('js/booster.js') }}"></script>
@endpush