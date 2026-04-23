@extends('layouts.app')

@section('title', 'Gentle Mind Reset')
@section('body-class', 'booster-page')

@push('styles')
  <link rel="stylesheet" href="{{ asset('booster.css') }}">
  <link rel="stylesheet" href="{{ asset('cross-off.css') }}">
  <style>
    .mr-header { margin-bottom: 22px; position: relative; }
    .mr-item {
      background: rgba(255,255,255,0.88);
      border-radius: 13px; padding: 16px 20px;
      display: flex; align-items: center; gap: 10px;
      font-size: 0.95rem; font-weight: 600;
      box-shadow: 0 1px 8px rgba(0,0,0,0.06); transition: all 0.2s;
    }
    .mr-item:hover { background: rgba(255,255,255,0.96); transform: translateX(4px); }
  </style>
@endpush

@section('content')
  <div class="page-content">
    <a href="{{ url('/moodbooster') }}" class="btn-back">&#8592; Back to Mood Boosters</a>

    <div class="mr-header">
      <div class="bb-header-text">
        <h1>Gentle Mind Reset</h1>
        <p>Clear the mental fog</p>
      </div>
      <img src="{{ asset('mindreset.jpeg') }}" alt="Mind Reset" class="bb-header-img">
    </div>

    <div class="progress-text" id="progressText">You’ve completed 0 / 0 tasks 💚</div>

    <div class="cross-off-container">
      @php
        $items = [
          'Take 3 deep, slow breaths',
          'Notice one thing you can smell',
          'Wash your face with cool water',
          'Let out a big sigh',
          'Unclench your jaw and drop your shoulders',
          'Close your eyes for 30 seconds'
        ];
      @endphp
      @foreach($items as $item)
        <div class="mr-item cross-off-item">
          <div class="cross-off-box"></div>
          <span class="cross-off-text">{{ $item }}</span>
        </div>
      @endforeach
    </div>

    <div class="cross-off-footer">
      <button class="btn-outline cross-off-reset-btn">Reset All Items</button>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('booster.js') }}"></script>
  <script src="{{ asset('progress.js') }}"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      initProgress({ selector: ".mr-item", storageKey: "mindreset-progress", activeClass: "done" });
    });
  </script>
@endpush