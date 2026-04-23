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

/* ── Core: build per-day snapshots with streak-based stage transitions ── */
function buildSnapshots(log) {
  if (!log.length) return [];

  /* Latest check-in per day */
  const byDay = {};
  log.forEach(e => {
    if (!byDay[e.date] || e.ts > byDay[e.date].ts) byDay[e.date] = e;
  });
  const countByDay = {};
  log.forEach(e => { countByDay[e.date] = (countByDay[e.date] || 0) + 1; });

  const days = Object.keys(byDay).sort();

  let cumulativePoints = 0;
  let streak = 0;
  let stageKey = "egg";
  let hasReachedButterfly = false;
  let consecutiveNeg = 0;
  let recoveryStreak = 0;

  const snapshots = days.map(day => {
    const mood = byDay[day].mood;
    const isPos = AireData.POSITIVE.includes(mood);

    // ✅ FIX: update streak properly
    if (isPos) {
      streak += 1;
    } else {
      streak = 0;
    }

    // existing points logic...
    cumulativePoints += 1;
    if (isPos) cumulativePoints += 1;

    if (localStorage.getItem("grounding-achieved") === day) cumulativePoints += 0.5;
    if (localStorage.getItem("moodlifting-achieved") === day) cumulativePoints += 0.5;
    if (localStorage.getItem("mindreset-achieved") === day) cumulativePoints += 0.5;
    if (localStorage.getItem("minitask-achieved") === day) cumulativePoints += 0.5;
    if (localStorage.getItem("bodybooster-achieved") === day) cumulativePoints += 0.5;


    /* ── Stage transitions ── */
    if (hasReachedButterfly) {
      /* Already reached butterfly — handle decline / recovery */
      if (!isPos) {
        /* Declining based on negative moods */
        if (consecutiveNeg >= 2) {
          stageKey = "struggling";
        } else {
          stageKey = "surviving";
        }
      } else {
        /* Recovering — only advance if currently in a decline state */
        if (stageKey === "struggling" && recoveryStreak >= 2) {
          stageKey = "surviving";
        } else if (stageKey === "surviving" && recoveryStreak >= 3) {
          stageKey = "butterfly";
          recoveryStreak = 0;
        }
        /* If already butterfly, stay butterfly */
      }
    } else {
      /* Normal growth path based on cumulative points */
      if (cumulativePoints < 10) {
        stageKey = "egg";
      } else if (cumulativePoints < 30) {
        stageKey = "caterpillar";
      } else if (cumulativePoints < 50) {
        stageKey = "pupa";
      } else {
        stageKey = "butterfly";
      }

      if (stageKey === "butterfly") {
        hasReachedButterfly = true;
        recoveryStreak = 0;
        consecutiveNeg = 0;
      }
    }

    return {
      dayKey: day,
      mood,
      note: byDay[day].note || "",
      stageKey,
      streak,
      cumulativePoints,
      recoveryStreak,
      hasReachedButterfly,
      consecutiveNeg,
      checkIns: countByDay[day] || 1,
    };
  });

  return snapshots.reverse(); /* most-recent first */
}

/* ── Get current stage info ── */
function getCurrentStage(snapshots) {
  if (!snapshots.length) {
    return { stageKey: "egg", streak: 0, cumulativePoints: 0, recoveryStreak: 0, hasReachedButterfly: false };
  }
  return snapshots[0];
}

