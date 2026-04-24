/* ═══════════════════════════════════════════════════
   BREATHING TIMER — state & constants
═══════════════════════════════════════════════════ */

/* Background colours per phase — matching the PNG designs */
const phaseBg = {
    'phase-inhale': '#9fac9f',   /* default sage — INHALE */
    'phase-hold':   '#7a9480',   /* darker sage — HOLD    */
    'phase-exhale': '#7a9480',   /* darker sage — EXHALE  */
    'phase-done':   '#9fac9f'    /* back to default       */
};

const phases = [
    { name: 'INHALE',  duration: 4, cssClass: 'phase-inhale'  },
    { name: 'HOLD',    duration: 7, cssClass: 'phase-hold'    },
    { name: 'EXHALE',  duration: 8, cssClass: 'phase-exhale'  }
];

let currentPhaseIdx = 0;
let currentTime     = phases[0].duration;
let currentCycle    = 1;
let TOTAL_CYCLES    = 4;   // default; user can change via selector (1–5)
let timerInterval   = null;
let running         = false;

/* ═══════════════════════════════════════════════════
   TAB SWITCHER
═══════════════════════════════════════════════════ */
function switchTab(tab) {
    const breathingSection = document.getElementById('breathing-section');
    const moodSection      = document.getElementById('mood-section');
    const btnBreathing     = document.getElementById('btn-breathing');
    const btnMood          = document.getElementById('btn-mood');
    const butterfly        = document.getElementById('corner-butterfly');

    if (tab === 'breathing') {
        breathingSection.classList.remove('hidden');
        moodSection.classList.add('hidden');
        btnBreathing.classList.add('active');
        btnMood.classList.remove('active');
        butterfly.classList.remove('visible');
        /* Restore breathing background based on current phase */
        const currentClass = phases[currentPhaseIdx].cssClass;
        document.body.style.backgroundColor = phaseBg[currentClass];
    } else {
        breathingSection.classList.add('hidden');
        moodSection.classList.remove('hidden');
        btnBreathing.classList.remove('active');
        btnMood.classList.add('active');
        butterfly.classList.add('visible');
        /* Mood tab always uses the default sage background */
        document.body.style.backgroundColor = '#9fac9f';
    }
}
/* ═══════════════════════════════════════════════════
   BREATHING — display update
═══════════════════════════════════════════════════ */
function updateDisplay() {
    const circle   = document.getElementById('breath-circle');
    const cssClass = phases[currentPhaseIdx].cssClass;

    document.getElementById('timer').textContent = currentTime;
    document.getElementById('phase').textContent = phases[currentPhaseIdx].name;
    document.getElementById('cycle').textContent = currentCycle;

    /* Swap phase colour class on the circle */
    circle.classList.remove('phase-inhale', 'phase-hold', 'phase-exhale', 'phase-done');
    circle.classList.add(cssClass);

    /* Change body background to match the phase */
    document.body.style.backgroundColor = phaseBg[cssClass];
}

/* ═══════════════════════════════════════════════════
   BREATHING — start
═══════════════════════════════════════════════════ */
function startBreathing() {
    if (running) return;
    running = true;
    updateDisplay();

    timerInterval = setInterval(() => {
        currentTime--;

        if (currentTime <= 0) {
            currentPhaseIdx++;

            if (currentPhaseIdx >= phases.length) {
                currentPhaseIdx = 0;
                currentCycle++;

                if (currentCycle > TOTAL_CYCLES) {
                    clearInterval(timerInterval);
                    running = false;

                    /* Done state */
                    const circle = document.getElementById('breath-circle');
                    circle.classList.remove('phase-inhale', 'phase-hold', 'phase-exhale');
                    circle.classList.add('phase-done');
                    document.getElementById('timer').textContent = '✓';
                    document.getElementById('phase').textContent = 'DONE!';
                    document.getElementById('cycle').textContent = TOTAL_CYCLES;
                    document.body.style.backgroundColor = phaseBg['phase-done'];
                    recordCompletedCycle();
                    renderProgressDashboard();
                    return;
                }
            }
            currentTime = phases[currentPhaseIdx].duration;
        }

        updateDisplay();
    }, 1000);
}

