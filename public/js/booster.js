// ── booster.js — shared interactivity for all Mood Booster pages ──

document.addEventListener('DOMContentLoaded', () => {

  // ── Mood selection (mood-booster page) ──
  document.querySelectorAll('.mood-btnmb').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.mood-btnmb').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
    });
  });

  // ── Task Start buttons (mini-tasks & body-booster) ──
  document.querySelectorAll('.task-start-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.task-card') || btn.closest('.bb-card');
      const duration = parseInt(btn.getAttribute('data-duration')) || 10;

      if (btn.classList.contains("breathing-task")) {
        startBreathingExercise(btn, card);
      } else {
        startTaskTimer(btn, card, duration);
      }
    });
  });

  loadMoodLiftingProgress();
  updateProgress();
  renderMoodBoosterStreak();

  // 🌿 Guided Mind Reset

  document.querySelectorAll(".mr-item").forEach(item => {
    item.addEventListener("click", () => {
      openFocusMode(item);
    });  
  });

  const focusOverlay = document.getElementById("focusOverlay");

  if (focusOverlay) {
    focusOverlay.addEventListener("click", (e) => {
      if (e.target === focusOverlay) {
        closeFocusMode();
      }
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeFocusMode();
    }
  });

});

/* ──────────────────────────────────────────────
   Start a countdown timer on a task button.
   Uses a CSS transition on scaleX for the bar
   (more reliable than @keyframes + duration override).
────────────────────────────────────────────── */
function startTaskTimer(btn, card, duration) {
  btn.disabled  = true;
  btn.className = 'btn btn-timer';

  // Build button: full-width fill bar + countdown label on top
  btn.innerHTML =
    '<span class="timer-bar"></span>' +
    '<span class="timer-text">' + duration + 's</span>';

  var bar    = btn.querySelector('.timer-bar');
  var textEl = btn.querySelector('.timer-text');

  // Set the transition duration, then on the next two frames
  // kick off the shrink — double-rAF ensures the initial paint
  // happens before the transition fires.
  bar.style.transition = 'transform ' + duration + 's linear';

  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      bar.style.transform = 'scaleX(0)';
    });
  });

  // Countdown text (updates every second)
  var remaining = duration;
  var interval  = setInterval(function () {
    remaining--;
    if (remaining > 0) {
      textEl.textContent = remaining + 's';
    } else {
      clearInterval(interval);
      markTaskDone(btn, card);
    }
  }, 1000);
}

function getEncouragementMessage(page) {
  const messages = {
    mindreset: [
      "You’re grounding yourself 🌿",
      "Good job noticing ✨",
      "Stay present, you're doing well 💚"
    ],
    minitask: [
      "Nice job 💚",
      "Small steps matter 🌱",
      "Keep going ✨"
    ],
    bodybooster: [
      "Great movement 💪",
      "Your body thanks you 🌿",
      "That was refreshing 💚"
    ],
    moodlifting: [
      "That’s something to be proud of 💚",
      "That matters more than you think ✨",
      "You're doing better than you realise 🌿",
      "That’s a meaningful reflection 💭"
    ]
  };

  const list = messages[page] || messages.minitask;
  return list[Math.floor(Math.random() * list.length)];
}


function showEncouragement(page = "minitask") {
  const msg = getEncouragementMessage(page);

  const popup = document.createElement("div");
  popup.className = "encouragement-popup";
  popup.textContent = msg;

  document.body.appendChild(popup);

  setTimeout(() => popup.classList.add("show"), 10);

  setTimeout(() => {
    popup.classList.remove("show");
    setTimeout(() => popup.remove(), 300);
  }, 2000);
}

/* Mark a task as completed after the timer finishes */
function markTaskDone(btn, card) {
  
  showEncouragement(getCurrentPage());
  showSparkles();

  btn.innerHTML = 'Completed ✓';
  btn.className = 'btn btn-completed';
  btn.disabled  = true;

  if (!card) return;

  card.classList.add('completed'); // ✅ FIRST

  updateProgress();

  // ✅ SAVE PROGRESS
  saveProgress(card);
  checkDailyCompletion();

  var circle = card.querySelector('.bb-check-circle');
  if (circle) {
    circle.classList.add('done');
    circle.textContent = '✓';
  }

  var leaf = card.querySelector('.task-leaf');
  if (leaf) leaf.textContent = '✓';
}