/* ── Hero section ─────────────────────────────────────── */
function renderHero(snapshots) {
  const current = getCurrentStage(snapshots);
  const streak = AireData.getStreak();
  const daysTracked = AireData.getDaysTracked();
  const todayCount = AireData.getTodayCheckInCount();
  const latestMood = AireData.getLatestMood();
  const moodMeta = AireData.MOOD_META;

  const cfg = STAGE_CONFIG[current.stageKey] || STAGE_CONFIG.egg;

  /* Butterfly image + label */
  const imgEl = document.getElementById("petImg");
  const labelEl = document.getElementById("petStageLabel");
  if (imgEl) {
    imgEl.src = cfg.img;
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

  let xpVal, xpMax, hintText;
  const isDecline = current.hasReachedButterfly && current.stageKey !== "butterfly";

  if (current.stageKey === "butterfly") {
    xpVal = cfg.xpMax;
    xpMax = cfg.xpMax;
    hintText = "Your butterfly is fully grown! Keep your streak alive 🦋";
  } else if (isDecline) {
    /* Recovery XP — consecutive positives since last negative */
    xpVal = current.recoveryStreak;
    xpMax = cfg.xpMax;
    const next = NEXT_STAGE_LABEL[current.stageKey];
    const rem = xpMax - xpVal;
    hintText = `${rem} more positive mood${rem !== 1 ? "s" : ""} to reach ${next}`;
  } else {
    /* Normal growth progress */
    xpVal = Math.min(current.cumulativePoints, cfg.xpMax);
    xpMax = cfg.xpMax;
    const next = NEXT_STAGE_LABEL[current.stageKey];
    if (next) {
      const rem = Math.max(xpMax - current.cumulativePoints, 0);
      hintText = `${rem} more positive mood${rem !== 1 ? "s" : ""} to reach ${next}`;
    } else {
      hintText = "You've reached the final stage! 🦋";
    }
  }

  const pct = Math.round((xpVal / xpMax) * 100);
  if (xpFill) {
    xpFill.style.width = pct + "%";
    xpFill.style.background = cfg.color;
  }
  if (xpText) xpText.textContent = `${xpVal} / ${xpMax} pts`;
  if (xpHint) xpHint.textContent = hintText;

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
function renderActions() {
  const todayISO = new Date().toISOString().split("T")[0];
  const log = AireData.getMoodLog();
  const todayLogs = log.filter(e => e.date === todayISO);
  const snapshots = buildSnapshots(log);
  const current = getCurrentStage(snapshots);
  const streak = current.streak;

  const hasToday = todayLogs.length > 0;
  const hasPositive = todayLogs.some(e => AireData.POSITIVE.includes(e.mood));

  const bodyBoosterDone = localStorage.getItem("bodybooster-achieved") === todayISO;
  const miniTaskDone = localStorage.getItem("minitask-achieved") === todayISO;
  const mindResetDone = localStorage.getItem("mindreset-achieved") === todayISO;
  const moodLiftingDone = localStorage.getItem("moodlifting-achieved") === todayISO;
  const groundingDone = localStorage.getItem("grounding-achieved") === todayISO;

  const mark = (checkId, done) => {
    const el = document.getElementById(checkId);
    if (!el) return;
    el.textContent = done ? "✓" : "";
    el.style.background = done ? "#3a6b35" : "rgba(0,0,0,0.08)";
    el.style.borderColor = done ? "#3a6b35" : "rgba(0,0,0,0.15)";
    el.style.color = done ? "#fff" : "transparent";
  };

  mark("checkMood", hasToday);
  mark("checkPositive", hasPositive);
  mark("checkBodyBooster", bodyBoosterDone);
  mark("checkMiniTask", miniTaskDone);
  mark("checkMindReset", mindResetDone);
  mark("checkMoodLifting", moodLiftingDone);
  mark("checkGrounding", groundingDone);
}

/* ── Streak badges ────────────────────────────────────── */
function renderStreakBadges() {
  const el = document.getElementById("streakBadges");
  if (!el) return;
  const snapshots = buildSnapshots(AireData.getMoodLog());
  const current = getCurrentStage(snapshots);
  const streak = current.streak;

  el.innerHTML = STREAK_MILESTONES.map(m => {
    const earned = streak >= m.days;
    return `
      <div class="streak-badge ${earned ? "earned" : ""}">
        <div class="streak-badge-icon" style="${earned ? `color:${m.color}` : ""}">${earned ? m.icon : "🔥"}</div>
        <div class="streak-badge-line"></div>
        <div class="streak-badge-days">${m.days}d</div>
      </div>`;
  }).join("");
}

/* ── Mood chart ───────────────────────────────────────── */
function renderMoodChart(log) {
  const canvas = document.getElementById("moodChart");
  if (!canvas) return;

  const MOOD_SCORE = { joyful: 2, happy: 2, content: 1, neutral: 0, tired: -1, anxious: -1, sad: -2 };
  const MOOD_COLOR = { joyful: "#3a8c3a", happy: "#5aab5a", content: "#7aab72", neutral: "#b0a060", tired: "#c47a5a", anxious: "#c47a5a", sad: "#a07070" };

  const byDay = {};
  log.forEach(e => {
    if (!byDay[e.date] || e.ts > byDay[e.date].ts) byDay[e.date] = e;
  });

  const days = [];
  for (let i = 13; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    days.push(d.toISOString().split("T")[0]);
  }

  const labels = days.map(d => {
    const [y, m, day] = d.split("-").map(Number);
    return new Date(y, m - 1, day).toLocaleDateString(undefined, { month: "short", day: "numeric" });
  });

  if (!log.length) {
    canvas.parentElement.innerHTML = `<p style="text-align:center;color:rgba(0,0,0,0.4);padding:30px 0;">No mood data yet — log your first mood on the home page!</p>`;
    return;
  }

  new Chart(canvas, {
    type: "bar",
    data: {
      labels,
      datasets: [{
        data: days.map(d => byDay[d] ? (MOOD_SCORE[byDay[d].mood] ?? 0) : null),
        backgroundColor: days.map(d => byDay[d] ? (MOOD_COLOR[byDay[d].mood] || "#b0a060") : "rgba(0,0,0,0.06)"),
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const d = days[ctx.dataIndex];
              if (!byDay[d]) return "No check-in";
              const meta = AireData.MOOD_META[byDay[d].mood] || {};
              return `${meta.emoji || ""} ${meta.label || byDay[d].mood}`;
            }
          }
        }
      },
      scales: {
        y: {
          min: -3, max: 3,
          ticks: {
            stepSize: 1,
            callback: (v) => ({ 2: "😄", 1: "🙂", 0: "😐", "-1": "😔", "-2": "😢" }[v] || ""),
            font: { size: 14 }
          },
          grid: { color: "rgba(0,0,0,0.06)" }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 }, color: "rgba(0,0,0,0.5)" }
        }
      }
    }
  });
}