/* ═══════════════════════════════════════════════════
   BREATHING — restart
═══════════════════════════════════════════════════ */
function restartBreathing() {
    clearInterval(timerInterval);
    running         = false;
    currentPhaseIdx = 0;
    currentTime     = phases[0].duration;
    currentCycle    = 1;

    const circle = document.getElementById('breath-circle');
    circle.classList.remove('phase-inhale', 'phase-hold', 'phase-exhale', 'phase-done');

    /* Reset body background to INHALE colour */
    document.body.style.backgroundColor = phaseBg['phase-inhale'];

    updateDisplay();
}

/* ═══════════════════════════════════════════════════
   MOOD TRACKING
═══════════════════════════════════════════════════ */
let selectedMoodEmoji = null;

function selectMood(element, emoji) {
    document.querySelectorAll('.emoji-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    selectedMoodEmoji = emoji;
}

function saveMood() {

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

    /* SAVE MOOD */
    AireData.logMood(moodType, note);
    showToast(moodType);

    console.log("Mood saved:", AireData.getMoodLog());
    
    /* REFRESH UI */
    loadMoodEntries();

    /* Reset form */
    document.querySelectorAll(".emoji-item").forEach(el => el.classList.remove("selected"));
    document.getElementById("mood-note").value = "";
    selectedMoodEmoji = null;
}

/* ═══════════════════════════════════════════════════
   MOOD TRACKING — load past moods
═══════════════════════════════════════════════════ */

function loadMoodEntries() {

  const list = document.getElementById("entries-list");
  if (!list) return;

  const moodLog = AireData.getMoodLog();

  const emojiMap = {
    joyful: "😄",
    happy: "😊",
    neutral: "😐",
    anxious: "😰",
    sad: "😔",
    tired: "😩",
    content: "🙂"
  };

  list.innerHTML = "";

  const sorted = [...moodLog].sort((a,b)=>b.date.localeCompare(a.date));

  sorted.forEach(entry => {

    const date = new Date(entry.date + "T00:00:00");
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const dateStr = `${months[date.getMonth()]} ${date.getDate()}`;

    const item = document.createElement("div");
    item.className = "entry-item";

    item.innerHTML = `
      ${dateStr} ${emojiMap[entry.mood] || "🙂"}
      <strong>Notes:</strong> ${entry.note || "—"}
    `;

    list.appendChild(item);

  });

}

/* ═══════════════════════════════════════════════════
   INIT — set correct display on page load
═══════════════════════════════════════════════════ */
window.addEventListener("DOMContentLoaded", () => {
  updateDisplay();
  loadMoodEntries();
  renderProgressDashboard();
  initCycleSelector();
});

/* ═══════════════════════════════════════════════════
   SIDEBAR TOGGLE
═══════════════════════════════════════════════════ */
function toggleSidebar(section) {
  const bodyId  = section === 'progress' ? 'progressBody' : 'howBody';
  const arrowId = section === 'progress' ? 'progressArrow' : 'howArrow';
  const body    = document.getElementById(bodyId);
  const arrow   = document.getElementById(arrowId);
  if (!body) return;
  const isHidden = body.classList.toggle('hidden');
  arrow.textContent = isHidden ? '▼' : '▲';
}

/* ═══════════════════════════════════════════════════
   PROGRESS DASHBOARD
═══════════════════════════════════════════════════ */
const CYCLE_KEY   = 'aire_breathing_cycles';
const MAX_BARS    = 7;
const MAX_PER_BAR = 5;

function getWeeklyCycles() {
  try {
    const data   = JSON.parse(localStorage.getItem(CYCLE_KEY) || '{}');
    const now    = new Date();
    const day    = now.getDay() || 7;
    const monday = new Date(now);
    monday.setDate(now.getDate() - (day - 1));
    monday.setHours(0, 0, 0, 0);
    let total = 0;
    Object.entries(data).forEach(([d, count]) => {
      if (new Date(d) >= monday) total += count;
    });
    return total;
  } catch { return 0; }
}

function recordCompletedCycle() {
  try {
    const data  = JSON.parse(localStorage.getItem(CYCLE_KEY) || '{}');
    const today = new Date().toISOString().split('T')[0];
    data[today] = (data[today] || 0) + 1;
    localStorage.setItem(CYCLE_KEY, JSON.stringify(data));
  } catch {}
}

function renderProgressDashboard() {
  const barsEl  = document.getElementById('progressBars');
  const labelEl = document.getElementById('progressLabel');
  if (!barsEl || !labelEl) return;

  const DAY_LABELS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  // Build an array of the 7 dates for this week (Mon → Sun)
  const now    = new Date();
  const day    = now.getDay() || 7;           // 1=Mon … 7=Sun
  const monday = new Date(now);
  monday.setDate(now.getDate() - (day - 1));
  monday.setHours(0, 0, 0, 0);

  const weekDates = [];
  for (let i = 0; i < 7; i++) {
    const d = new Date(monday);
    d.setDate(monday.getDate() + i);
    weekDates.push(d.toISOString().split('T')[0]);
  }

  // Read stored cycles per date
  let stored = {};
  try { stored = JSON.parse(localStorage.getItem(CYCLE_KEY) || '{}'); } catch {}

  const todayStr = now.toISOString().split('T')[0];
  let totalCycles = 0;
  let html = '';

  weekDates.forEach((dateStr, i) => {
    const count   = stored[dateStr] || 0;
    const filled  = Math.min(count, MAX_PER_BAR);
    const isToday = dateStr === todayStr;
    totalCycles  += count;

    let icons = '';
    for (let j = 0; j < MAX_PER_BAR; j++) {
      icons += `<span class="bar-icon ${j < filled ? 'filled' : 'empty'}">🦋</span>`;
    }

    html += `
      <div class="prog-bar-col">
        <div class="prog-bar">${icons}</div>
        <span class="bar-day${isToday ? ' today' : ''}">${DAY_LABELS[i]}</span>
      </div>`;
  });

  barsEl.innerHTML = html;
  labelEl.innerHTML = `This week's Breathings:<br><strong>${totalCycles} Cycle${totalCycles !== 1 ? 's' : ''} Completed</strong>`;
}

/* ═══════════════════════════════════════════════════
   CYCLE SELECTOR (1–5)
═══════════════════════════════════════════════════ */
function initCycleSelector() {
  const dots   = document.querySelectorAll('.cycle-dot');
  const valEl  = document.getElementById('cycleChosenVal');

  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      if (running) return; // don't change mid-session
      dots.forEach(d => d.classList.remove('active'));
      dot.classList.add('active');
      TOTAL_CYCLES = parseInt(dot.dataset.val, 10);
      if (valEl) valEl.textContent = TOTAL_CYCLES;
    });
  });
}

