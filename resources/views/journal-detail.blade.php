@extends('layouts.app')

@section('title', 'Journal Detail')
@section('body-class', 'journal-page-body')

@section('content')
  <div class="book-wrapper">
    <div class="book-tabs">
      <a href="javascript:void(0)" class="book-tab tab-inactive" id="journalTabLink" title="Journal">📖</a>
      <a href="javascript:void(0)" class="book-tab tab-history tab-inactive" id="historyTabLink" title="History">HISTORY</a>
    </div>

    <div class="book-open" id="bookOpen">
      <div class="book-page book-page-left">
        <div class="book-ruled-lines"></div>
        <div class="book-left-content">
          <span class="about-pill">Journal Entry</span>
          <h2 class="book-left-title">Moments & Thoughts ✨</h2>
          <p class="book-left-tagline"><em>Captured on:</em></p>
          <div style="font-size: 1.1rem; color: #4c7a60; font-weight: 800; margin-bottom: 20px;">
            {{ $journal->created_at->format('d F Y H:i') }}
          </div>
          <p class="book-left-desc">
            This is a permanent record of how you felt and what you were thinking. 
            Reflecting on past entries helps you see how much you've grown.
          </p>
          <div class="book-butterfly-img">
            <img src="{{ asset('inJar.png') }}" alt="Butterfly" onerror="this.style.display='none'" />
          </div>
        </div>
      </div>

      <div class="book-spine">
        <span></span><span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span>
        <span></span><span></span>
      </div>

      <div class="book-page book-page-right" id="rightPage">
        <div class="book-ruled-lines"></div>
        <div class="book-right-content">
          <div class="book-right-title">📖 The Entry</div>

          <div class="entry-full-text">
            {{ $journal->content }}
          </div>

          @if($journal->image)
            <img src="{{ asset('storage/' . $journal->image) }}" 
                style="margin-top:20px; max-width:100%; border-radius:10px;">
          @else
            <p style="opacity:0.6; margin-top:10px;">No image attached</p>
          @endif
          </div>

          <img src="{{ asset('Land.png') }}" class="corner-butterfly-img" onerror="this.style.display='none'" />
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const rightPage = document.getElementById("rightPage");
    const historyTab = document.getElementById("historyTabLink");
    const journalTab = document.getElementById("journalTabLink");

    function flipAndNavigate(url) {
      rightPage.style.transform = "rotateY(-5deg) scale(1.01)";
      setTimeout(() => {
        rightPage.classList.add('flip-backward');
        rightPage.style.transform = "";
      }, 30);
      setTimeout(() => { window.location.href = url; }, 650);
    }

    if (historyTab) historyTab.addEventListener('click', () => flipAndNavigate("{{ url('/journal-history') }}"));
    if (journalTab) journalTab.addEventListener('click', () => flipAndNavigate("{{ url('/journal') }}"));
  });
</script>
@endpush