/* ── Timeline ─────────────────────────────────────────── */
function renderTimeline(snapshots, log) {
  const el = document.getElementById("growthTimeline");
  if (!el) return;
  
  if (!log.length) {
    el.innerHTML = `<p style="text-align:center;color:rgba(0,0,0,0.4);padding:30px 0;">No check-ins yet — <a href="home.html" style="color:#3a6b35;font-weight:700;">log your first mood</a>!</p>`;
    return;
  }
  
  el.innerHTML = snapshots.map(s => {
    const cfg = STAGE_CONFIG[s.stageKey] || STAGE_CONFIG.egg;
    const moodMeta = AireData.MOOD_META[s.mood] || {};
    const isDecline = s.hasReachedButterfly && s.stageKey !== "butterfly";
    
    let streakInfo = `🔥 ${s.streak} day streak`;
    if (isDecline && s.stageKey === "struggling") {
      streakInfo = `💔 Struggling - ${s.recoveryStreak}/2 positives to recover`;
    } else if (isDecline && s.stageKey === "surviving") {
      streakInfo = `🌱 Surviving - ${s.recoveryStreak}/3 positives to become butterfly`;
    }
    
    return `
      <article class="day-card">
        <div class="day-left">
          <div class="day-date">${formatDayLabel(s.dayKey)}</div>
          <div class="day-meta">
            <span class="badge stage" style="background:${cfg.color}20; color:${cfg.color}">
              <img src="${cfg.img}" alt="${cfg.label}" style="width:18px;height:18px;object-fit:contain;vertical-align:middle;border-radius:3px;"> ${cfg.label}
            </span>
            <span class="badge mood">${moodMeta.emoji || "🙂"} ${moodMeta.label || s.mood}</span>
            <span class="badge count">${s.checkIns} check-in${s.checkIns === 1 ? "" : "s"}</span>
          </div>
          ${s.note ? `<div class="day-note" style="margin-top:8px;font-size:0.85rem;color:rgba(0,0,0,0.6);">"${s.note.substring(0, 100)}${s.note.length > 100 ? '...' : ''}"</div>` : ""}
        </div>
        <div class="day-right">
          <div class="day-streak">${streakInfo}</div>
          <div class="day-points" style="font-size:0.75rem;color:rgba(0,0,0,0.4);margin-top:4px;">${s.cumulativePoints} total points</div>
        </div>
      </article>`;
  }).join("");
}

/* ── Init ─────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
  const log = AireData.getMoodLog();
  const snapshots = buildSnapshots(log);

  renderHero(snapshots);
  renderActions();
  renderStreakBadges();
  renderMoodChart(log);
  renderTimeline(snapshots, log);
});

document.addEventListener("visibilitychange", () => {
  if (!document.hidden) {
    const log = AireData.getMoodLog();
    const snapshots = buildSnapshots(log);
    renderHero(snapshots);
    renderActions();
    renderStreakBadges();
    renderMoodChart(log);
    renderTimeline(snapshots, log);
  }
});