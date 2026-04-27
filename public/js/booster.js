/* =========================================================
   UNIVERSAL BOOSTER SYSTEM (FIXED FOR BODYBOOSTER)
   ========================================================= */

document.addEventListener('DOMContentLoaded', async () => {

  const page = getCurrentPage();

  if (page === "moodlifting") {
    await initBooster("moodlifting", ".ml-card", "completed");
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

      /* =========================================
        MOODLIFTING
      ========================================= */
      if (type === "moodlifting") {

        card.classList.remove("completed");
        card.classList.remove("reflected");

        const tick = card.querySelector(".ml-tick");
        if (tick) tick.remove();
      }

      /* =========================================
        BODYBOOSTER + MINITASK (THIS IS THE FIX)
      ========================================= */
      else {

        card.classList.remove(doneClass);

        const btn = card.querySelector(".task-start-btn");
        const fill = card.querySelector(".task-bar-fill");

        if (btn) {
          btn.textContent = "Start";
          btn.disabled = false;
        }

        if (fill) {
          fill.style.width = "0%";
        }
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

  const today = new Date().toISOString().split("T")[0];

  // ✅ RESET if NOT today
  if (!data.date || data.date !== today) {

    document.querySelectorAll(selector).forEach(card => {

      if (type === "moodlifting") {
        card.classList.remove("completed", "reflected");

        const tick = card.querySelector(".ml-tick");
        if (tick) tick.remove();
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

    });

    updateProgress(selector, doneClass);
    return;
  }

  const cards = document.querySelectorAll(selector);

  cards.forEach((card, index) => {

    /* =========================
       🌿 MOODLIFTING (SPECIAL)
    ========================= */
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

        card.classList.remove("completed", "reflected");

        const tick = card.querySelector(".ml-tick");
        if (tick) tick.remove();
      }

    }

    /* =========================
       💪 BODYBOOSTER + MINITASK
    ========================= */
    else {

      if (data.completed.includes(index)) {

        card.classList.add(doneClass);

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

    /* =========================================
       MOOD LIFTING (KEEP EXACT)
    ========================================= */
    if (type === "moodlifting") {

      if (data.completed.includes(i)) {

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

      const btn = card.querySelector(".task-start-btn");
      const fill = card.querySelector(".task-bar-fill");

      if (data.completed.includes(i)) {

        card.classList.add(doneClass);

        if (fill) fill.style.width = "100%";

        if (btn) {
          btn.textContent = "Done ✓";
          btn.disabled = true;
        }

      } else {

        card.classList.remove(doneClass);

        if (fill) fill.style.width = "0%";

        if (btn) {
          btn.textContent = "Start";
          btn.disabled = false;
        }
      }
    }

  });

  showEncouragement(type);
  showSparkles();

  updateProgress(selector, doneClass);

  await checkGrowth(type);

  /* 🔥 FORCE LOCALSTORAGE (FIX) */
  const today = new Date().toISOString().split("T")[0];

  const doneCount = document.querySelectorAll(`${selector}.${doneClass}`).length;
  const total = document.querySelectorAll(selector).length;

  if (doneCount === total) {

    // ✅ prevent repeating toast
    if (!localStorage.getItem(`${type}-toast-${today}`)) {

      localStorage.setItem(`${type}-achieved-${today}`, "true");
      localStorage.setItem(`${type}-toast-${today}`, "true");

      showGlobalToast({
        title: "✨ Session complete!",
        message: "You finished all tasks 💚",
        tip: "Your butterfly is growing stronger 🦋"
      });

    }
  }
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
    bodybooster: [
      "Nice movement! Keep going 💪",
      "Your body thanks you 🧘",
      "Energy boost unlocked ⚡",
      "You’re building strength 🌱"
    ],
    minitask: [
      "Small wins matter ✨",
      "One step at a time 💛",
      "Progress is progress 🌼",
      "You showed up today 👏"
    ],
    moodlifting: [
      "That matters 💚",
      "You chose yourself today 🌸",
      "A little lift goes a long way 🌈",
      "Keep nurturing your mood 💫"
    ],
    mindreset: [
      "You gave yourself a reset 🌿",
      "That pause mattered 💚",
      "Breathe… you're doing okay 🌸",
      "You made space for yourself ✨"
    ],
    default: [
      "Your butterfly is growing ✨",
      "You’re doing better than you think 🦋",
      "Growth takes time 🌱",
      "Every step counts 💛"
    ]
  };

  const typeMessages = messages[type] || messages.default;

  const msg =
    typeMessages[Math.floor(Math.random() * typeMessages.length)];

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

  const today = new Date().toISOString().split("T")[0];

  if (data.completed) {
    localStorage.setItem(`${type}-achieved-${today}`, "true");
    showSparkles();
  }
}