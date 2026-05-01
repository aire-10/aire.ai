/* ================================================================
   growth.js  —  Butterfly Pet page
   Correct 4-stage lifecycle:
     egg (0-9 XP) → caterpillar (10-29 XP) → pupa (30-49 XP) → butterfly (50+ XP)
   
   Decline from butterfly:
     1 negative  → surviving
     2+ negatives → struggling
   
   Recovery (positive moods while in decline):
     struggling → surviving (after 2 positives)
     surviving  → butterfly  (after 3 positives)
   ================================================================ */
let moodChartInstance = null;

const STAGE_CONFIG = {
  egg:         { img: "egg.png",         label: "Egg 🥚",        color: "#8a7060", xpMax: 10  },
  caterpillar: { img: "caterpillar.png", label: "Caterpillar 🐛", color: "#7aab72", xpMax: 30  },
  pupa:        { img: "pupa.png",        label: "Pupa 🦋",        color: "#4c7a60", xpMax: 50  },
  butterfly:   { img: "adult_glow.png",  label: "Butterfly 🦋",   color: "#3a8c3a", xpMax: 100 },
  surviving:   { img: "surviving.jpeg",  label: "Surviving 🌱",    color: "#b0a060", xpMax: 3   },
  struggling:  { img: "struggling.jpeg", label: "Struggling 💔",   color: "#a07070", xpMax: 2   },
};

const NEXT_STAGE_LABEL = {
  egg:         "Caterpillar",
  caterpillar: "Pupa",
  pupa:        "Butterfly",
  butterfly:   null,
  surviving:   "Butterfly",
  struggling:  "Surviving",
};

const STREAK_MILESTONES = [
  { days: 3,   icon: "🌱",  color: "#8aaa88" },
  { days: 7,   icon: "🌿",  color: "#6a9a68" },
  { days: 14,  icon: "🌸",  color: "#c97e5a" },
  { days: 30,  icon: "🦋",  color: "#4c7a60" },
  { days: 100, icon: "💜🦋", color: "#9b59b6" },
];

function formatDayLabel(dayKey) {
  const [y, m, d] = dayKey.split("-").map(Number);
  return new Date(y, m - 1, d).toLocaleDateString(undefined, {
    weekday: "short", month: "short", day: "numeric", year: "numeric"
  });
}

/* ── Helper: Get total points from mood log ── */
function getTotalMoodPoints(log) {
  let points = 0;
  log.forEach(entry => {
    if (AireData.POSITIVE.includes(entry.mood)) {
      points += 1;
    }
  });
  return points;
}

/* ── Get current stage info ── */
function getCurrentStage(snapshots) {
  if (!snapshots.length) {
    return { stageKey: "egg", streak: 0, cumulativePoints: 0, recoveryStreak: 0, hasReachedButterfly: false };
  }
  return snapshots[0];
}

/* ── Hero section ─────────────────────────────────────── */
async function renderHero() {
  const streak = await AireData.getStreak();
  const points = await AireData.getTotalPoints();
  const stageKey = await AireData.getStageKey();
  const stats = await AireData.getStats();

  const daysTracked = stats.daysTracked;
  const todayCount = stats.todayCheckIns;
  const latestMood = stats.latestMood;
  const moodMeta = AireData.MOOD_META();

  const cfg = STAGE_CONFIG[stageKey] || STAGE_CONFIG.egg;

  /* Butterfly image + label */
  const imgEl = document.getElementById("petImg");
  const labelEl = document.getElementById("petStageLabel");
  if (imgEl) {
    imgEl.src = `/images/${cfg.img}`;
    imgEl.alt = cfg.label;
  }
  if (labelEl) {
    labelEl.textContent = cfg.label;
    labelEl.style.color = cfg.color;
  }

  /* XP bar */
  const xpFill = document.getElementById("petXpFill");
  const xpText = document.getElementById("petXpText");
  const xpHint = document.getElementById("petXpHint");

  const xpVal = points;
  const xpMax = cfg.xpMax;

  const pct = Math.round((xpVal / xpMax) * 100);

  if (xpFill) {
    xpFill.style.width = pct + "%";
    xpFill.style.background = cfg.color;
  }

  if (xpText) xpText.textContent = `${xpVal} / ${xpMax} pts`;
  if (xpHint) xpHint.textContent = "Keep going 💚";

  /* KPI row */
  const moodLabel = latestMood
    ? ((moodMeta[latestMood]?.emoji || "") + " " + (moodMeta[latestMood]?.label || latestMood))
    : "—";
  
  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };

  set("pkStreak", streak);
  set("pkDays", daysTracked);
  set("pkToday", todayCount);
  set("pkMood", moodLabel);
}

