// ===== BREATHING SETTINGS =====
const phases = [
  { name: 'INHALE', duration: 4, css: 'phase-inhale' },
  { name: 'HOLD', duration: 7, css: 'phase-hold' },
  { name: 'EXHALE', duration: 8, css: 'phase-exhale' }
];

let TOTAL_CYCLES = 4;
let currentCycle = 1;
let phaseIndex = 0;
let timeLeft = phases[0].duration;
let timer = null;
let running = false;


// ===== TAB SWITCH =====
function switchTab(tab) {

  const breathingSection = document.getElementById('breathing-section');
  const moodSection = document.getElementById('mood-section');

  const btnBreathing = document.getElementById('btn-breathing');
  const btnMood = document.getElementById('btn-mood');

  if (tab === 'breathing') {
    breathingSection.classList.remove('hidden');
    moodSection.classList.add('hidden');

    btnBreathing.classList.add('active');
    btnMood.classList.remove('active');
  }

  if (tab === 'mood') {
    breathingSection.classList.add('hidden');
    moodSection.classList.remove('hidden');

    btnBreathing.classList.remove('active');
    btnMood.classList.add('active');
  }
}


// ===== UPDATE DISPLAY =====
function updateDisplay() {
  const phase = phases[phaseIndex];

  document.getElementById('timer').textContent = timeLeft;
  document.getElementById('phase').textContent = phase.name;
  document.getElementById('cycle').textContent = currentCycle;

  const circle = document.getElementById('breath-circle');
  circle.className = 'circle ' + phase.css;
}


// ===== START =====
function startBreathing() {
  if (running) return;

  running = true;

  timer = setInterval(() => {

    timeLeft--;

    if (timeLeft <= 0) {
      phaseIndex++;

      if (phaseIndex >= phases.length) {
        phaseIndex = 0;
        currentCycle++;

        if (currentCycle > TOTAL_CYCLES) {
          finishBreathing();
          return;
        }
      }

      timeLeft = phases[phaseIndex].duration;
    }

    updateDisplay();

  }, 1000);
}


// ===== FINISH =====
function finishBreathing() {
  clearInterval(timer);
  running = false;

  document.getElementById('timer').textContent = "✓";
  document.getElementById('phase').textContent = "DONE!";

  recordCompletedCycle();          // ✅ FIXED
  renderProgressDashboard();       // ✅ FIXED
  saveBreathingSession();
}


// ===== RESTART =====
function restartBreathing() {
  clearInterval(timer);

  running = false;
  phaseIndex = 0;
  currentCycle = 1;
  timeLeft = phases[0].duration;

  updateDisplay();
}


// ===== SAVE TO DB =====
async function saveBreathingSession() {
  try {

    const totalDuration = TOTAL_CYCLES * (4 + 7 + 8);

    const response = await fetch('/breathing', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        duration: totalDuration
      })
    });

    if (!response.ok) {
      const text = await response.text();
      console.error("❌ Server error:", text);
      return;
    }

    const data = await response.json();
    console.log("✅ Saved to DB:", data);

  } catch (err) {
    console.error("❌ Save error:", err);
  }
}


// ===== CYCLE SELECTOR =====
function initCycleSelector() {

  const dots = document.querySelectorAll('.cycle-dot');
  const label = document.getElementById('cycleChosenVal');

  dots.forEach(dot => {

    dot.addEventListener('click', () => {

      if (running) return;

      dots.forEach(d => d.classList.remove('active'));
      dot.classList.add('active');

      TOTAL_CYCLES = parseInt(dot.dataset.val);
      label.textContent = TOTAL_CYCLES;

    });

  });
}


// ===== PROGRESS STORAGE =====
const CYCLE_KEY = 'aire_breathing_cycles';

function recordCompletedCycle() {
  const data = JSON.parse(localStorage.getItem(CYCLE_KEY) || '{}');
  const today = new Date().toISOString().split('T')[0];

  data[today] = (data[today] || 0) + 1;
  localStorage.setItem(CYCLE_KEY, JSON.stringify(data));
}


// ===== PROGRESS DASHBOARD =====
function renderProgressDashboard() {

  const barsEl = document.getElementById('progressBars');
  const labelEl = document.getElementById('progressLabel');

  if (!barsEl || !labelEl) return;

  const now = new Date();
  const day = now.getDay() || 7;

  const monday = new Date(now);
  monday.setDate(now.getDate() - (day - 1));

  const data = JSON.parse(localStorage.getItem(CYCLE_KEY) || '{}');

  let total = 0;
  let html = '';

  for (let i = 0; i < 7; i++) {
    const d = new Date(monday);
    d.setDate(monday.getDate() + i);

    const key = d.toISOString().split('T')[0];
    const count = data[key] || 0;

    total += count;

    html += `<div class="prog-bar-col">
        <div class="prog-bar">${count}</div>
        <span>${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'][i]}</span>
      </div>`;
  }

  barsEl.innerHTML = html;
  labelEl.innerHTML = `This week's Breathings:<br><strong>${total} Cycles Completed</strong>`;
}


// ===== SIDEBAR TOGGLE =====
function toggleSidebar(section) {
  const bodyId  = section === 'progress' ? 'progressBody' : 'howBody';
  const arrowId = section === 'progress' ? 'progressArrow' : 'howArrow';

  const body = document.getElementById(bodyId);
  const arrow = document.getElementById(arrowId);

  if (!body) return;

  const hidden = body.classList.toggle('hidden');
  arrow.textContent = hidden ? '▼' : '▲';
}


/* ================================
   MOOD TRACKING
================================ */
let selectedMoodEmoji = null;

function selectMood(element, emoji) {
  document.querySelectorAll('.emoji-item').forEach(el => el.classList.remove('selected'));
  element.classList.add('selected');
  selectedMoodEmoji = emoji;
}

async function saveMood() {

  if (!selectedMoodEmoji) {
    alert("Please select a mood first!");
    return;
  }

  const moodMap = {
    "😄": "joyful",
    "😊": "happy",
    "😐": "neutral",
    "😰": "anxious",
    "😔": "sad"
  };

  const moodType = moodMap[selectedMoodEmoji];
  const note = document.getElementById("mood-note").value.trim();

  await AireData.logMood(moodType, note); // ✅ USE API

  loadMoodEntries();

  document.querySelectorAll(".emoji-item").forEach(el => el.classList.remove("selected"));
  document.getElementById("mood-note").value = "";
  selectedMoodEmoji = null;
}

async function loadMoodEntries() {

  const list = document.getElementById("entries-list");
  if (!list) return;

  const moodLog = await AireData.getMoodLog();

  list.innerHTML = "";

  const emojiMap = {
    joyful: "😄",
    happy: "😊",
    neutral: "😐",
    anxious: "😰",
    sad: "😔"
  };

  moodLog.forEach(entry => {

    const item = document.createElement("div");
    item.className = "entry-item";

    item.innerHTML = `
      ${entry.date} ${emojiMap[entry.mood] || '🙂'}
      ${entry.note ? `<br><small>Notes: ${entry.note}</small>` : ''}
    `;

    list.appendChild(item);
  });
}

// ===== INIT =====
window.addEventListener('DOMContentLoaded', () => {
  updateDisplay();
  initCycleSelector();
  renderProgressDashboard();
  loadMoodEntries();
});