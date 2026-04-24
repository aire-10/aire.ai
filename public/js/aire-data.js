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
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      if (!response.ok) {
        throw new Error(`API error: ${response.status}`);
      }
      
      const data = await response.json();
      return data;
    } catch (error) {
      console.error(`API fetch error (${endpoint}):`, error);
      return defaultValue;
    }
  }

  /* ── Fetch Mood Meta from API ── */
  async function fetchMoodMeta() {
    const meta = await fetchFromAPI('mood-meta', MOOD_META);
    if (meta) {
      MOOD_META = { ...MOOD_META, ...meta };
    }
    return MOOD_META;
  }

  /* ── Get Mood Log from API ── */
  async function getMoodLog() {
    if (!cache.moodLog || !isCacheFresh()) {
      cache.moodLog = await fetchFromAPI('mood-log', []);
      cache.lastFetch = Date.now();
    }
    return cache.moodLog;
  }

  /* ── Get Streak from API ── */
  async function getStreak() {
    if (!cache.streak || !isCacheFresh()) {
      cache.streak = await fetchFromAPI('streak', 0);
    }
    return cache.streak;
  }

  /* ── Get Days Tracked from API ── */
  async function getDaysTracked() {
    if (!cache.daysTracked || !isCacheFresh()) {
      cache.daysTracked = await fetchFromAPI('days-tracked', 0);
    }
    return cache.daysTracked;
  }

  /* ── Get Today's Check-in Count from API ── */
  async function getTodayCheckInCount() {
    if (!cache.todayCheckIns || !isCacheFresh()) {
      cache.todayCheckIns = await fetchFromAPI('today-checkins', 0);
    }
    return cache.todayCheckIns;
  }

  /* ── Get Latest Mood from API ── */
  async function getLatestMood() {
    if (!cache.latestMood || !isCacheFresh()) {
      cache.latestMood = await fetchFromAPI('latest-mood', null);
    }
    return cache.latestMood;
  }

  /* ── Calculate Stage based on points and mood history ── */
  async function calcStageKey() {
    const log = await getMoodLog();
    const points = await getTotalPoints();
    
    if (!log.length) return "egg";
    
    // Check if user has reached butterfly before (points >= 50)
    const hasReachedButterfly = points >= 50;
    
    if (hasReachedButterfly) {
      // Check recent negative moods for decline
      const recentLogs = log.slice(-5);
      const negativeCount = recentLogs.filter(entry => 
        NEGATIVE.includes(entry.mood)
      ).length;
      
      if (negativeCount >= 2) return "struggling";
      if (negativeCount >= 1) return "surviving";
      return "butterfly";
    }
    
    // Normal growth based on points
    if (points < 10) return "egg";
    if (points < 30) return "caterpillar";
    if (points < 50) return "pupa";
    return "butterfly";
  }

  /* ── Calculate Total Points (moods + completed activities) ── */
  async function getTotalPoints() {
    const log = await getMoodLog();
    let points = 0;
    
    // Each mood entry = 1 point
    points += log.length;
    
    // Positive mood bonus = +1 point
    const positiveMoods = log.filter(entry => POSITIVE.includes(entry.mood));
    points += positiveMoods.length;
    
    // Task completions (from localStorage for now, eventually from API)
    const today = todayStr();
    if (localStorage.getItem("grounding-achieved") === today) points += 0.5;
    if (localStorage.getItem("moodbooster-achieved") === today) points += 0.5;
    if (localStorage.getItem("mindreset-achieved") === today) points += 0.5;
    if (localStorage.getItem("minitask-achieved") === today) points += 0.5;
    
    return points;
  }

  /* ── Helper Functions ── */
  function todayStr() {
    return new Date().toISOString().split("T")[0];
  }

  /* ── Log a mood to the API ── */
  async function logMood(mood, note) {
    try {
      const response = await fetch('/api/log-mood', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({ mood, note })
      });
      
      if (!response.ok) throw new Error('Failed to log mood');
      
      const result = await response.json();
      
      // Invalidate cache
      cache.moodLog = null;
      cache.streak = null;
      cache.daysTracked = null;
      cache.todayCheckIns = null;
      cache.latestMood = null;
      
      return result;
    } catch (error) {
      console.error('Error logging mood:', error);
      throw error;
    }
  }

  /* ── Get Stage Info ── */
  async function getStageInfo() {
    const stageKey = await calcStageKey();
    return STAGES[stageKey] || STAGES.egg;
  }

  /* ── Get Stage Key ── */
  async function getStageKey() {
    return await calcStageKey();
  }

  /* ── Get Latest Entry ── */
  async function getLatestEntry() {
    const log = await getMoodLog();
    if (!log.length) return null;
    return log.reduce((a, b) => (a.ts > b.ts ? a : b));
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
      .map(([date, e]) => ({ date, mood: e.mood, note: e.note, ts: e.ts }));
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
  };

})();