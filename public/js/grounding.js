document.addEventListener("DOMContentLoaded", async () => {

  const overlay = document.getElementById("focusOverlay");

  if (overlay) {
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) closeFocusMode();
    });
  }

  // ✅ LOAD ONLY ONCE
  await loadFromDatabase();

});

document.addEventListener("input", () => {
  if (currentStepIndex === null) return;

  // ❌ DO NOT SAVE AFTER FULL COMPLETION
  if (completedSteps.size === TOTAL_STEPS) return;

  // ❌ DO NOT SAVE if this step is already completed
  if (completedSteps.has(currentStepIndex)) return;

  // ✅ SAFE TYPING SAVE
  saveToDatabase(currentStepIndex, false, true);
});

let currentStepIndex = null;
const TOTAL_STEPS = 5;
const completedSteps = new Set();

/* =========================
   🔥 API FUNCTIONS
========================= */

async function loadFromDatabase() {
  try {
    const res = await fetch('/api/grounding/progress', {
      credentials: 'include',
      headers: { "Accept": "application/json" }
    });

    const data = await res.json();

    const today = new Date().toLocaleDateString('en-CA'); 
    const apiDate = data.date ? data.date.split("T")[0] : null;

    if (!data || !data.has_progress || apiDate !== today) {

      completedSteps.clear();

      // CLEAR INPUTS
      for (let i = 0; i < TOTAL_STEPS; i++) {
        const container = document.getElementById(`inputs-${i}`);
        if (container) {
          container.querySelectorAll(".step-input")
            .forEach(input => input.value = "");
        }
      }

      // RESET UI
      for (let i = 0; i < TOTAL_STEPS; i++) {
        const step = document.getElementById(`step-${i}`);
        if (step) {
          step.classList.remove("done");
          const icon = step.querySelector(".step-icon");
          if (icon) icon.textContent = "";
        }

        const arrow = document.getElementById(`arrow-${i}`);
        if (arrow) {
          arrow.textContent = "›";
          arrow.style.color = "";
        }
      }

      updateProgress();
      return;
    }

    // ✅ RESET FIRST
    completedSteps.clear();

    // ✅ RESTORE STEPS
    (data.completed_steps || []).forEach(i => {
      completedSteps.add(i);
      markStepDoneUI(i);
    });

    // ✅ RESTORE INPUTS PROPERLY
    const inputs = data.step_inputs || {};

    Object.keys(inputs).forEach(stepIndex => {
      const container = document.getElementById(`inputs-${stepIndex}`);
      if (!container) return;

      const inputEls = container.querySelectorAll(".step-input");
      const values = inputs[stepIndex] || [];

      inputEls.forEach((input, i) => {
        input.value = values[i] || "";
      });
    });

    // 🔥 FORCE UI consistency
    for (let i = 0; i < TOTAL_STEPS; i++) {
      if (completedSteps.has(i)) {
        markStepDoneUI(i);
      }
    }

    updateProgress();

  } catch (err) {
    console.error("❌ load grounding error:", err);
  }
}

async function saveToDatabase(stepsOrIndex, isCompleted = false, isTyping = false) {
  try {

    let payload = {
      inputs: getAllInputs(),
      is_completed: isCompleted
    };

    // ✅ HANDLE FULL ARRAY (FROM doneStep)
    if (Array.isArray(stepsOrIndex)) {
      payload.step_index = stepsOrIndex[stepsOrIndex.length - 1] ?? 0;
      payload.completed_steps = stepsOrIndex;
    } 
    // ✅ HANDLE TYPING
    else {
      payload.step_index = stepsOrIndex ?? 0;

      if (!isTyping) {
        payload.completed_steps = Array.from(completedSteps);
      }
    }

    console.log("🔥 SENDING:", payload);

    const res = await fetch('/api/grounding/progress', {
    method: 'POST',
    credentials: 'include', // ✅ ADD THIS
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(payload)
  });

    const text = await res.text();

    if (!res.ok) {
      console.error("❌ SAVE FAILED:", text);
      return;
    }

    console.log("✅ SAVED:", text);

  } catch (err) {
    console.error("❌ save grounding error:", err);
  }
}
/* =========================
   🎯 FOCUS MODE (RESTORED)
========================= */

