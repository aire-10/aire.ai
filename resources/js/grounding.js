document.addEventListener("DOMContentLoaded", () => {

  const overlay = document.getElementById("focusOverlay");

  if (overlay) {
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        closeFocusMode();
      }
    });
  }

  // 🔹 Load + restore (now async)
  loadInputs();
  loadSteps().then(() => {
    autoCompleteStepsFromInputs();
    checkGroundingCompletion();
  });

  // 🔹 Input listeners
  document.querySelectorAll(".step-input").forEach(input => {
    input.addEventListener("input", () => {
      saveInputs();
      autoCompleteStepsFromInputs();
      saveSteps(); // Save to API when inputs change
    });
  });
});

let currentStepIndex = null;
const STORAGE_KEY = "grounding-progress";
const ACHIEVEMENT_KEY = "grounding-achieved";
const POPUP_KEY = "grounding-popup";
const TOTAL_STEPS = 5;
const completedSteps = new Set();

const groundingMessages = [
  "Good… stay present 💚",
  "You’re doing well 🌿",
  "Keep going gently 🌱",
  "You’re grounding yourself 🫶",
  "That’s a nice moment ✨"
];

// ────────────────── API FUNCTIONS ──────────────────

async function saveProgressToAPI(completedStepsArray, stepInputs, isCompleted) {
    try {
        const response = await fetch('/api/grounding/progress', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                completed_steps: Array.from(completedStepsArray),
                inputs: stepInputs,
                is_completed: isCompleted
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error saving progress:', error);
        return null;
    }
}

async function loadProgressFromAPI() {
    try {
        const response = await fetch('/api/grounding/progress', {
            headers: {
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error loading progress:', error);
        return null;
    }
}

async function checkGroundingCompletionAPI() {
    try {
        const response = await fetch('/api/grounding/today-completion', {
            headers: {
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        return data.completed;
    } catch (error) {
        console.error('Error checking completion:', error);
        return false;
    }
}

// ────────────────── CORE FUNCTIONS ──────────────────

function toggleStep(index) {
  if (completedSteps.has(index)) return;
  openFocusMode(index);
}

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

  const guidanceTexts = [
    "Take a moment. Gently look around you…",
    "Take a moment. Gently feel around you…",
    "Take a moment. Listen closely…",
    "Take a moment. Notice the scents around you…",
    "Take a moment. Think of something you appreciate…"
  ];

  title.textContent = labels[index];
  guide.textContent = guidanceTexts[index];

  // clone inputs from original panel
  const originalInputs = document.querySelector(`#inputs-${index}`).innerHTML;
  inputsContainer.innerHTML = originalInputs;

  // Add input listeners to cloned inputs
  inputsContainer.querySelectorAll(".step-input").forEach(input => {
    input.addEventListener("input", () => {
      saveInputs();
      // Update original inputs as well
      const originalInputsList = document.querySelectorAll(`#inputs-${index} .step-input`);
      const clonedInputsList = inputsContainer.querySelectorAll(".step-input");
      clonedInputsList.forEach((cloneInput, idx) => {
        if (originalInputsList[idx]) {
          originalInputsList[idx].value = cloneInput.value;
        }
      });
      autoCompleteStepsFromInputs();
      saveSteps();
    });
  });

  overlay.classList.remove("hidden");

  setTimeout(() => {
    overlay.classList.add("show");
  }, 10);

  // DONE BUTTON
  document.getElementById("focusDoneBtn").onclick = () => {
    doneStep(new Event("click"), index);
    closeFocusMode();
  };
  
  // NEXT BUTTON
  document.getElementById("focusNextBtn").onclick = () => {
    goToNextStep();
  };
}

function closeFocusMode() {
  const overlay = document.getElementById("focusOverlay");
  overlay.classList.remove("show");

  setTimeout(() => {
    overlay.classList.add("hidden");
  }, 300);
}

function showGroundingFeedback() {
  const msg = groundingMessages[Math.floor(Math.random() * groundingMessages.length)];

  const popup = document.createElement("div");
  popup.className = "grounding-feedback";
  popup.textContent = msg;

  document.body.appendChild(popup);

  setTimeout(() => popup.classList.add("show"), 10);

  setTimeout(() => {
    popup.classList.remove("show");
    setTimeout(() => popup.remove(), 300);
  }, 2000);
}

function doneStep(event, index) {
  if (event) event.stopPropagation();

  completedSteps.add(index);

  // ✅ SAVE steps (to localStorage AND API)
  saveSteps();

  // Mark as done
  const stepBtn = document.getElementById(`step-${index}`);

  if (stepBtn) {
    stepBtn.classList.add('done');
    const icon = stepBtn.querySelector('.step-icon');
    if (icon) icon.textContent = '✓';
  }

  // Close panel
  const panel = document.getElementById(`panel-${index}`);
  const arrow = document.getElementById(`arrow-${index}`);
  if (panel) panel.classList.remove('open');
  if (arrow) {
    arrow.classList.remove('open');
    arrow.textContent = '✓';
    arrow.style.color = '#2d5c28';
  }

  // Update progress
  updateProgress();

  if (completedSteps.size === TOTAL_STEPS) {
    closeFocusMode(); 
  } else {
    showGroundingFeedback();
  }

  checkGroundingCompletion();
}

function goToNextStep() {
  doneStep(new Event("click"), currentStepIndex);

  if (completedSteps.size === TOTAL_STEPS) {
    closeFocusMode();
    return;
  }

  const card = document.querySelector(".focus-card");
  card.classList.add("fade-out");

  setTimeout(() => {
    card.classList.remove("fade-out");

    let next = currentStepIndex + 1;
    while (completedSteps.has(next) && next < TOTAL_STEPS) {
      next++;
    }
    openFocusMode(next);
  }, 300);
}

function updateProgress() {
  const done = completedSteps.size;
  const pct = (done / TOTAL_STEPS) * 100;

  const progressFill = document.getElementById('progress-fill');
  const progressLabel = document.getElementById('progress-label');
  
  if (progressFill) progressFill.style.width = `${pct}%`;
  if (progressLabel) progressLabel.textContent = `${done} / ${TOTAL_STEPS} complete`;

  const msg = document.getElementById('completion-msg');

  if (done === TOTAL_STEPS && msg && !msg.classList.contains('visible')) {
    const today = new Date().toISOString().split("T")[0];
    const lastShown = localStorage.getItem(POPUP_KEY);

    if (lastShown !== today) {
      msg.classList.add('visible');
      showSparkles();
      
      setTimeout(() => {
        showTaskGrowthToast();
      }, 400);

      setTimeout(() => {
        msg.classList.remove('visible');
      }, 3000);

      localStorage.setItem(POPUP_KEY, today);
    }
  }
}

function saveInputs() {
  const allInputs = document.querySelectorAll(".step-input");
  const values = Array.from(allInputs).map(input => input.value);
  localStorage.setItem(STORAGE_KEY + "-inputs", JSON.stringify(values));
}

function loadInputs() {
  const saved = JSON.parse(localStorage.getItem(STORAGE_KEY + "-inputs")) || [];
  const allInputs = document.querySelectorAll(".step-input");

  allInputs.forEach((input, index) => {
    if (saved[index]) {
      input.value = saved[index];
    }
  });
}

function autoCompleteStepsFromInputs() {
  let changed = false;
  
  for (let i = 0; i < TOTAL_STEPS; i++) {
    const inputs = document.querySelectorAll(`#inputs-${i} .step-input`);
    const allFilled = Array.from(inputs).every(input => input.value.trim() !== "");

    if (allFilled && !completedSteps.has(i)) {
      completedSteps.add(i);
      changed = true;

      const step = document.getElementById(`step-${i}`);
      if (step) {
        step.classList.add("done");
        const icon = step.querySelector(".step-icon");
        if (icon) icon.textContent = "✓";
      }

      const arrow = document.getElementById(`arrow-${i}`);
      if (arrow) {
        arrow.textContent = "✓";
        arrow.style.color = "#2d5c28";
      }
    }
  }

  if (changed) {
    updateProgress();
    saveSteps(); // Save to API when auto-completed
  }
}

// REPLACED saveSteps() - Now saves to API
function saveSteps() {
  const stepsArray = Array.from(completedSteps);
  
  // Save to localStorage (backup)
  localStorage.setItem(STORAGE_KEY + "-steps", JSON.stringify(stepsArray));
  
  // Save to API
  const allInputs = document.querySelectorAll(".step-input");
  const inputsByStep = {};
  for (let i = 0; i < TOTAL_STEPS; i++) {
    const stepInputs = document.querySelectorAll(`#inputs-${i} .step-input`);
    inputsByStep[i] = Array.from(stepInputs).map(input => input.value);
  }
  
  const isCompleted = completedSteps.size === TOTAL_STEPS;
  saveProgressToAPI(stepsArray, inputsByStep, isCompleted);
}

// REPLACED loadSteps() - Now loads from API first
async function loadSteps() {
  try {
    // Try loading from API first
    const apiData = await loadProgressFromAPI();
    
    if (apiData && apiData.has_progress) {
      // Load from API
      apiData.completed_steps.forEach(index => {
        if (!completedSteps.has(index)) {
          completedSteps.add(index);
          
          // Restore UI
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
      });
      
      // Restore input values
      if (apiData.step_inputs) {
        for (let stepIndex = 0; stepIndex < TOTAL_STEPS; stepIndex++) {
          const inputs = apiData.step_inputs[stepIndex];
          if (inputs && inputs.length > 0) {
            const inputElements = document.querySelectorAll(`#inputs-${stepIndex} .step-input`);
            inputs.forEach((value, idx) => {
              if (inputElements[idx] && value) {
                inputElements[idx].value = value;
              }
            });
          }
        }
      }
      
      updateProgress();
      return;
    }
  } catch (error) {
    console.error('API load failed, falling back to localStorage:', error);
  }
  
  // Fallback to localStorage
  const saved = JSON.parse(localStorage.getItem(STORAGE_KEY + "-steps")) || [];
  saved.forEach(index => {
    if (!completedSteps.has(index)) {
      completedSteps.add(index);
      
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
  });
  
  updateProgress();
}

function showSparkles() {
  for (let i = 0; i < 25; i++) {
    const sparkle = document.createElement("div");
    sparkle.className = "sparkle";
    sparkle.style.left = Math.random() * window.innerWidth + "px";
    sparkle.style.top = Math.random() * window.innerHeight + "px";
    document.body.appendChild(sparkle);
    setTimeout(() => sparkle.remove(), 1200);
  }
}

// REPLACED checkGroundingCompletion() - Now checks API
async function checkGroundingCompletion() {
  if (completedSteps.size === TOTAL_STEPS) {
    const todayStr = new Date().toISOString().split("T")[0];
    const key = ACHIEVEMENT_KEY;
    
    const lastCompleted = localStorage.getItem(key);
    
    if (lastCompleted !== todayStr) {
      // Check API to confirm not already completed today
      const apiCompleted = await checkGroundingCompletionAPI();
      
      if (!apiCompleted) {
        // Give XP
        if (window.AireData && typeof AireData.addXP === "function") {
          AireData.addXP(0.5);
        }
        
        if (typeof renderHero === "function") {
          renderHero([]);
        }
        if (typeof renderActions === "function") {
          renderActions();
        }
        
        // Save achievement
        localStorage.setItem(key, todayStr);
        if (typeof renderActions === "function") {
          renderActions();
        }
        
        // Show encouragement
        showEncouragement("groundingMessages");
      }
    }
  }
}

function showEncouragement() {
  const popup = document.createElement("div");
  popup.className = "grounding-feedback";
  popup.textContent = "🌟 +0.5 XP! Your butterfly is growing! 🌟";
  document.body.appendChild(popup);
  setTimeout(() => popup.classList.add("show"), 10);
  setTimeout(() => {
    popup.classList.remove("show");
    setTimeout(() => popup.remove(), 300);
  }, 2500);
}

/* Popup for showing task growth progress (butterfly) */
function showTaskGrowthToast() {
  if (!window.AireData) return;
  
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

  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");
  const toastTip = document.getElementById("toastTip");
  
  if (toastTitle) toastTitle.textContent = "🌿 Your butterfly is growing!";
  if (toastMsg) toastMsg.textContent = `Stage: ${STAGE_LABELS[stageKey]} · Streak: ${streak} day${streak !== 1 ? "s" : ""}`;
  if (toastTip) toastTip.textContent = TASK_TIPS[stageKey] || "Keep going! 💪";

  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 5000);
}