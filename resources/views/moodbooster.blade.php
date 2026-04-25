@extends('layouts.app')

@section('title', 'Mood Booster')
@section('body-class', 'moodbooster-page')

@section('content')
<main class="page-content">

  <!-- BACK -->
  <a href="{{ url('home') }}" class="btn-back">← Back to Home</a>

  <div class="mb-layout">

    <!-- MAIN -->
    <div class="mb-main">

      <div class="mb-title">
        <h1>✨ Mood Booster ✨</h1>
        <h2>Choose Your Mood Boost</h2>
        <p>Quick activities to lift your mood</p>
      </div>

      <div class="mb-grid">

        <!-- MINI TASK -->
        <div class="mb-card" onclick="location.href='{{ url('minitask') }}'">
          <img src="{{ asset('images/task.jpeg') }}">
          <h3>Quick Mini-Tasks</h3>
          <button class="btn btn-green">Start</button>
        </div>

        <!-- MIND RESET -->
        <div class="mb-card" onclick="location.href='{{ url('mindreset') }}'">
          <img src="{{ asset('images/mindreset.jpeg') }}">
          <h3>Mind Reset</h3>
          <button class="btn btn-green">Try Now</button>
        </div>

        <!-- MOOD LIFTING -->
        <div class="mb-card" onclick="location.href='{{ url('moodlifting') }}'">
          <img src="{{ asset('images/moodlifting.jpeg') }}">
          <h3>Mood Lifting</h3>
          <button class="btn btn-green">Try Now</button>
        </div>

        <!-- BODY BOOSTER -->
        <div class="mb-card" onclick="location.href='{{ url('bodybooster') }}'">
          <img src="{{ asset('images/dumbell.jpeg') }}">
          <h3>Body-Based Boosters</h3>
          <button class="btn btn-green">Start</button>
        </div>

      </div>

    </div>

  </div>

</main>
@endsection


@push('styles')
<link rel="stylesheet" href="{{ asset('css/booster.css') }}">
@endpush


@push('scripts')
<script src="{{ asset('js/aire-data.js') }}"></script>
@endpush