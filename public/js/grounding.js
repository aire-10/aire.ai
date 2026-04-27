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
  if (currentStepIndex !== null) {

    // ❌ DO NOT SAVE AFTER FULL COMPLETION
    if (completedSteps.size === TOTAL_STEPS) return;

    // ✅ SAVE INPUT ONLY (TYPING MODE)
    saveToDatabase(currentStepIndex, false, true);
  }
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
      headers: { "Accept": "application/json" }
    });

    const data = await res.json();

    if (!data || !data.has_progress) return;

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
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
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

  document.getElementById("focusDoneBtn").onclick = () => {
    doneStep(new Event("click"), index);
    closeFocusMode();
  };

  document.getElementById("focusNextBtn").onclick = goToNextStep;
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

  // 🔥 FORCE ALL STEPS INTO ARRAY BEFORE SAVE
  const allSteps = Array.from(completedSteps);

  markStepDoneUI(index);

  updateProgress();

  const isCompleted = completedSteps.size === TOTAL_STEPS;

  // ✅ SEND FULL ARRAY (FIX)
  saveToDatabase(allSteps, isCompleted);

  if (isCompleted) showCompletion();
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

function showCompletion() {

  const msg = document.getElementById('completion-msg');
  if (!msg) return;

  msg.classList.add('visible');

  const today = new Date().toISOString().split("T")[0];

  // ✅ CONNECT TO GROWTH SYSTEM (OPTION B)
  if (!localStorage.getItem(`grounding-achieved-${today}`)) {
    localStorage.setItem(`grounding-achieved-${today}`, "true");
  }

  setTimeout(() => {
    msg.classList.remove('visible');
  }, 3000);
}