/* ── Grow actions ─────────────────────────────────────── */
/* Update this section inside the renderActions() function in growth.js */

async function renderActions() {

  const todayISO = new Date().toISOString().split("T")[0];

  const log = await AireData.getMoodLog();

  const todayLogs = log.filter(e => {
    const d = e.date || e.created_at?.split("T")[0];
    return d === todayISO;
  });

  const hasToday = todayLogs.length >= 1;
  const hasPositive = todayLogs.some(e =>
    AireData.POSITIVE.includes(e.mood?.toLowerCase())
  );

  // ✅ FETCH BACKEND COMPLETION STATUS
  const stats = await fetch('/growth/data', {
    credentials: "include"
  }).then(r => r.json());
  const completed = stats.completedActions;

  const mark = (id, done) => {
    const el = document.getElementById(id);
    if (!el) return;

    el.textContent = done ? "✓" : "";
    el.style.background = done ? "#3a6b35" : "rgba(0,0,0,0.08)";
    el.style.borderColor = done ? "#3a6b35" : "rgba(0,0,0,0.15)";
    el.style.color = done ? "#fff" : "transparent";
  };

  // ✅ MOOD
  mark("checkMood", hasToday);
  mark("checkPositive", hasPositive);

  // ✅ SELF-CARE (ALL BACKEND)
  mark("checkGrounding", completed.grounding);
  mark("checkBodyBooster", completed.bodybooster);
  mark("checkMiniTask", completed.minitask);
  mark("checkMindReset", completed.mindreset);
  mark("checkMoodLifting", completed.moodlifting);
}


/* ── Streak badges ────────────────────────────────────── */
async function renderStreakBadges() {
  const el = document.getElementById("streakBadges");
  if (!el) return;

  const streak = await AireData.getStreak();

  el.innerHTML = STREAK_MILESTONES.map(m => {
    const earned = streak >= m.days;
    return `
      <div class="streak-badge ${earned ? "earned" : ""}">
        <div class="streak-badge-icon" style="${earned ? `color:${m.color}` : ""}">
          ${earned ? m.icon : "🔥"}
        </div>
        <div class="streak-badge-line"></div>
        <div class="streak-badge-days">${m.days}d</div>
      </div>`;
  }).join("");
}

/* ── Get growth data ────────────────────────────────────── */
async function fetchGrowthData() {
  const res = await fetch('/growth/data', {
    credentials: "include"
  });
  const data = await res.json();
  return data;
}

/* =========================
   📊 MOOD CHART
========================= */

async function renderMoodChart() {

  const ctx = document.getElementById("moodChart");
  if (!ctx) return;

  const moodLog = await AireData.getMoodLog();

  if (!moodLog || moodLog.length === 0) {
    ctx.parentElement.innerHTML = `
      <p style="text-align:center;color:#777;">
        No mood data yet — log your first mood on the home page
      </p>`;
    return;
  }

  // ✅ GROUP BY DAY (IMPORTANT FIX)
  const grouped = {};

  moodLog.forEach(e => {
    if (!grouped[(e.date || e.created_at?.split("T")[0])]) grouped[(e.date || e.created_at?.split("T")[0])] = [];
    grouped[(e.date || e.created_at?.split("T")[0])].push(e);
  });

  const dates = Object.keys(grouped).sort().slice(-14);

  // ✅ ONE BAR PER DAY (latest mood of the day)
  const labels = [];
  const data = [];

  dates.forEach(date => {
    const entries = grouped[date];

    // take latest entry of the day
    const latest = entries[entries.length - 1];

    labels.push(date);

    const moodLevelMap = {
      joyful: 5,
      happy: 4,
      neutral: 3,
      anxious: 2,
      sad: 1
    };

    data.push(
      moodLevelMap[latest.mood?.toLowerCase()] ?? 3
    );
  });

  // ✅ DESTROY OLD CHART
  if (moodChartInstance) {
    moodChartInstance.destroy();
  }

  moodChartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [{
        label: "Mood",
        data: data
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          min: 0,
          max: 5,
          offset: true, // ✅ THIS IS IMPORTANT (moves ticks between bars)

          ticks: {
            stepSize: 1,

            padding: 10, // ✅ pushes emoji slightly UP

            callback: function(value) {
              const map = {
                1: "😔",
                2: "😰",
                3: "😐",
                4: "😊",
                5: "😄"
              };

              return map[value] || "";
            }
          },

          grid: {
            drawBorder: false
          }
        }
      }
    }
  });
}