/* ═══════════════════════════════════════════════════
   POPUP TOAST — shows after mood log, with butterfly stage, streak, and tip
═══════════════════════════════════════════════════ */

function showToast(mood) {
  const stageKey = AireData.getStageKey();
  const streak = AireData.getStreak();

  const STAGE_IMAGES = {
    egg: "egg.png",
    pupa: "pupa.png",
    caterpillar: "caterpillar.png",
    butterfly: "adult_glow.png",
    surviving: "surviving.jpeg",
    struggling: "struggling.jpeg"
  };

  const STAGE_LABELS = {
    egg: "Egg",
    pupa: "Pupa",
    caterpillar: "Caterpillar",
    butterfly: "Butterfly 🦋",
    surviving: "Surviving",
    struggling: "Struggling"
  };

  const TIPS = {
    egg: "Log a Joyful 😄 or Happy 😊 mood to hatch your egg!",
    pupa: "Keep logging positive moods for 1–2 days to grow 🌱",
    caterpillar: "You're getting stronger 🐛",
    butterfly: "You're glowing! 🦋",
    surviving: "You're getting through this 💚",
    struggling: "One step at a time 🌱",
  };

  const TITLES = {
    joyful: "🌟 Your butterfly is thriving!",
    happy: "🌿 Your butterfly is glowing!",
    neutral: "😐 Your butterfly is resting…",
    sad: "💙 Your butterfly feels your sadness.",
    anxious: "💙 Your butterfly is with you.",
  };

  const toast = document.getElementById("moodToast");
  if (!toast) return;

  document.getElementById("toastTitle").textContent =
    TITLES[mood] || "🌱 Your butterfly noticed your mood.";

  document.getElementById("toastMsg").textContent =
    `Stage: ${STAGE_LABELS[stageKey]} · Streak: ${streak} day${streak !== 1 ? "s" : ""}`;

  document.getElementById("toastTip").textContent =
    TIPS[stageKey];

  document.getElementById("toastPetImg").src =
    STAGE_IMAGES[stageKey];

  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 5000);
}

/* close button */
document.addEventListener("click", (e) => {
  if (e.target.id === "toastClose") {
    document.getElementById("moodToast")?.classList.remove("show");
  }
});