function saveProgress(card) {
  let storageKey;
  let items;

  // 🔥 Detect which page you're on
  if (card.classList.contains("bb-card")) {
    storageKey = "bodybooster-progress";
    items = document.querySelectorAll(".bb-card");
  } else {
    storageKey = "minitasks-progress";
    items = document.querySelectorAll(".task-card");
  }

  const completed = [];

  items.forEach((item, index) => {
    if (item.classList.contains("completed")) {
      completed.push(index);
    }
  });

  localStorage.setItem(storageKey, JSON.stringify(completed));
}

/* Update Mood button handler (called from onclick in HTML) */
function updateMood() {

  var selected = document.querySelector('.mood-btnmb.selected');
  var btn = document.querySelector('.update-mood-btnmb');

  if (!selected) {
    btn.textContent = 'Pick a mood first!';
    setTimeout(() => btn.textContent = 'Update Mood', 1500);
    return;
  }

  const mood = selected.dataset.mood;

  // SAVE MOOD TO BUTTERFLY SYSTEM
  AireData.logMood(mood);

  if (typeof renderHero === "function" && typeof buildSnapshots === "function") {
    const log = AireData.getMoodLog();
    const snapshots = buildSnapshots(log);
    renderHero(snapshots);
  }

  if (typeof renderActions === "function") {
    renderActions();
  }

  if (typeof renderMoodChart === "function") {
    renderMoodChart(AireData.getMoodLog());
  }

  if (typeof renderTimeline === "function") {
    const log = AireData.getMoodLog();
    const snapshots = buildSnapshots(log);
    renderTimeline(snapshots, log);
  }
  
  btn.textContent = 'Mood Updated ✓';
  renderMoodBoosterStreak();
  btn.disabled = true;
  btn.style.background = '#3a6b35';

  setTimeout(() => {
    btn.textContent = 'Update Mood';
    btn.disabled = false;
    btn.style.background = '';
  }, 2500);
}

function startBreathingExercise(btn, card) {
  const duration = parseInt(btn.getAttribute('data-duration')) || 10;
  startTaskTimer(btn, card, duration);
}

function showSparkles() {
  for (let i = 0; i < 25; i++) { // more sparkles
    const sparkle = document.createElement("div");
    sparkle.className = "sparkle";

    // 🌟 RANDOM POSITION ACROSS SCREEN
    sparkle.style.left = Math.random() * window.innerWidth + "px";
    sparkle.style.top  = Math.random() * window.innerHeight + "px";

    document.body.appendChild(sparkle);

    setTimeout(() => sparkle.remove(), 1200);
  }
}

function updateProgress() {
  let tasks = [];
  let completed = 0;

  // 🔥 Detect current page
  if (document.body.classList.contains("moodlifting-page")) {
    tasks = document.querySelectorAll(".thought-card");
    completed = document.querySelectorAll(".thought-card.crossed-off").length;
  } 
  else {
    tasks = document.querySelectorAll(".task-card, .bb-card, .mr-item");
    completed = document.querySelectorAll(
      ".task-card.completed, .bb-card.completed, .mr-item.done"
    ).length;
  }

  const progressText = document.getElementById("progressText");

  if (progressText) {
    progressText.textContent = `You’ve completed ${completed} / ${tasks.length} tasks 💚`;
  }
}

