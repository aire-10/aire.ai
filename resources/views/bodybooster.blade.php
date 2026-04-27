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

            <!-- ✅ ADD THIS PROGRESS BAR -->
            <div class="task-progress">
                <div class="task-bar">
                    <div class="task-bar-fill"></div>
                </div>
            </div>

            <div class="bb-card-footer">
                <button class="btn btn-green task-start-btn" data-duration="{{ $task['dur'] }}">
                    Start
                </button>
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
  <div class="mood-toast-img-wrap">
    <img id="toastPetImg" src="{{ asset('images/egg.png') }}" />
  </div>
  <div class="mood-toast-body">
    <p id="toastTitle">🌱 Your butterfly is growing!</p>
    <p id="toastMsg"></p>
    <p id="toastTip"></p>
  </div>
  <button id="toastClose">✕</button>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/aire-data.js') }}"></script>
<script src="{{ asset('js/booster.js') }}"></script>
@endpush