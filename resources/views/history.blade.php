@extends('layouts.app')

@section('title', 'Chat History')
@section('body-class', 'history-page')

@push('styles')
<style>
    /* History page styles - Visible buttons layout */
    .history-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #d9d9d9;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 10px;
    }
    
    .history-row-main {
        flex: 1;
        cursor: pointer;
    }
    
    .history-row-main:hover {
        opacity: 0.8;
    }
    
    .history-row-buttons {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
        margin-left: 15px;
    }
    
    .rename-chat-btn {
        background: #4c7a60;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .rename-chat-btn:hover {
        background: #3e644f;
    }
    
    .delete-chat-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .delete-chat-btn:hover {
        background: #c82333;
    }
    
    .history-title {
        font-weight: 700;
        margin-bottom: 6px;
    }
    
    .history-preview {
        opacity: 0.85;
        font-size: 13px;
    }
    
    .history-date {
        margin-top: 8px;
        font-size: 11px;
        opacity: 0.6;
    }
    
    /* Keep existing filter styles */
    .history-search {
        position: relative;
        margin-bottom: 12px;
    }
    
    .history-search input {
        width: 100%;
        border: none;
        outline: none;
        background: rgba(220,220,220,0.75);
        border-radius: 16px;
        padding: 14px 16px 14px 44px;
        font-size: 14px;
    }
    
    .history-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.6;
    }
    
    .history-filters {
        display: flex;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .pill-dd {
        position: relative;
        display: inline-block;
    }
    
    .pill-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        min-width: 220px;
        padding: 10px;
        border-radius: 14px;
        background: rgba(255,255,255,0.92);
        box-shadow: 0 18px 40px rgba(0,0,0,0.16);
        display: none;
        z-index: 50;
        backdrop-filter: blur(8px);
    }
    
    .pill-dd.is-open .pill-menu {
        display: block;
    }
    
    .pill-item {
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        font: inherit;
        color: #2c4d3b;
    }
    
    .pill-item:hover {
        background: rgba(0,0,0,0.06);
    }
    
    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: rgba(220,220,220,0.75);
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .filter-pill.is-active {
        background: #4c7a60;
        color: white;
    }
    
    .pill-caret {
        margin-left: 6px;
        font-size: 10px;
    }
    
    .history-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-top: 20px;
    }
    
    .page-btn, .num-btn {
        padding: 8px 16px;
        background: rgba(70, 115, 92, 0.75);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    
    .num-active {
        background: #4c7a60;
    }
    
    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .history-empty {
        text-align: center;
        padding: 40px;
        opacity: 0.7;
    }
</style>
@endpush

@section('content')
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
                <button class="filter-pill" type="button">
                    <span class="pill-label" id="timeLabel">This week</span>
                    <span class="pill-caret">▾</span>
                </button>
                <div class="pill-menu">
                    <button type="button" class="pill-item" data-value="this_week">This week</button>
                    @foreach(['january','february','march','april','may','june','july','august','september','october','november','december'] as $month)
                        <button type="button" class="pill-item" data-value="{{ $month }}">{{ ucfirst($month) }}</button>
                    @endforeach
                </div>
            </div>
            <div class="pill-dd" data-dd="mood">
                <button class="filter-pill" type="button">
                    <span class="pill-label" id="moodLabel">Mood: Any</span>
                    <span class="pill-caret">▾</span>
                </button>
                <div class="pill-menu">
                    @foreach(['any'=>'Any mood','anxious'=>'Anxious','happy'=>'Happy','sad'=>'Sad','stressed'=>'Stressed','tired'=>'Tired','calm'=>'Calm'] as $val => $label)
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