function checkDailyCompletion() {

  let tasks = [];
  let completed = [];
  let key = "daily-completed-date";

  // 🎯 Detect page type
  if (document.body.classList.contains("moodlifting-page")) {
    tasks = document.querySelectorAll(".thought-card");
    completed = document.querySelectorAll(".thought-card.crossed-off");
    key = "moodlifting-achieved";
  } 
  else if (document.body.classList.contains("mindreset-page")) {
    tasks = document.querySelectorAll(".mr-item");
    completed = document.querySelectorAll(".mr-item.done");
    key = "mindreset-achieved";
  } 
  else if (document.body.classList.contains("booster-page")) {
    tasks = document.querySelectorAll(".bb-card");
    completed = document.querySelectorAll(".bb-card.completed");
    key = "bodybooster-achieved";
  } 
  else if (document.body.classList.contains("minitask-page")) {
    tasks = document.querySelectorAll(".task-card");
    completed = document.querySelectorAll(".task-card.completed");
    key = "minitask-achieved";
  }

  // ✅ Check if all tasks done
  if (tasks.length > 0 && completed.length === tasks.length) {

  const todayStr = new Date().toISOString().split("T")[0]

  // ⭐ Achievement key (per page)
  const achievementKey = key;

  // 🎯 Popup key
  const popupKey = key + "-popup";

  const lastAchieved = localStorage.getItem(achievementKey);
  const lastPopup = localStorage.getItem(popupKey);

  // ⭐ Handle achievement + XP
  if (lastAchieved !== todayStr) {

    let xpAdded = false;

    if (window.AireData && typeof AireData.addXP === "function") {

      if (
        achievementKey === "bodybooster-achieved" ||
        achievementKey === "minitask-achieved" ||
        achievementKey === "mindreset-achieved" ||
        achievementKey === "moodlifting-achieved"
      ) {
        AireData.addXP(0.5);
        xpAdded = true;
      }
    }

    // 🔥 UPDATE UI ONLY ONCE
    if (xpAdded) {
      if (typeof renderHero === "function" && typeof buildSnapshots === "function") {
        const log = AireData.getMoodLog();
        const snapshots = buildSnapshots(log);
        renderHero(snapshots);
      }
    }

    localStorage.setItem(achievementKey, todayStr);
    if (typeof renderActions === "function") {
    renderActions();
    }
  }

  // 🎉 Handle popup (separate system)
  if (lastPopup !== todayStr) {
    showDailyCompletionPopup();
    localStorage.setItem(popupKey, todayStr);
  }
}
}

function showDailyCompletionPopup() {

  const page = getCurrentPage();

  let message = "🌿 You completed your session 💚";

  if (page === "bodybooster") {
    message = "🌿 You’ve completed your body booster 💪";
  } 
  else if (page === "minitask") {
    message = "🌿 You’ve completed your mini tasks 💚";
  }
  else if (page === "moodlifting") {
    message = "🌿 You’ve completed your mood lifting session 💚";
  }
  else if (page === "mindreset") {
    message = "🌿 You’ve completed your gentle reset 💚";
  }

  const popup = document.createElement("div");
  popup.className = "daily-popup centered-popup";
  popup.innerHTML = `
    <div class="daily-popup-content">
      ${message}
    </div>
  `;

  document.body.appendChild(popup);

  // ✨ Sparkles
  showSparkles();

  setTimeout(() => {
    showTaskGrowthToast();
  }, 400);

  setTimeout(() => popup.classList.add("show"), 10);

  setTimeout(() => {
    popup.classList.remove("show");
    setTimeout(() => popup.remove(), 300);
  }, 3000);
}

function openFocusMode(item) {
  const overlay = document.getElementById("focusOverlay");
  const text = item.querySelector(".cross-off-text").textContent;

  const items = Array.from(document.querySelectorAll(".mr-item"));
  let currentIndex = items.indexOf(item);

  document.getElementById("focusText").textContent = text;

  overlay.classList.remove("hidden");

  setTimeout(() => {
    overlay.classList.add("show");
  }, 10);

  // highlight
  items.forEach(i => i.classList.remove("active"));
  item.classList.add("active");

  // 🔹 DONE BUTTON
  document.getElementById("focusDoneBtn").onclick = () => {
    completeTask(item);
    closeFocusMode(); // exit overlay
  };

  // 🔹 NEXT STEP BUTTON
  document.getElementById("focusNextBtn").onclick = () => {
    completeTask(item);

    const nextItem = items[currentIndex + 1];

    if (nextItem) {
      openFocusMode(nextItem); // go next
    } else {
      closeFocusMode(); // no more tasks
    }
  };
}

