@extends('layouts.app')

@section('title', 'Mood Lifting Thoughts')
@section('body-class', 'moodlifting-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booster.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cross-off.css') }}">
@endpush

@section('content')
<div class="page-content">
    <a href="{{ url('moodbooster') }}" class="btn-back">&#8592; Back to Mood Boosters</a>
    <div class="ml-header"><h1>Mood Lifting Thoughts</h1><span class="ml-header-emoji">❄️</span><img src="{{ asset('images/moodlifting.jpeg') }}" alt="Mood Lifting" class="ml-header-icon"></div>
    <div class="progress-text" id="progressText">You’ve completed 0 / 0 tasks 💚</div>
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
    <div class="ml-card cross-off-item">
        <span class="cross-off-text">{!! $thought !!}</span>
    </div>
    @endforeach
    </div>
    <div class="cross-off-footer">
        <button class="btn-outline reset-all-btn">
        Reset All Thoughts
        </button>
    </div>
</div>
<img src="{{ asset('images/logo.png') }}" alt="Butterfly" class="ml-butterfly">

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