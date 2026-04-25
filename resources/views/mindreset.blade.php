@extends('layouts.app')

@section('title', 'Gentle Mind Reset')
@section('body-class', 'booster-page')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/booster.css') }}">
  <link rel="stylesheet" href="{{ asset('css/cross-off.css') }}">
@endpush

@section('content')
  <div class="page-content">
    <a href="{{ url('/moodbooster') }}" class="btn-back">&#8592; Back to Mood Boosters</a>
    <div class="mr-header"><div class="bb-header-text"><h1>Gentle Mind Reset</h1><p>Clear the mental fog</p></div><img src="{{ asset('images/mindreset.jpeg') }}" alt="Mind Reset" class="bb-header-img"></div>
    <div class="progress-text" id="progressText">You’ve completed 0 / 0 tasks 💚</div>
    <div class="cross-off-container">
      @php $items = ['Take 3 deep, slow breaths','Notice one thing you can smell','Wash your face with cool water','Let out a big sigh','Unclench your jaw and drop your shoulders','Close your eyes for 30 seconds']; @endphp
      @foreach($items as $item)
        <div class="mr-item cross-off-item"><div class="cross-off-box"></div><span class="cross-off-text">{{ $item }}</span></div>
      @endforeach
    </div>
    <div class="cross-off-footer"><button class="btn-outline cross-off-reset-btn">Reset All Items</button></div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('js/aire-data.js') }}"></script>
  <script src="{{ asset('js/booster.js') }}"></script>
@endpush