/* ── Timeline ─────────────────────────────────────────── */
async function renderTimeline(log) {
  const el = document.getElementById("growthTimeline");
  if (!el) return;

  if (!log.length) {
    el.innerHTML = `<p style="text-align:center;color:rgba(0,0,0,0.4);padding:30px 0;">
      No check-ins yet
    </p>`;
    return;
  }

  const moodMeta = AireData.MOOD_META();

  // ✅ GROUP BY DAY
  const grouped = {};

  log.forEach(entry => {
    const date = entry.date || entry.created_at?.split("T")[0];

    if (!grouped[date]) grouped[date] = [];
    grouped[date].push(entry);
  });

  // ✅ SORT LATEST FIRST
  const days = Object.keys(grouped).sort().reverse();

  // ✅ FETCH GLOBAL STATS ONCE
  const stats = await AireData.getStats();
  const stageKey = stats.stage;
  const stage = STAGE_CONFIG[stageKey] || STAGE_CONFIG.egg;

  el.innerHTML = days.map(date => {

    const entries = grouped[date];

    // ✅ TAKE LATEST ENTRY OF THAT DAY
    const latest = entries[entries.length - 1];

    const mood = latest.mood?.toLowerCase();
    const meta = moodMeta[mood] || {};

    // ✅ COUNT CHECK-INS
    const checkIns = entries.length;

    // ✅ CALCULATE DAILY POINTS (same logic as backend)
    let points = 1; // logged mood
    if (entries.some(e => AireData.POSITIVE.includes(e.mood))) {
      points += 1;
    }

    // ✅ GET NOTE
    const note = latest.note || "";

    return `
      <article class="day-card">
        <div class="day-left">

          <div class="day-date">${date}</div>

          <div class="day-meta">
            <span class="badge stage">
              🥚 ${stage.label}
            </span>

            <span class="badge mood">
              ${meta.emoji || "🙂"} ${meta.label || mood}
            </span>

            <span class="badge checkins">
              ${checkIns} check-in${checkIns > 1 ? "s" : ""}
            </span>

            <span class="badge points">
              ⭐ ${points} pts
            </span>
          </div>

          ${note ? `<p class="day-note">"${note}"</p>` : ""}

        </div>
      </article>
    `;

  }).join("");
}

/* ── Init ─────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", async () => {
  const log = await AireData.getMoodLog();

  await renderHero();
  await renderActions();
  await renderStreakBadges();
  await renderMoodChart();
  await renderTimeline(log);
});

document.addEventListener("visibilitychange", async () => {
  if (!document.hidden) {
    const log = await AireData.getMoodLog();
    await renderHero();
    await renderActions();
    await renderStreakBadges();
    await renderMoodChart();
    await renderTimeline(log);
  }
});

window.addEventListener("aire:mood-logged", async () => {
  const log = await fetch('/api/mood-log', {
    credentials: "include"
  }).then(res => res.json());

  await renderHero();
  await renderActions();
  await renderStreakBadges();
  await renderMoodChart();
  await renderTimeline(log);
});

window.addEventListener("focus", async () => {
  const log = await AireData.getMoodLog();

  await renderHero();
  await renderActions();
  await renderStreakBadges();
  await renderMoodChart();
  await renderTimeline(log);
});