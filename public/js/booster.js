/* =========================================================
   UNIVERSAL BOOSTER SYSTEM (FIXED FOR BODYBOOSTER)
   ========================================================= */

document.addEventListener('DOMContentLoaded', async () => {

  const page = getCurrentPage();

  if (page === "moodlifting") {
    await initBooster("moodlifting", ".thought-card", "crossed-off");
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

    document.querySelectorAll(selector).forEach(c => {
      c.classList.remove(doneClass);

      const btn = c.querySelector(".task-start-btn");
      const bar = c.querySelector(".task-bar");

      if (btn) {
        btn.textContent = "Start";
        btn.disabled = false;
      }

      if (bar) {
        bar.style.width = "0%";
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

  document.querySelectorAll(selector).forEach((card, index) => {

    if (data.completed.includes(index)) {
      card.classList.add(doneClass);

      const btn = card.querySelector(".task-start-btn");
      const bar = card.querySelector(".task-bar");

      if (btn) {
        btn.textContent = "Done ✓";
        btn.disabled = true;
      }

      if (bar) {
        bar.style.width = "100%";
      }
    }
  });
}


/* =========================================================
   TIMER (FIXED)
   ========================================================= */
function startTaskTimer(card, duration, type, selector, doneClass) {

  const btn = card.querySelector(".task-start-btn");

  // ✅ SAFETY FIX (THIS IS THE KEY)
  if (!btn) return;

  let bar = card.querySelector(".task-bar");

  if (!bar) {
    bar = document.createElement("div");
    bar.className = "task-bar";
    bar.style.height = "6px";
    bar.style.background = "#e0e0e0";
    bar.style.borderRadius = "10px";
    bar.style.margin = "10px 0";
    bar.innerHTML = `<div class="task-bar-fill" style="height:100%; width:0%; background:#2e6b3f; border-radius:10px;"></div>`;

    const top = card.querySelector(".bb-card-top");
    if (top) top.after(bar);
  }

  const fill = bar.querySelector(".task-bar-fill");

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

  document.querySelectorAll(selector).forEach((card, i) => {

    const btn = card.querySelector(".task-start-btn");

    if (btn) {
      if (data.completed.includes(i)) {
        btn.textContent = "Done ✓";
        btn.disabled = true;
      } else {
        btn.textContent = "Start";
        btn.disabled = false;
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