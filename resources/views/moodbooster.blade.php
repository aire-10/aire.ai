@extends('layouts.app')

@section('title', 'Mood Booster')
@section('body-class', 'booster-page')

@section('content')
<main class="bodybooster-page">
  <div class="page-content">
    <a href="{{ url('home') }}" class="btn-back">&#8592; Back to Home</a>
    <div class="mb-layout">
      <div class="mb-main">
        <div class="mb-title"><h1>✨ Mood Booster ✨</h1><h2>Choose Your Mood Boost</h2><p>Quick activities to lift your mood</p></div>
        <div class="mb-grid">
          <div class="mb-card" onclick="location.href='{{ url('minitask') }}'"><img src="{{ asset('images/task.jpeg') }}" alt="Quick Mini-Tasks"><h3>Quick Mini-Tasks</h3><button class="btn btn-green">Start</button></div>
          <div class="mb-card" onclick="location.href='{{ url('mindreset') }}'"><img src="{{ asset('images/mindreset.jpeg') }}" alt="Mind Reset"><h3>Mind Reset</h3><button class="btn btn-green">Try Now</button></div>
          <div class="mb-card" onclick="location.href='{{ url('moodlifting') }}'"><img src="{{ asset('images/moodlifting.jpeg') }}" alt="Mood Lifting"><h3>Mood Lifting</h3><button class="btn btn-green">Try Now</button></div>
          <div class="mb-card" onclick="location.href='{{ url('bodybooster') }}'"><img src="{{ asset('images/dumbell.jpeg') }}" alt="Body-Based Boosters"><h3>Body-Based Boosters</h3><button class="btn btn-green">Start</button></div>
        </div>
      </div>
      <div class="mb-sidebar">
        <div class="sidebar-card"><h4>Instant Mood Check-In</h4><p class="sidebar-sub">How do you feel right now?</p><div class="mood-grid"><button class="mood-btnmb" data-mood="happy">😊 Happy</button><button class="mood-btnmb" data-mood="neutral">😐 Neutral</button><button class="mood-btnmb" data-mood="anxious">😰 Anxious</button><button class="mood-btnmb" data-mood="sad">😢 Sad</button></div><button class="btn btn-green update-mood-btnmb" onclick="updateMood()">Update Mood</button></div>
        <div class="sidebar-card"><h4>Your Progress This Week</h4><div class="streak-row"><span class="streak-text">0 Day Streak 🔥</span></div><div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 0%"></div></div><p class="keep-up">Keep it up!</p></div>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/aire-data.js') }}"></script>
<script src="{{ asset('js/booster.js') }}"></script>
@endpush