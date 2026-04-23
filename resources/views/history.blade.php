@extends('layouts.app')

@section('title', 'Chat History')
@section('body-class', 'history-page')

@section('content')
<script>
    if (!localStorage.getItem("authToken")) window.location.href = "{{ route('login') }}";
</script>

<main class="history-wrap">
    <header class="history-hero">
        <h1>Chat History</h1>
        <span id="sessionCount" class="session-count"></span>
    </header>

    <section class="history-panel">
        <div class="history-search">
            <span class="history-search-icon">⌕</span>
            <input id="historySearch" type="text" placeholder="Search Keywords (e.g. anxious, exam, sleep)" />
        </div>

        <div class="history-filters">
            <button class="filter-pill is-active" type="button" data-filter="all">All</button>

            <div class="pill-dd" data-dd="time">
                <button class="filter-pill" type="button" aria-haspopup="true" aria-expanded="false">
                    <span class="pill-label" id="timeLabel">This week</span>
                    <span class="pill-caret">▾</span>
                </button>
                <div class="pill-menu" role="menu">
                    <button type="button" class="pill-item" data-value="this_week">This week</button>
                    @foreach(['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'] as $month)
                        <button type="button" class="pill-item" data-value="{{ $month }}">{{ ucfirst($month) }}</button>
                    @endforeach
                </div>
            </div>

            <div class="pill-dd" data-dd="mood">
                <button class="filter-pill" type="button" aria-haspopup="true" aria-expanded="false">
                    <span class="pill-label" id="moodLabel">Mood: Anxious</span>
                    <span class="pill-caret">▾</span>
                </button>
                <div class="pill-menu" role="menu">
                    @foreach(['any' => 'Any mood', 'anxious' => 'Anxious', 'happy' => 'Happy', 'sad' => 'Sad', 'stressed' => 'Stressed', 'tired' => 'Tired', 'calm' => 'Calm'] as $val => $label)
                        <button type="button" class="pill-item" data-value="{{ $val }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <div id="historyList" class="history-list"></div>

        <div class="history-pagination">
            <button id="prevPage" class="page-btn" type="button">Previous</button>
            <div id="pageNumbers" class="page-numbers"></div>
            <button id="nextPage" class="page-btn" type="button">Next &gt;</button>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/history.js') }}" defer></script>
@endpush