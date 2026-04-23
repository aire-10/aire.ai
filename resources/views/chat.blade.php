@extends('layouts.app')

@section('title', 'Chat')
@section('body-class', 'chat-page')

@push('head-scripts')
  <script>if (!localStorage.getItem("authToken")) window.location.href = "{{ url('/login') }}";</script>
  <script src="{{ asset('chat.js') }}" defer></script>
@endpush

@section('content')
  <main class="chat-wrap">
    <section class="chat-stage">
      <aside class="chat-left">
        <div class="chat-card">
          <h3>AI Bot: Airé</h3>
          <p class="chat-disclaimer"><b>Disclaimer:</b> Airé is not a licensed professional...</p>
        </div>
        <div class="chat-card">
          <h3>History Chats</h3>
          <a class="btn btn-filled" href="{{ url('/history') }}">View All Chats</a>
          <button class="btn btn-outline" id="newChatBtn" style="margin-top:10px;">New Chat</button>
          <button id="clearChatBtn" class="btn btn-outline" style="margin-top:10px;">Clear Chat</button>
        </div>
      </aside>

      <section class="chat-center">
        <div class="mood-strip">
          <div class="mood-title">How are you feeling today?</div>
          <div class="mood-row">
            @foreach(['happy'=>'😄', 'good'=>'😊', 'neutral'=>'😐', 'sad'=>'😢', 'tired'=>'😔'] as $val => $emoji)
              <button class="mood-btn" data-mood="{{ $val }}">{{ $emoji }}</button>
            @endforeach
          </div>
        </div>
        <div class="chat-messages" id="chatMessages"></div>
        <form class="chat-inputbar">
          <input id="chatInput" type="text" placeholder="Type a message..." />
          <button type="submit" class="send-btn">Send</button>
        </form>
      </section>

      <aside class="chat-right">
        <div class="chat-card chat-suggest">
          <h3>Suggestions</h3>
          @foreach(['I\'m feeling anxious'=>'😟', 'Coping with stress'=>'😮‍💨', 'Tiring day'=>'😪'] as $text => $emoji)
            <button class="suggest-item" type="button" data-fill="{{ $text }}">
              <span>{{ $emoji }}</span>
              <span class="suggest-text">{{ $text }}</span>
              <span class="chev">›</span>
            </button>
          @endforeach
          <hr class="suggest-divider">
          <div class="hotline-section">
            <h4>Need immediate help?</h4>
            <div class="hotline-item"><span>☎️ Talian Harapan</span> <a href="tel:145">145</a></div>
          </div>
        </div>
      </aside>
    </section>
  </main>
@endsection