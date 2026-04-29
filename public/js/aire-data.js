/* ================================================================
   aire-data.js  —  Unified data layer for Airé
   Now fetches from Laravel API instead of localStorage
   ================================================================ */

const AireData = (() => {


  /* Mood string sets */
  const POSITIVE = ["joyful", "happy", "content"];
  const NEGATIVE = ["anxious", "sad", "tired", "neutral"];

  /* Mood → display label + emoji (will be enriched by API) */
  let MOOD_META = {
    joyful:  { label: "Joyful",  emoji: "😄" },
    happy:   { label: "Happy",   emoji: "😊" },
    content: { label: "Content", emoji: "🙂" },
    neutral: { label: "Neutral", emoji: "😐" },
    anxious: { label: "Anxious", emoji: "😰" },
    sad:     { label: "Sad",     emoji: "😔" },
    tired:   { label: "Tired",   emoji: "😩" },
  };

  /* Butterfly stage definitions */
  const STAGES = {
    egg:         { label: "Egg",         img: "egg.png"},
    pupa:        { label: "Pupa",        img: "pupa.png"},
    caterpillar: { label: "Caterpillar", img: "caterpillar.png"},
    butterfly:   { label: "Butterfly",   img: "adult_glow.png"},
    surviving:   { label: "Surviving",   img: "surviving.jpeg" },
    struggling:  { label: "Struggling",  img: "struggling.jpeg" },
  };

  /* Cache for API data to reduce requests */
  let cache = {
    moodLog: null,
    streak: null,
    daysTracked: null,
    todayCheckIns: null,
    latestMood: null,
    lastFetch: 0
  };

  const CACHE_DURATION = 30000; // 30 seconds

  /* ── Helper: Check if cache is fresh ── */
  function isCacheFresh() {
    return (Date.now() - cache.lastFetch) < CACHE_DURATION;
  }

  /* ── API Call Helper with Error Handling ── */
  async function fetchFromAPI(endpoint, defaultValue = null) {
    try {
      const response = await fetch(`/api/${endpoint}`, {
        method: "GET",
        credentials: "include",
        headers: {
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        }
      });

      if (!response.ok) {
        throw new Error(`API error: ${response.status}`);
      }

      return await response.json();

    } catch (error) {
      console.error(`API fetch error (${endpoint}):`, error);
      return defaultValue;
    }
  }

  /* ── Fetch Mood Meta from API ── */
  async function fetchMoodMeta() {
    return MOOD_META;
  }

  /* ── Get Mood Log from API ── */
  async function getMoodLog() {
    const raw = await fetchFromAPI('mood-log', []);

    return raw.map(e => ({
      ...e,
      mood: e.mood
    }));
  }

  /* ── Get Streak from backend ── */
  async function getStreak() {
    const stats = await getStats();
    return stats.streak;
  }

  /* ── Get Growth Data from backend ── */
  async function getGrowthData() {
    try {
      const response = await fetch('/growth/data', {
        credentials: "include"
      });

      if (!response.ok) {
        throw new Error(`API error: ${response.status}`);
      }

      return await response.json();

    } catch (error) {
      console.error("Growth data fetch error:", error);
      return {
        daysTracked: 0,
        todayCheckIns: 0,
        latestMood: null
      };
    }
  }

  /* ── Calculate Total Points (moods + completed activities) ── */
  async function getTotalPoints() {
    const stats = await getStats();
    return stats.points;
  }

  /* ── Helper Functions ── */
  function todayStr() {
    return new Date().toISOString().split("T")[0];
  }

  /* ── Log a mood to the API ── */
  async function logMood(mood, note) {
    try {
      const response = await fetch('/mood', {
        method: 'POST',
        credentials: "include",
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          mood: mood,
          notes: note
        })
      });

      if (!response.ok) {
        const err = await response.text();
        console.error("❌ Server error:", err);
        throw new Error(`Failed (${response.status})`);
      }

      const data = await response.json();

      // ✅ CLEAR CACHE AFTER SAVE
      cache = {
        moodLog: null,
        streak: null,
        daysTracked: null,
        todayCheckIns: null,
        latestMood: null,
        lastFetch: 0
      };

      return data;

    } catch (error) {
      console.error('❌ Error logging mood:', error);
      throw error;
    }
  }

  /* ── Get Stage Info ── */
  async function getStageInfo() {
    const stageKey = await getStageKey();
    return STAGES[stageKey] || STAGES.egg;
  }

  /* ── Get Stage Key ── */
  async function getStageKey() {
    const stats = await getStats();
    return stats.stage;
  }

  /* ── Get Latest Entry ── */
  async function getLatestEntry() {
    const log = await getMoodLog();
    if (!log.length) return null;
    return log.reduce((a, b) =>
      new Date(a.created_at) > new Date(b.created_at) ? a : b
    );
  }

  /* ── Human-readable mood label ── */
  async function getMoodReflected() {
    const m = await getLatestMood();
    return m ? (MOOD_META[m]?.label || m) : "—";
  }

  /* ── Health label = stage name ── */
  async function getHealth() {
    const info = await getStageInfo();
    return info.label;
  }

  /* ── Message for home breathe card ── */
  async function getButterflyMessage() {
    const msgs = {
      egg:         "Your journey is just beginning. 🥚",
      pupa:        "You're growing in your own quiet way. 🌱",
      caterpillar: "Every step forward matters. 🌿",
      butterfly:   "Your butterfly is glowing softly today. 🦋",
      surviving:   "It's okay to rest. You're still here. 💚",
      struggling:  "Hard days are part of the journey too. 🌧️",
    };
    const stage = await getStageKey();
    return msgs[stage] || msgs.egg;
  }

  /* ── CSS class for image filter ── */
  async function getButterflyClass() {
    const map = {
      egg:         "butterfly-struggling",
      pupa:        "butterfly-surviving",
      caterpillar: "butterfly-thriving",
      butterfly:   "butterfly-excelling",
      surviving:   "butterfly-surviving",
      struggling:  "butterfly-struggling",
    };
    const stage = await getStageKey();
    return map[stage] || "butterfly-struggling";
  }

  /* ── Butterfly Stage String ── */
  async function getButterflyStage() {
    return await getStageKey();
  }

  /* ── Bond Days (same as days tracked) ── */
  async function getBondDays() {
    return await getDaysTracked();
  }

  /* ── Bond Level ── */
  async function getBondLevel() {
    const n = await getDaysTracked();
    if (n >= 14) return "High";
    if (n >= 7)  return "Medium";
    return "Low";
  }

  /* ── Add Bond Day (for backward compatibility) ── */
  async function addBondDay(date) {
    // This will be handled by mood logging
    // Keeping for compatibility
    return true;
  }

  /* ── Growth History ── */
  async function getGrowthHistory() {
    const log = await getMoodLog();
    const byDay = {};
    log.forEach(e => {
      if (!byDay[e.date] || e.ts > byDay[e.date].ts) byDay[e.date] = e;
    });
    return Object.entries(byDay)
      .sort(([a], [b]) => b.localeCompare(a))
      .map(([date, e]) => ({ date, mood: e.mood, note: e.notes, ts: e.created_at }));
  }

  /* ── XP Functions (using API points) ── */
  async function getXP() {
    return await getTotalPoints();
  }

  function addXP(amount) {
    // XP is calculated from actual activities
    // This function kept for compatibility
    console.log('addXP called with', amount);
  }

  /* ── Initialize: Fetch mood meta on load ── */
  fetchMoodMeta();

  // toast
  window.showGlobalToast = function ({ title, message, tip }) {
    const toast = document.getElementById("moodToast");
    if (!toast) return;

    document.getElementById("toastTitle").textContent = title;
    document.getElementById("toastMsg").textContent = message;
    document.getElementById("toastTip").textContent = tip || "";

    toast.classList.add("show");

    setTimeout(() => {
      toast.classList.remove("show");
    }, 3500);
  }

  // backend stats fetch (points, streak, stage)
  async function getStats() {
    return await fetchFromAPI('stats', {
      points: 0,
      streak: 0,
      stage: 'egg'
    });
  }

  function mapMoodLevelToName(level) {
    if (level >= 9) return "joyful";
    if (level >= 7) return "happy";
    if (level >= 5) return "neutral";
    if (level >= 3) return "anxious";
    return "sad";
  }

  /* ── FIX: Missing backend-backed helpers ── */
  async function getDaysTracked() {
    const data = await getGrowthData();
    return data.daysTracked || 0;
  }

  async function getTodayCheckInCount() {
    const data = await getGrowthData();
    return data.todayCheckIns || 0;
  }

  async function getLatestMood() {
    const data = await getGrowthData();
    return data.latestMood || null;
  }

  /* Expose everything - Note: Many functions now return Promises! */
  return {
    logMood,
    getMoodLog,
    getTotalPoints: () => getTotalPoints(),
    getStreak: () => getStreak(),
    getStageKey: () => getStageKey(),
    getStageInfo: () => getStageInfo(),
    getDaysTracked: () => getDaysTracked(),
    getLatestMood: () => getLatestMood(),
    getLatestEntry: () => getLatestEntry(),
    getTodayCheckInCount: () => getTodayCheckInCount(),
    getMoodReflected: () => getMoodReflected(),
    getHealth: () => getHealth(),
    getButterflyMessage: () => getButterflyMessage(),
    getButterflyClass: () => getButterflyClass(),
    getButterflyStage: () => getButterflyStage(),
    getGrowthHistory: () => getGrowthHistory(),
    addBondDay: (date) => addBondDay(date),
    getBondDays: () => getBondDays(),
    getBondLevel: () => getBondLevel(),
    addXP: (amount) => addXP(amount),
    getXP: () => getXP(),
    POSITIVE,
    NEGATIVE,
    MOOD_META: () => MOOD_META,
    STAGES,
    today: () => todayStr(),
    getStats,
  };

})();