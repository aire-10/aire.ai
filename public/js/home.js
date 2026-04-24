  document.addEventListener("DOMContentLoaded", function () {

    /* ── Daily Affirmations ──────────────────────── */
    const AFFIRMATIONS = [
      "I am allowed to grow at my own pace, just like my butterfly.",
      "I can take one small step today, and that is enough.",
      "My feelings are valid, and they will pass in time.",
      "I am learning to be gentle with myself.",
      "I deserve rest, even when I feel behind.",
      "I can breathe through this moment.",
      "I am doing the best I can with what I have today.",
      "Progress can be quiet and still meaningful."
    ];

    function getDailyIndex() {
      const now  = new Date();
      const seed = (now.getFullYear() * 10000) + ((now.getMonth() + 1) * 100) + now.getDate();
      return seed % AFFIRMATIONS.length;
    }

    function setAffirmation() {
      const el = document.getElementById("affirmationText");
      if (el) el.textContent = AFFIRMATIONS[getDailyIndex()];
    }

    function scheduleMidnightRefresh() {
      const now  = new Date();
      const next = new Date(now);
      next.setHours(24, 0, 0, 0);
      setTimeout(() => { setAffirmation(); scheduleMidnightRefresh(); }, next - now);
    }

    setAffirmation();
    scheduleMidnightRefresh();

    /* ── Pet card image mapping ────────────────────── */
    const STAGE_IMAGES = {
      egg:         "egg.png",
      pupa:        "pupa.png",
      caterpillar: "caterpillar.png",
      butterfly:   "adult_glow.png",
      surviving:   "surviving.jpeg",
      struggling:  "struggling.jpeg"
    };

    const STAGE_LABELS = {
      egg:         "Egg",
      pupa:        "Pupa",
      caterpillar: "Caterpillar",
      butterfly:   "Butterfly 🦋",
      surviving:   "Surviving",
      struggling:  "Struggling"
    };

    const STAGE_MESSAGES = {
      egg:         "Your journey is just beginning. 🥚",
      pupa:        "You're transforming, little one. 🐛",
      caterpillar: "Every step forward matters. 🐾",
      butterfly:   "Spread your wings! You're glowing. 🦋",
      surviving:   "You're getting through this. 💚",
      struggling:  "One small step at a time. You've got this. 🌱"
    };

    function refreshPetCard() {
      const streak = AireData.getStreak();
      const hasReachedButterfly = localStorage.getItem("hasReachedButterfly") === "true";
      const consecutiveNeg = parseInt(localStorage.getItem("consecutiveNeg") || "0");
      const recoveryStreak = parseInt(localStorage.getItem("recoveryStreak") || "0");
      const daysTracked = AireData.getDaysTracked();

      const stageKey = AireData.getStageKey()
      const imgPath = STAGE_IMAGES[stageKey] || "egg.png";
      const stageLabel = STAGE_LABELS[stageKey] || "Egg";
      const message = STAGE_MESSAGES[stageKey] || "Your journey continues. 🌿";

      const imgEl = document.getElementById("dashPetImg");
      const stageEl = document.getElementById("dashPetStage");
      const msgEl = document.getElementById("dashPetMsg");
      const streakEl = document.getElementById("dashPetStreak");
      const daysEl = document.getElementById("dashPetDays");

      if (imgEl) imgEl.src = imgPath;
      if (stageEl) stageEl.textContent = stageLabel;
      if (msgEl) msgEl.textContent = message;
      if (streakEl) streakEl.textContent = `🔥 ${streak} streak`;
      if (daysEl) daysEl.textContent = `📅 ${daysTracked} days`;
    }

    refreshPetCard();

    /* ── Toast helpers ───────────────────────────── */
    const TIPS = {
      egg:         "Log a Joyful 😄 or Happy 😊 mood to hatch your egg!",
      pupa:        "Keep logging positive moods for 1–2 days to become a Caterpillar 🐛",
      caterpillar: "You're so close! 3–4 days positive gets you to Butterfly 🦋",
      butterfly:   "Amazing! Keep your streak alive to stay a Butterfly 🦋",
      surviving:   "Log Joyful or Happy moods for 5 days to become a Butterfly again 🌤️",
      struggling:  "One positive mood starts your recovery. You've got this! 💚",
    };

    const TITLES = {
      joyful:  "🌟 Your butterfly is thriving!",
      happy:   "🌿 Your butterfly is glowing!",
      neutral: "😐 Your butterfly is resting…",
      sad:     "💙 Your butterfly feels your sadness.",
      tired:   "😴 Your butterfly is tired too.",
      anxious: "💙 Your butterfly is with you.",
    };

    let toastTimer = null;

    function showToast(mood) {
      const streak = AireData.getStreak();
      const hasReachedButterfly = localStorage.getItem("hasReachedButterfly") === "true";
      const consecutiveNeg = parseInt(localStorage.getItem("consecutiveNeg") || "0");
      const recoveryStreak = parseInt(localStorage.getItem("recoveryStreak") || "0");
      const stageKey = AireData.getStageKey();
      const stageLabel = STAGE_LABELS[stageKey] || "Egg";
      const imgPath = STAGE_IMAGES[stageKey] || "egg.png";

      const toast = document.getElementById("moodToast");
      const titleEl = document.getElementById("toastTitle");
      const msgEl = document.getElementById("toastMsg");
      const tipEl = document.getElementById("toastTip");
      const imgEl = document.getElementById("toastPetImg");

      if (!toast) return;

      titleEl.textContent = TITLES[mood] || "🌱 Your butterfly noticed your mood.";
      msgEl.textContent = `Stage: ${stageLabel} · Streak: ${streak} day${streak !== 1 ? "s" : ""}`;
      tipEl.textContent = TIPS[stageKey] || "";
      if (imgEl) imgEl.src = imgPath;

      toast.classList.add("show");

      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => toast.classList.remove("show"), 5000);
    }

    document.getElementById("toastClose")?.addEventListener("click", () => {
      document.getElementById("moodToast")?.classList.remove("show");
    });

    /* ── Mood Check-in ───────────────────────────── */
    const emojis = document.querySelectorAll(".dash-mood-emoji");
    const savedMsg = document.getElementById("dashMoodSaved");

    emojis.forEach(el => {
      el.addEventListener("click", () => {
        emojis.forEach(e => e.classList.remove("selected"));
        el.classList.add("selected");

        const mood = el.dataset.mood;

        // ✅ USE YOUR REAL SYSTEM
        AireData.logMood(mood);

        const moodLabels = {
          joyful: "😄 Joyful",
          happy: "😊 Happy",
          neutral: "😐 Neutral",
          sad: "😢 Sad",
          tired: "😔 Tired"
        };

        savedMsg.textContent = `${moodLabels[mood]} saved!`;

        refreshPetCard();
        showToast(mood);

        setTimeout(() => emojis.forEach(e => e.classList.remove("selected")), 800);
      });
    });

    /* Show last logged mood if already checked in today */
    const latest = AireData.getLatestEntry();
    const today = new Date().toISOString().split("T")[0];
    if (latest && latest.date === today) {
      const moodLabels = {
        joyful: "😄 Joyful",
        happy: "😊 Happy",
        neutral: "😐 Neutral",
        sad: "😢 Sad",
        tired: "😔 Tired"
      };
      savedMsg.textContent = `Last: ${moodLabels[latest.mood] || latest.mood}`;
    }

  });