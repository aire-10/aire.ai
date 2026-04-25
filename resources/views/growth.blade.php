@extends('layouts.app')

@section('title', 'Butterfly Pet')
@section('body-class', 'dash-body')

@push('head-scripts')
  <script src="{{ asset('js/aire-data.js') }}" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <script src="{{ asset('js/growth.js') }}" defer></script>
@endpush

@section('content')
  <main class="pet-page">
    <section class="pet-hero">
      <div class="pet-butterfly-wrap"><img class="pet-butterfly-img" id="petImg" src="{{ asset('images/egg.png') }}" alt="Egg" /><div class="pet-stage-label" id="petStageLabel">Egg 🥚</div></div>
      <div class="pet-xp-wrap"><div class="pet-xp-bar-outer"><div class="pet-xp-bar-fill" id="petXpFill"></div><span class="pet-xp-text" id="petLevelLabel">Lvl 1</span></div><p class="pet-xp-subtext">Grow your butterfly by completing self-care tasks!</p></div>
    </section>
    <section class="pet-card"><h2 class="pet-card-title">🌱 Daily Growth Tasks</h2><p class="pet-card-sub">Resets every midnight</p>
      <div class="pet-action-item" id="actionJournal"><div class="pet-action-check" id="checkJournal"></div><div class="pet-action-info"><div class="pet-action-name">Write a Journal Entry</div><div class="pet-action-pts">+1 growth point 🦋</div></div></div>
      <div class="pet-action-item" id="actionMood"><div class="pet-action-check" id="checkMood"></div><div class="pet-action-info"><div class="pet-action-name">Log your Mood (Chat)</div><div class="pet-action-pts">+0.5 growth points 🌿</div></div></div>
      <div class="pet-action-item" id="actionGrounding"><div class="pet-action-check" id="checkGrounding"></div><div class="pet-action-info"><div class="pet-action-name">Complete Grounding Exercise</div><div class="pet-action-pts">+0.5 growth points 🌿</div></div></div>
    </section>
    <section class="pet-card"><h2 class="pet-card-title">🏆 Your Streak Badges</h2><p class="pet-card-sub">Celebrate your milestones!</p><div class="streak-badges" id="streakBadges"></div></section>
    <section class="pet-card"><h2 class="pet-card-title">📊 Mood Over Time</h2><p class="pet-card-sub">Your emotional pattern across the last 14 days.</p><div class="chart-wrap"><canvas id="moodChart"></canvas></div><div class="chart-legend"><span class="legend-dot" style="background:#3a8c3a;"></span> Positive &nbsp;<span class="legend-dot" style="background:#b0a060;"></span> Neutral &nbsp;<span class="legend-dot" style="background:#a07070;"></span> Low</div></section>
  </main>
@endsection