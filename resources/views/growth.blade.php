@extends('layouts.app')

@section('title', 'Butterfly Pet')
@section('body-class', 'dash-body')

@push('styles')
<style>
    .pet-page { max-width: 1000px; margin: 0 auto; padding: 20px; font-family: 'Inter', sans-serif; }
    .hero-container { background: #d3e9d3; border-radius: 20px; padding: 40px; text-align: center; margin-bottom: 30px; position: relative; }
    
    /* XP Bar */
    .xp-bar-container { background: rgba(0,0,0,0.1); border-radius: 15px; height: 20px; position: relative; margin: 20px auto; overflow: hidden; width: 80%; }
    .xp-fill { height: 100%; transition: width 0.5s ease; }

    .kpi-row { display: flex; justify-content: space-around; margin-top: 25px; }
    .kpi-card { text-align: center; }
    .kpi-card strong { font-size: 1.5rem; display: block; }
    .kpi-card small { color: #555; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; }

    /* Task List */
    .task-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .task-item { display: flex; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
    .check-circle { 
        width: 30px; height: 30px; border-radius: 50%; 
        border: 2px solid #e0e0e0; margin-right: 15px; 
        display: flex; align-items: center; justify-content: center;
    }

    /* Streak Badges Styling - MISSING IN YOUR ORIGINAL */
    .streak-badge { text-align: center; flex: 1; opacity: 0.3; grayscale: 100%; transition: 0.3s; }
    .streak-badge.earned { opacity: 1; grayscale: 0%; }
    .streak-badge-icon { font-size: 2rem; margin-bottom: 5px; }
    .streak-badge-days { font-size: 0.8rem; font-weight: bold; color: #666; }

    /* Timeline Styling - MISSING IN YOUR ORIGINAL */
    .timeline-item { display: flex; padding: 15px; border-left: 3px solid #eee; margin-left: 20px; position: relative; background: #fafafa; margin-bottom: 10px; border-radius: 0 10px 10px 0; }
    .timeline-mood-icon { font-size: 1.5rem; margin-right: 15px; }
    .timeline-date { font-weight: bold; display: block; font-size: 0.9rem; }
</style>
@endpush

@push('head-scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <script src="{{ asset('js/aire-data.js') }}"></script> 
  <script src="{{ asset('js/growth.js') }}" defer></script>
@endpush

@section('content')
<main class="pet-page">
    <div class="hero-container">
        <div class="pet-visual">
            <img id="petImg" src="{{ asset('images/' . $stage['img']) }}" alt="Pet Stage" style="width: 120px;">
            <h2 id="petStageLabel" style="margin-top: 10px; color: {{ $stage['color'] }}">{{ $stage['label'] }}</h2>
        </div>

        <div class="xp-bar-container">
            <div id="petXpFill" class="xp-fill" style="width: {{ ($growthPoints / ($stage['xpMax'] ?? 10)) * 100 }}%; background: {{ $stage['color'] }}"></div>
            <span id="petXpText" style="position: absolute; width: 100%; left: 0; top: 2px; font-weight: bold;">{{ $growthPoints }} / {{ $stage['xpMax'] }} pts</span>
        </div>
        <p id="petXpHint" style="font-size: 0.9rem; color: #555;">Loading progress...</p>

        <div class="kpi-row">
            <div class="kpi-card"><strong><span id="pkStreak">{{ $streak }}</span></strong><br><small>🔥 STREAK</small></div>
            <div class="kpi-card"><strong><span id="pkDays">{{ $daysTracked }}</span></strong><br><small>📅 DAYS TRACKED</small></div>
            <div class="kpi-card"><strong><span id="pkToday">{{ $todayCheckIns }}</span></strong><br><small>✅ TODAY</small></div>
            <div class="kpi-card"><strong><span id="pkMood">{{ $latestMood ?? '—' }}</span></strong><br><small>😊 LATEST MOOD</small></div>
        </div>
    </div>

    <section class="task-card">
        <h3>🌱 Grow Your Butterfly</h3>
        <p><small>Complete these actions to help your butterfly grow!</small></p>
        <hr>
        
          <div class="task-card">

              @php $today = now()->toDateString(); @endphp

                {{-- Mood --}}
                <div class="task-item">
                    <div class="check-circle" id="checkMood"></div>
                    <div>
                        <strong>Log your mood today</strong>
                        <small>+1 growth point</small>
                    </div>
                </div>

                {{-- Positive Mood --}}
                <div class="task-item">
                    <div class="check-circle" id="checkPositive"></div>
                    <div>
                        <strong>Log a Joyful or Happy mood</strong>
                        <small>+1 bonus point</small>
                    </div>
                </div>

                {{-- Body Booster --}}
                <div class="task-item">
                    <div class="check-circle" id="checkBodyBooster"></div>
                    <div>
                        <strong>Complete Body Booster</strong>
                        <small>+0.5 growth points 💪</small>
                    </div>
                </div>

                {{-- Mini Tasks --}}
                <div class="task-item">
                    <div class="check-circle" id="checkMiniTask"></div>
                    <div>
                        <strong>Complete Mini Tasks</strong>
                        <small>+0.5 growth points 🌱</small>
                    </div>
                </div>

                {{-- Mind Reset --}}
                <div class="task-item">
                    <div class="check-circle" id="checkMindReset"></div>
                    <div>
                        <strong>Complete Mind Reset</strong>
                        <small>+0.5 growth points 🧠</small>
                    </div>
                </div>

                {{-- Mood Lifting --}}
                <div class="task-item">
                    <div class="check-circle" id="checkMoodLifting"></div>
                    <div>
                        <strong>Complete Mood Lifting</strong>
                        <small>+0.5 growth points ☁️</small>
                    </div>
                </div>

                {{-- Grounding --}}
                <div class="task-item">
                    <div class="check-circle" id="checkGrounding"></div>
                    <div>
                        <strong>Complete Grounding Exercise</strong>
                        <small>+0.5 growth points 🌿</small>
                    </div>
                </div>

          </div>
    </section>

    <section class="task-card" style="margin-top: 25px;">
        <h3>🏆 Your Streak Badges</h3>
        <div id="streakBadges" style="display: flex; gap: 10px; margin-top: 20px;"></div>
    </section>

    <section class="task-card" style="margin-top: 25px;">
        <h3>📊 Mood Over Time</h3>
        <div style="height: 300px;">
            <canvas id="moodChart"></canvas>
        </div>
    </section>

    <section class="task-card" style="margin-top: 25px;">
        <h3>📋 Mood History</h3>
        <div id="growthTimeline"></div>
    </section>
</main>
@endsection