function closeFocusMode() {
  const overlay = document.getElementById("focusOverlay");

  overlay.classList.remove("show");

  setTimeout(() => {
    overlay.classList.add("hidden");
  }, 300);
}

function getCurrentPage() {
  if (document.body.classList.contains("mindreset-page")) return "mindreset";
  if (document.body.classList.contains("booster-page")) return "bodybooster";
  if (document.body.classList.contains("minitask-page")) return "minitask";
  if (document.body.classList.contains("moodlifting-page")) return "moodlifting";
  return "minitask";
}

function saveMindResetProgress() {
  const items = document.querySelectorAll(".mr-item");
  const completed = [];

  items.forEach((item, index) => {
    if (item.classList.contains("done")) {
      completed.push(index);
    }
  });

  localStorage.setItem("mindreset-progress", JSON.stringify(completed));
}

/* Mind Reset */
function completeTask(item) {
  if (!item.classList.contains("done")) {
    item.classList.add("done");

    showEncouragement("mindreset");
    showSparkles();
    updateProgress();
    saveMindResetProgress();
    checkDailyCompletion();
  }
}

/* Check if all Mind Reset tasks are completed, and if so, show a one-time popup. */
function checkMindResetCompletion() {
  const tasks = document.querySelectorAll(".mr-item");
  const completed = document.querySelectorAll(".mr-item.done");

  if (tasks.length > 0 && completed.length === tasks.length) {

    const today = new Date().toISOString().split("T")[0]
    const lastShown = localStorage.getItem("mindreset-complete-date");

    if (lastShown !== today) {
      showMindResetCompletion();
      localStorage.setItem("mindreset-complete-date", today);
    }
  }
}

/* Moodlifting */
function saveMoodLiftingProgress() {
  const items = document.querySelectorAll(".thought-card");
  const completed = [];

  items.forEach((item, index) => {
    if (item.classList.contains("crossed-off")) {
      completed.push(index);
    }
  });

  localStorage.setItem("moodlifting-progress", JSON.stringify(completed));
  checkDailyCompletion();
}

function loadMoodLiftingProgress() {
  const saved = localStorage.getItem("moodlifting-progress");
  if (!saved) return;

  const completed = JSON.parse(saved);
  const items = document.querySelectorAll(".thought-card");

  completed.forEach(index => {
    if (items[index]) {
      items[index].classList.add("crossed-off");
    }
  });
}

/* Popup for showing task growth progress (butterfly) */
function showTaskGrowthToast() {
  const stageKey = AireData.getStageKey();
  const streak = AireData.getStreak();

  const STAGE_LABELS = {
    egg: "Egg",
    pupa: "Pupa",
    caterpillar: "Caterpillar",
    butterfly: "Butterfly 🦋",
    surviving: "Surviving",
    struggling: "Struggling"
  };

  const TASK_TIPS = {
    egg: "Small actions like this help your butterfly grow 🌱",
    pupa: "You're building momentum — keep going 💚",
    caterpillar: "Your efforts are adding up 🐛",
    butterfly: "You're thriving — keep it up 🦋",
    surviving: "These actions support your recovery 💚",
    struggling: "Even small steps matter 🌱"
  };

  const toast = document.getElementById("moodToast");
  if (!toast) return;

  document.getElementById("toastTitle").textContent =
    "🌿 Your butterfly is growing!";

  document.getElementById("toastMsg").textContent =
    `Stage: ${STAGE_LABELS[stageKey]} · Streak: ${streak} day${streak !== 1 ? "s" : ""}`;

  document.getElementById("toastTip").textContent =
    TASK_TIPS[stageKey];

  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 5000);
}

function renderMoodBoosterStreak() {
  const streak = AireData.getStreak();

  const streakText = document.querySelector(".streak-text");
  if (streakText) {
    streakText.textContent = `${streak} Day Streak 🔥`;
  }
}