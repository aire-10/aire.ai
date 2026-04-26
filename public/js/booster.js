/* =========================================================
   UNIVERSAL BOOSTER SYSTEM (FIXED FOR BODYBOOSTER)
   ========================================================= */

document.addEventListener('DOMContentLoaded', async () => {

  const page = getCurrentPage();

  if (page === "moodlifting") {
    await initBooster("moodlifting", ".thought-card", "completed");
  }

  if (page === "bodybooster") {
    await initBooster("bodybooster", ".bb-card", "completed");
  }

  if (page === "minitask") {
    await initBooster("minitask", ".task-card", "completed");
  }

  if (page === "mindreset") return;
});


/* =========================================================
   INIT BOOSTER
   ========================================================= */
async function initBooster(type, selector, doneClass) {

  await loadProgress(type, selector, doneClass);

  document.querySelectorAll(selector).forEach((card, index) => {

    const btn = card.querySelector(".task-start-btn");

    if (btn) {
      btn.addEventListener("click", (e) => {
        e.stopPropagation();

        const duration = getTaskDuration(card, btn);
        startTaskTimer(card, duration, type, selector, doneClass);
      });
    }

    else {
      card.addEventListener("click", () => {
        toggleTask(type, index, selector, doneClass);
      });
    }

  });

  /* RESET */
  document.querySelector(".reset-all-btn")?.addEventListener("click", async () => {

    await fetch(`/booster/reset/${type}`, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
      }
    });

    document.querySelectorAll(selector).forEach(card => {

      if (type === "moodlifting") {

        // ✅ CLEAR EVERYTHING
        card.classList.remove("completed");
        card.classList.remove("reflected");

        const tick = card.querySelector(".ml-tick");
        if (tick) tick.remove();
      }

    });

    updateProgress(selector, doneClass);
  });

  updateProgress(selector, doneClass);
}


/* =========================================================
   LOAD PROGRESS
   ========================================================= */
async function loadProgress(type, selector, doneClass) {

  const res = await fetch(`/booster/progress/${type}`);
  const data = await res.json();

  const cards = document.querySelectorAll(selector);

  cards.forEach((card, index) => {

    /* =========================================
       MOOD LIFTING (DO NOT TOUCH - KEEP)
    ========================================= */
    if (type === "moodlifting") {

      if (data.completed.includes(index)) {

        card.classList.add("completed");
        card.classList.add("reflected");

        if (!card.querySelector(".ml-tick")) {
          const tick = document.createElement("span");
          tick.className = "ml-tick";
          tick.textContent = "✓";
          card.appendChild(tick);
        }

      } else {

        card.classList.remove("completed");
        card.classList.remove("reflected");

        const tick = card.querySelector(".ml-tick");
        if (tick) tick.remove();
      }
    }

    /* =========================================
       BODYBOOSTER + MINITASK (THIS IS THE FIX)
    ========================================= */
    else {

      if (data.completed.includes(index)) {

        // ✅ restore completed state
        card.classList.add(doneClass);

        // OPTIONAL: restore progress bar visually
        const fill = card.querySelector(".task-bar-fill");
        if (fill) fill.style.width = "100%";

        const btn = card.querySelector(".task-start-btn");
        if (btn) {
          btn.textContent = "Done ✓";
          btn.disabled = true;
        }

      } else {

        card.classList.remove(doneClass);

        const fill = card.querySelector(".task-bar-fill");
        if (fill) fill.style.width = "0%";

        const btn = card.querySelector(".task-start-btn");
        if (btn) {
          btn.textContent = "Start";
          btn.disabled = false;
        }
      }
    }

  });

  updateProgress(selector, doneClass);
}


/* =========================================================
   TIMER (FIXED)
   ========================================================= */
