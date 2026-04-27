@extends('layouts.app')

@section('title', 'Gentle Mind Reset')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/booster.css') }}">
@endpush

@section('content')
<div class="page-content mindreset-page">

    <a href="{{ url('/moodbooster') }}" class="btn-back">
        ← Back to Mood Boosters
    </a>

    <div class="mr-header">
        <h1>Gentle Mind Reset</h1>
        <p>Even small resets count</p>
    </div>

    <div id="progressText">
        You’ve completed 0 / 7 tasks 💚
    </div>

    <div class="mr-layout">

        <div class="mr-list">
            @php
            $items = [
                'Look at something green',
                "Find one thing you're grateful for",
                'Think of one small thing you did well today',
                'Notice one sound around you',
                'Feel your heartbeat for 10 seconds',
                'Smile softly (even if it’s silly)',
                'Take one deep breath and sigh it out'
            ];
            @endphp

            @foreach($items as $index => $item)
            <div class="mr-item" data-index="{{ $index }}">
                <span class="mr-item-icon">🌿</span>
                <span class="mr-item-label">{{ $item }}</span>
            </div>
            @endforeach
        </div>

        <div class="mr-side">
            <div class="mr-reminder-card">
                <img src="{{ asset('images/mindreset.jpeg') }}">
                <p>Slow down<br>Calm your thoughts</p>
            </div>
        </div>

    </div>

    <div style="text-align:center; margin-top:20px;">
        <button id="resetBtn" class="btn btn-green">
            Reset All Items
        </button>
    </div>

</div>

<!-- 🌿 Focus Overlay -->
<div id="focusOverlay" class="focus-overlay hidden">
  <div class="focus-card">
    <p class="focus-guide">Take a moment with this 🌿</p>
    <h2 id="focusText">Task here</h2>

    <div style="display:flex; gap:10px; justify-content:center;">
      <button id="focusDone" class="btn btn-green">Done</button>
      <button id="focusNext" class="btn btn-outline">Skip →</button>
    </div>
  </div>
</div>

<div class="mood-toast" id="moodToast">
  <div class="mood-toast-img-wrap">
    <img id="toastPetImg" src="{{ asset('images/egg.png') }}" />
  </div>
  <div class="mood-toast-body">
    <p id="toastTitle">🌱 Your butterfly is growing!</p>
    <p id="toastMsg"></p>
    <p id="toastTip"></p>
  </div>
  <button id="toastClose">✕</button>
</div>
@endsection

@push('scripts')

<!-- ✅ LOAD booster.js ONLY ONCE -->
<script src="{{ asset('js/aire-data.js') }}"></script>
<script src="{{ asset('js/booster.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", async () => {

  const items = document.querySelectorAll(".mr-item");
  const progressText = document.getElementById("progressText");
  const resetBtn = document.getElementById("resetBtn");

  const overlay = document.getElementById("focusOverlay");
  const focusText = document.getElementById("focusText");
  const focusDone = document.getElementById("focusDone");
  const focusNext = document.getElementById("focusNext");

  let completed = [];
  let currentIndex = null;

  /* LOAD BACKEND */
  const res = await fetch('/booster/progress/mindreset');
  const data = await res.json();

  const today = new Date().toISOString().split("T")[0];

  if (!data.date || data.date !== today) {
    completed = [];
  } else {
    completed = data.completed || [];
  }

  function updateUI() {
    items.forEach((el, i) => {
      el.classList.toggle("done", completed.includes(i));
    });

    progressText.textContent =
      `You’ve completed ${completed.length} / ${items.length} tasks 💚`;
  }

  updateUI();

  /* CLICK ITEM */
  items.forEach((el, index) => {
    el.addEventListener("click", () => {

      currentIndex = index;

      focusText.textContent =
        el.querySelector(".mr-item-label").textContent;

      overlay.classList.remove("hidden");

      setTimeout(() => {
        overlay.classList.add("show");
      }, 10);
    });
  });

  /* DONE BUTTON */
  focusDone.addEventListener("click", async () => {

    if (currentIndex === null) return;

    await fetch('/booster/toggle', {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
      },
      body: JSON.stringify({
        type: "mindreset",
        index: currentIndex
      })
    });

    if (!completed.includes(currentIndex)) {
      completed.push(currentIndex);
    }

    updateUI();

    const todayStr = new Date().toISOString().split("T")[0];

    if (completed.length === items.length) {
      localStorage.setItem(`mindreset-achieved-${todayStr}`, "true");
    }

    if (completed.length === items.length) {

      if (!localStorage.getItem(`mindreset-toast-${todayStr}`)) {

        localStorage.setItem(`mindreset-achieved-${todayStr}`, "true");
        localStorage.setItem(`mindreset-toast-${todayStr}`, "true");

        showGlobalToast({
          title: "🌿 Mind reset complete!",
          message: "You completed your reset 💚",
          tip: "Take a breath… you're doing okay 🌸"
        });

      }

    }

    // 🎉 NOW WORKS
    showEncouragement("mindreset");
    showSparkles();

    closeFocus();
  });

  /* NEXT */
  focusNext.addEventListener("click", () => {

    if (currentIndex === null) return;

    let next = currentIndex + 1;
    if (next >= items.length) next = 0;

    currentIndex = next;

    focusText.textContent =
      items[next].querySelector(".mr-item-label").textContent;
  });

  function closeFocus() {
    overlay.classList.remove("show");
    setTimeout(() => overlay.classList.add("hidden"), 300);
  }

  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) closeFocus();
  });

  /* RESET */
  resetBtn.addEventListener("click", async () => {

    await fetch('/booster/reset/mindreset', {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
      }
    });

    completed = [];
    updateUI();
  });

});
</script>

@endpush