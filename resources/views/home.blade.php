@extends('layouts.app')

@section('title', 'Home')
@section('body-class', 'dash-body')

@section('content')
<main class="dash-wrapper">
  <div class="dash-grid">
    <a class="dash-card dash-pet" href="{{ url('growth') }}">
      <div class="dash-pet-img-wrap"><img id="dashPetImg" src="{{ asset('/images/egg.png') }}" alt="Your butterfly" /></div>
      <div class="dash-pet-right">
        <p class="dash-pet-stage" id="dashPetStage">Egg</p>
        <h3 id="dashPetMsg">Your journey is just beginning. 🥚</h3>
        <div class="dash-pet-stats"><span class="dash-pet-stat" id="dashPetStreak">🔥 0 streak</span><span class="dash-pet-stat" id="dashPetDays">📅 0 days</span></div>
        <span class="dash-pill-btn">View Pet</span>
      </div>
    </a>

    <section class="dash-card dash-affirm">
      <div class="dash-affirm-inner">
        <h3>Daily Affirmations:</h3>
        <p id="affirmationText">I am allowed to grow at my own pace, just like my butterfly.</p>
        <img src="{{ asset('images/butterflyjar.png') }}" alt="Butterfly jar" />
      </div>
    </section>

    <a class="dash-card dash-chat" href="{{ url('chat') }}">
      <div class="dash-chat-top"><div class="dash-badge"><img src="{{ asset('images/logo.png') }}" alt="" /></div><h3>Chat with Airé</h3></div>
      <div class="dash-chat-img"><img src="{{ asset('images/butterflyhillnobg.png') }}" /></div>
    </a>

    <div class="dash-card dash-mood">
      <h3>Mood Check-in</h3>
      <p class="dash-mood-q">How are you feeling today?</p>
      <div class="dash-moods" id="dashMoods">
        <span class="dash-mood-emoji" data-mood="joyful">😄</span>
        <span class="dash-mood-emoji" data-mood="happy">😊</span>
        <span class="dash-mood-emoji" data-mood="neutral">😐</span>
        <span class="dash-mood-emoji" data-mood="sad">😢</span>
        <span class="dash-mood-emoji" data-mood="tired">😔</span>
      </div>
      <div class="dash-mood-saved" id="dashMoodSaved"></div>
    </div>

    <section class="dash-card dash-tools">
      <h3>Self-Care Tools</h3>
      <div class="dash-tools-grid">
        <a class="tool-btn" href="{{ url('breathing-mt') }}">Breathing & Mood Tracker</a>
        <a class="tool-btn" href="{{ url('grounding') }}">Grounding Exercise</a>
        <a class="tool-btn" href="{{ url('moodbooster') }}">Mood Booster</a>
      </div>
    </section>
  </div>
</main>

<div class="mood-toast" id="moodToast">
  <div class="mood-toast-img-wrap"><img id="toastPetImg" src="{{ asset('images/egg.png') }}" /></div>
  <div class="mood-toast-body"><p id="toastTitle">🌱 Your butterfly is growing!</p><p id="toastMsg"></p><p id="toastTip"></p></div>
  <button id="toastClose">✕</button>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/aire-data.js') }}"></script>
<script src="{{ asset('js/home.js') }}"></script>
@endpush