function startTaskTimer(card, duration, type, selector, doneClass) {

  const btn = card.querySelector(".task-start-btn");
  if (!btn) return;

  let fill = card.querySelector(".task-bar-fill");

  // ✅ FIX: CREATE IF NOT EXISTS
  if (!fill) {
    const bar = card.querySelector(".task-bar");
    if (!bar) return;

    fill = document.createElement("div");
    fill.className = "task-bar-fill";
    fill.style.height = "100%";
    fill.style.width = "0%";
    fill.style.background = "#2e6b3f";
    fill.style.borderRadius = "10px";

    bar.appendChild(fill);
  }

  let time = duration;
  btn.disabled = true;

  const interval = setInterval(() => {

    time--;

    const progress = ((duration - time) / duration) * 100;
    fill.style.width = progress + "%";

    btn.textContent = `${time}s`;

    if (time <= 0) {
      clearInterval(interval);

      btn.textContent = "Done ✓";
      card.classList.add(doneClass);

      const index = [...document.querySelectorAll(selector)].indexOf(card);
      toggleTask(type, index, selector, doneClass);
    }

  }, 1000);
}


/* =========================================================
   GET DURATION (FIXED)
   ========================================================= */
function getTaskDuration(card, btn) {

  // PRIORITY 1 → data-duration (BEST)
  const attr = btn.getAttribute("data-duration");
  if (attr) return parseInt(attr);

  // fallback text parsing
  const text = card.textContent.toLowerCase();
  const match = text.match(/\d+/);

  return match ? parseInt(match[0]) : 15;
}


/* =========================================================
   TOGGLE TASK
   ========================================================= */
async function toggleTask(type, index, selector, doneClass) {

  const res = await fetch(`/booster/toggle`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ type, index })
  });

  const data = await res.json();

  const cards = document.querySelectorAll(selector);

  cards.forEach((card, i) => {

    if (type === "moodlifting") {

      if (data.completed.includes(i)) {

        // ✅ IMPORTANT: ADD THIS
        card.classList.add("completed");

        card.classList.add("reflected");

        if (!card.querySelector(".ml-tick")) {
          const tick = document.createElement("span");
          tick.className = "ml-tick";
          tick.textContent = "✓";
          card.appendChild(tick);
        }

      } else {

        // ✅ IMPORTANT: REMOVE THIS
        card.classList.remove("completed");

        card.classList.remove("reflected");

        const tick = card.querySelector(".ml-tick");
        if (tick) tick.remove();
      }

    }

  });

  showEncouragement(type);
  showSparkles();

  updateProgress(selector, doneClass);

  await checkGrowth(type);
}

/* =========================================================
   PROGRESS TEXT
   ========================================================= */
function updateProgress(selector, doneClass) {
  const cards = document.querySelectorAll(selector);
  const done = document.querySelectorAll(`${selector}.${doneClass}`).length;

  const text = document.getElementById("progressText");
  if (text) {
    text.textContent = `You’ve completed ${done} / ${cards.length} tasks 💚`;
  }
}


/* =========================================================
   PAGE DETECTION
   ========================================================= */
function getCurrentPage() {
  if (document.body.classList.contains("moodlifting-page")) return "moodlifting";
  if (document.body.classList.contains("booster-page")) return "bodybooster";
  if (document.body.classList.contains("minitask-page")) return "minitask";
  if (document.body.classList.contains("mindreset-page")) return "mindreset";
  return "";
}


/* =========================================================
   EFFECTS
   ========================================================= */
function showEncouragement(type) {

  const messages = {
    bodybooster: ["Nice movement! Keep going 💪"],
    minitask: ["Small wins matter ✨"],
    moodlifting: ["That matters 💚"],
    default: ["Your butterfly is growing ✨"]
  };

  const msg = (messages[type] || messages.default)[0];

  const popup = document.createElement("div");
  popup.className = "xp-popup";
  popup.innerHTML = `<div class="xp-popup-inner">${msg}</div>`;

  document.body.appendChild(popup);

  setTimeout(() => popup.classList.add("show"), 10);
  setTimeout(() => popup.remove(), 2000);
}


function showSparkles() {
  for (let i = 0; i < 10; i++) {
    const s = document.createElement("div");
    s.className = "sparkle";
    s.style.left = Math.random() * window.innerWidth + "px";
    s.style.top = Math.random() * window.innerHeight + "px";
    document.body.appendChild(s);
    setTimeout(() => s.remove(), 1000);
  }
}


/* =========================================================
   GROWTH CHECK
   ========================================================= */
async function checkGrowth(type) {

  const res = await fetch(`/booster/check/${type}`);
  const data = await res.json();

  if (data.completed) {
    showSparkles();
  }
}