function openFocusMode(index) {

  currentStepIndex = index;

  const overlay = document.getElementById("focusOverlay");
  const title = document.getElementById("focusTitle");
  const guide = document.querySelector(".focus-guide");
  const inputsContainer = document.getElementById("focusInputs");

  const labels = [
    "5 Things you see",
    "4 Things you feel",
    "3 Things you hear",
    "2 Things you smell",
    "1 Thing you like"
  ];

  const guides = [
    "Look around you gently…",
    "Notice your body…",
    "Listen carefully…",
    "Notice scents…",
    "Think of something you like…"
  ];

  title.textContent = labels[index];
  guide.textContent = guides[index];

  inputsContainer.innerHTML =
    document.querySelector(`#inputs-${index}`).innerHTML;

  overlay.classList.remove("hidden");

  setTimeout(() => overlay.classList.add("show"), 10);

  const doneBtn = document.getElementById("focusDoneBtn");
  if (doneBtn) {
    doneBtn.onclick = () => {
      doneStep(new Event("click"), index);
      closeFocusMode();
    };
  }

  const nextBtn = document.getElementById("focusNextBtn");
  if (nextBtn) {
    nextBtn.onclick = goToNextStep;
  }
}

function closeFocusMode() {
  const overlay = document.getElementById("focusOverlay");
  overlay.classList.remove("show");
  setTimeout(() => overlay.classList.add("hidden"), 300);
}

function goToNextStep() {

  doneStep(new Event("click"), currentStepIndex);

  if (completedSteps.size === TOTAL_STEPS) {
    closeFocusMode();
    return;
  }

  let next = currentStepIndex + 1;

  while (completedSteps.has(next) && next < TOTAL_STEPS) {
    next++;
  }

  openFocusMode(next);
}

/* =========================
   UI LOGIC
========================= */

function toggleStep(index) {
  if (completedSteps.has(index)) return;

  // 🔥 THIS WAS MISSING → open focus mode
  openFocusMode(index);
}

function doneStep(e, index) {
  if (e) e.stopPropagation();

  // ✅ ADD STEP FIRST
  completedSteps.add(index);

  const allSteps = Array.from(completedSteps);

  markStepDoneUI(index);

  updateProgress();

  const isCompleted = completedSteps.size === TOTAL_STEPS;

  // ✅ SAVE TO BACKEND
  saveToDatabase(allSteps, isCompleted);

  // 🔥 ADD THIS (MATCH BOOSTER BEHAVIOR)
  showEncouragement();
  showSparkles();

  // ✅ ONLY SHOW TOAST WHEN FULL COMPLETE
  if (isCompleted) {
    showCompletion();
  }

  window.dispatchEvent(new Event("aire:mood-logged"));
}

function markStepDoneUI(index) {
  const step = document.getElementById(`step-${index}`);
  if (step) {
    step.classList.add("done");
    const icon = step.querySelector(".step-icon");
    if (icon) icon.textContent = "✓";
  }

  const arrow = document.getElementById(`arrow-${index}`);
  if (arrow) {
    arrow.textContent = "✓";
    arrow.style.color = "#2d5c28";
  }
}

function getAllInputs() {
  const result = {};

  for (let i = 0; i < TOTAL_STEPS; i++) {
    const inputs = document.querySelectorAll(`#inputs-${i} .step-input`);

    const values = Array.from(inputs)
      .map(i => i.value.trim())
      .filter(v => v !== ""); // 🔥 REMOVE EMPTY VALUES

    result[i] = values;
  }

  return result;
}

function updateProgress() {
  const done = completedSteps.size;
  const pct = (done / TOTAL_STEPS) * 100;

  document.getElementById('progress-fill').style.width = `${pct}%`;
  document.getElementById('progress-label').textContent =
    `${done} / ${TOTAL_STEPS} complete`;
}

async function showCompletion() {

  const today = new Date().toISOString().split("T")[0];

  if (localStorage.getItem(`grounding-toast-${today}`)) return;

  // ✅ SMALL DELAY (IMPORTANT)
  await new Promise(r => setTimeout(r, 300));

  try {
    const res = await fetch('/api/grounding/check', {
      credentials: 'include'
    });
    const data = await res.json();

    if (data.completed) {

      localStorage.setItem(`grounding-toast-${today}`, "true");

      showGlobalToast({
        title: "🌿 Grounding complete!",
        message: "You completed your grounding exercise 💚",
        tip: "Your butterfly feels calmer 🦋"
      });

    }

  } catch (err) {
    console.error("❌ completion check error:", err);
  }
}

/* =========================
   ✨ EFFECTS (COPY FROM BOOSTER)
========================= */

function showEncouragement() {

  const messages = [
    "Good… stay present 💚",
    "You're doing great 🌿",
    "Keep grounding yourself 🌱",
    "You’re safe in this moment 🤍",
    "Breathe… you're okay 🌸"
  ];

  const msg = messages[Math.floor(Math.random() * messages.length)];

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