document.addEventListener("DOMContentLoaded", async () => {

  const overlay = document.getElementById("focusOverlay");

  if (overlay) {
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) closeFocusMode();
    });
  }

  await loadFromDatabase();

document.querySelectorAll(".step-input").forEach(input => {
  input.addEventListener("input", () => {
    saveToDatabase(currentStepIndex || 0, false);
  });
}); 
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
      const values = inputs[stepIndex];

      values.forEach((val, i) => {
        const input = document.querySelectorAll(`#inputs-${stepIndex} .step-input`)[i];
        if (input) input.value = val;
      });
    });

    updateProgress();

  } catch (err) {
    console.error("❌ load grounding error:", err);
  }
}

async function saveToDatabase(stepIndex, isCompleted = false) {
  try {

    const payload = {
      step_index: stepIndex ?? 0,
      inputs: getAllInputs(),
      completed_steps: Array.from(completedSteps),
      is_completed: isCompleted
    };

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

  completedSteps.add(index);

  markStepDoneUI(index);

  updateProgress();

  const isCompleted = completedSteps.size === TOTAL_STEPS;

  saveToDatabase(index, isCompleted);

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

  setTimeout(() => {
    msg.classList.remove('visible');
  }, 3000);
}