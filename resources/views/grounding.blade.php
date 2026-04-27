@extends('layouts.app')

@section('title', 'Airé — Grounding Exercise')
@section('body-class', 'grounding-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/grounding.css') }}">
    <link rel="stylesheet" href="{{ asset('css/booster.css') }}">
    <style>
        body.grounding-page {
            background-image: url('{{ asset("images/landing-butterfly-right.jpeg") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .grounding-container { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); border-radius: 24px; margin: 20px auto; padding: 24px 32px 60px; max-width: 860px; }
        .method-card, .step-btn, .step-panel { background: rgba(255, 255, 255, 0.9); }
    </style>
@endpush

@section('content')
<a href="{{ route('home') }}" class="btn-back">&#8592; Back to Home</a>
<div class="grounding-container">
    <img src="{{ asset('images/breathing-leaf.png') }}" alt="" class="butterfly-top-right">
    <h1 class="grounding-title">Grounding Exercise</h1>
    <div class="grounding-layout">
        <div class="method-card">
            <p class="method-title">5-4-3-2-1 method</p>
            <div class="method-progress" id="method-progress"><div class="progress-fill" id="progress-fill"></div></div>
            <p class="progress-label" id="progress-label">0 / 5 complete</p>
        </div>
        <div class="steps-list">
            @php $steps = [['label' => '5 Things you see', 'placeholder' => 'I see...', 'count' => 5],['label' => '4 Things you feel', 'placeholder' => 'I feel...', 'count' => 4],['label' => '3 Things you hear', 'placeholder' => 'I hear...', 'count' => 3],['label' => '2 Things you smell', 'placeholder' => 'I smell...', 'count' => 2],['label' => '1 Thing you like', 'placeholder' => 'I like...', 'count' => 1]]; @endphp
            @foreach ($steps as $index => $step)
            <div class="step-btn" id="step-{{ $index }}" onclick="toggleStep({{ $index }})">
                <span class="step-icon">🌿</span><span class="step-label">{{ $step['label'] }}</span><span class="step-arrow" id="arrow-{{ $index }}">›</span>
            </div>
            <div class="step-panel" id="panel-{{ $index }}">
                <div class="step-inputs" id="inputs-{{ $index }}">@for ($i = 0; $i < $step['count']; $i++)<input type="text" placeholder="{{ $step['placeholder'] }}" class="step-input">@endfor</div>
                <button class="btn-done-step" onclick="doneStep(event, {{ $index }})">Done ✓</button>
            </div>
            @endforeach
        </div>
    </div>
</div>
<div id="focusOverlay" class="focus-overlay hidden"><div class="focus-card"><h2 id="focusTitle">5 Things you see</h2><p class="focus-guide">Take a moment. Gently look around you…</p><div id="focusInputs" class="step-inputs"></div><div style="margin-top:20px;display:flex;gap:12px;justify-content:center;"><button id="focusDoneBtn" class="btn-done-step">Done ✓</button><button id="focusNextBtn" class="btn-ghost">Next →</button></div></div></div>
<div id="completion-msg" class="completion-msg">🎉 Amazing! You've completed the grounding exercise! 🌿</div>

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
<script src="{{ asset('js/grounding.js') }}"></script>
@endpush