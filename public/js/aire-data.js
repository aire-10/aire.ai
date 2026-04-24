/* ================================================================
   aire-data.js  —  Unified data layer for Airé
   Storage key: "aire_mood_log"
   Each entry: { date: "YYYY-MM-DD", mood: string, note: string, ts: ISO }
   Multiple entries per day are ALL stored (not overwritten)
   ================================================================ */

const AireData = (() => {

  const MOOD_LOG_KEY = "aire_mood_log";
  const DAYS_KEY     = "aire_days_tracked"; // array of unique date strings

  /* Mood string sets */
  const POSITIVE = ["happy", "joyful"];
  const NEGATIVE = ["anxious", "sad", "tired", "neutral"];

  /* Mood → display label + emoji */
  const MOOD_META = {
    joyful:  { label: "Joyful",  emoji: "😄" },
    happy:   { label: "Happy",   emoji: "😊" },
    neutral: { label: "Neutral", emoji: "😐" },
    anxious: { label: "Anxious", emoji: "😰" },
    sad:     { label: "Sad",     emoji: "😔" },
    tired:   { label: "Tired",   emoji: "😩" },
    content: { label: "Content", emoji: "🙂" },
  };

  /* Butterfly stage definitions */
  const STAGES = {
    egg:         { label: "Egg",         img: "egg.png"},
    pupa:        { label: "Pupa",        img: "pupa.png"},
    caterpillar: { label: "Caterpillar", img: "catepillar.png"},
    butterfly:   { label: "Butterfly",   img: "daisybutterfly.png"},
    surviving:   { label: "Surviving",   img: "surviving.jpeg" },
    struggling:  { label: "Struggling",  img: "struggling.jpeg" },
  };

  /* ── Helpers ──────────────────────────────────────── */
  function todayStr() {
    return new Date().toISOString().split("T")[0];
  }

  function load(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      return raw !== null ? JSON.parse(raw) : fallback;
    } catch { return fallback; }
  }

  function save(key, val) {
    localStorage.setItem(key, JSON.stringify(val));
  }

  /* ── Stage key from streak + previous stage ───────── */
  function stageKeyFromStreak(streak, prevStageKey, latestMood) {
    const isNeg = NEGATIVE.includes(latestMood);
    const prev  = prevStageKey || "egg";

    if (prev === "butterfly" && isNeg)   return "surviving";
    if (prev === "struggling" && !isNeg) return "surviving";
    if (prev === "surviving"  && streak >= 5) return "butterfly";

    // ❌ REMOVE streak-based growth
    // ✅ Keep current stage (no forced evolution)
    return prevStageKey || "egg";
  }

  /* ── Streak: consecutive positive days (latest mood per day) ──
     Skipped days don't break streak; explicit negative does.     */
  function calcStreak(log) {
    if (!log.length) return 0;
    const byDay = {};
    log.forEach(e => {
      if (!byDay[e.date] || e.ts > byDay[e.date].ts) byDay[e.date] = e;
    });
    const days = Object.keys(byDay).sort().reverse();
    let streak = 0;
    for (const day of days) {
      if (POSITIVE.includes(byDay[day].mood)) streak++;
      else break;
    }
    return streak;
  }

  /* ── Stage key: walk days oldest→newest, track stage transitions ── */
  function calcStageKey(log) {
    if (!log.length) return "egg";
    const byDay = {};
    log.forEach(e => {
      if (!byDay[e.date] || e.ts > byDay[e.date].ts) byDay[e.date] = e;
    });
    const days = Object.keys(byDay).sort();
    let stageKey = "egg";
    let streak   = 0;
    days.forEach(day => {
      const mood = byDay[day].mood;
      if (POSITIVE.includes(mood)) streak++;
      else streak = 0;
      stageKey = stageKeyFromStreak(streak, stageKey, mood);
    });
    return stageKey;
  }

  /* ════════════════════════════════════════════════════
     PUBLIC API
  ════════════════════════════════════════════════════ */

  /**
   * Log a mood. Multiple calls per day are all stored.
   * Compatible with breathing-mt.js: logMood(moodString, noteString)
   */
  function logMood(mood, note) {
    const log   = load(MOOD_LOG_KEY, []);
    const today = todayStr();
    const ts    = new Date().toISOString();
    log.push({ date: today, mood, note: note || "", ts });
    save(MOOD_LOG_KEY, log);

    /* Track unique days */
    const days = new Set(load(DAYS_KEY, []));
    days.add(today);
    save(DAYS_KEY, [...days]);

    return { streak: calcStreak(log), stageKey: calcStageKey(log) };
  }

  /* Raw log array — used by breathing-mt.js and growth.js */
  function getMoodLog() {
    return load(MOOD_LOG_KEY, []);
  }

  function getStreak()    { return calcStreak(getMoodLog()); }
  function getStageKey()  { return calcStageKey(getMoodLog()); }
  function getStageInfo() { return STAGES[getStageKey()] || STAGES.egg; }

  /* Unique days the user has logged a mood */
  function getDaysTracked() {
    return new Set(load(DAYS_KEY, [])).size;
  }

  /* Latest mood string e.g. "joyful" */
  function getLatestMood() {
    const log = getMoodLog();
    if (!log.length) return null;
    return log.reduce((a, b) => (a.ts > b.ts ? a : b)).mood;
  }

  /* Latest full entry object */
  function getLatestEntry() {
    const log = getMoodLog();
    if (!log.length) return null;
    return log.reduce((a, b) => (a.ts > b.ts ? a : b));
  }

  /* Total mood logs for today (all check-ins, not just one) */
  function getTodayCheckInCount() {
    const today = todayStr();
    return getMoodLog().filter(e => e.date === today).length;
  }

  /* Human-readable mood label */
  function getMoodReflected() {
    const m = getLatestMood();
    return m ? (MOOD_META[m]?.label || m) : "—";
  }

  /* Health label = stage name */
  function getHealth() {
    return getStageInfo().label;
  }

  /* Message for home breathe card */
  function getButterflyMessage() {
    const msgs = {
      egg:         "Your journey is just beginning. 🥚",
      pupa:        "You're growing in your own quiet way. 🌱",
      caterpillar: "Every step forward matters. 🌿",
      butterfly:   "Your butterfly is glowing softly today. 🦋",
      surviving:   "It's okay to rest. You're still here. 💚",
      struggling:  "Hard days are part of the journey too. 🌧️",
    };
    return msgs[getStageKey()] || msgs.egg;
  }

  /* CSS class for image filter — profile.css uses butterfly-excelling etc. */
  function getButterflyClass() {
    const map = {
      egg:         "butterfly-struggling",
      pupa:        "butterfly-surviving",
      caterpillar: "butterfly-thriving",
      butterfly:   "butterfly-excelling",
      surviving:   "butterfly-surviving",
      struggling:  "butterfly-struggling",
    };
    return map[getStageKey()] || "butterfly-struggling";
  }

  /* Stage key string — used by profilebutterfly.js */
  function getButterflyStage() {
    return getStageKey();
  }

  /* Bond days = days tracked (backward compat for profilebutterfly.js) */
  function getBondDays()  { return getDaysTracked(); }
  function getBondLevel() {
    const n = getBondDays();
    if (n >= 14) return "High";
    if (n >= 7)  return "Medium";
    return "Low";
  }

  /* addBondDay — kept for breathing-mt.js compatibility */
  function addBondDay(date) {
    date = date || todayStr();
    const days = new Set(load(DAYS_KEY, []));
    days.add(date);
    save(DAYS_KEY, [...days]);
  }

  /* Growth history — one record per day (latest mood), newest first */
  function getGrowthHistory() {
    const log   = getMoodLog();
    const byDay = {};
    log.forEach(e => {
      if (!byDay[e.date] || e.ts > byDay[e.date].ts) byDay[e.date] = e;
    });
    return Object.entries(byDay)
      .sort(([a], [b]) => b.localeCompare(a))
      .map(([date, e]) => ({ date, mood: e.mood, note: e.note, ts: e.ts }));
  }

  const XP_KEY = "aire_xp";

  function addXP(amount) {
    const current = parseFloat(localStorage.getItem(XP_KEY) || "0");
    localStorage.setItem(XP_KEY, current + amount);
  }

  function getXP() {
    return parseFloat(localStorage.getItem(XP_KEY) || "0");
  }

  /* FOR HOME Mood Check In */
  function getStageFromPoints(points) {
    if (points < 10) return "egg";
    if (points < 30) return "pupa";
    if (points < 50) return "caterpillar";
    return "butterfly";
  }

  // function getCurrentStageByPoints() {
  //   return getStageFromPoints(getTotalPoints());
  // }

  // Task + Streak grow the butterfly
  // function getTotalPoints() {
  //   const today = new Date().toISOString().split("T")[0];

  //   const log = AireData.getMoodLog();
  //   const todayLogs = log.filter(e => e.date === today);

  //   let moodPoints = 0;

  //   // ✅ Mood logic (your idea)
  //   if (todayLogs.length > 0) {
  //     moodPoints += 1; // logged mood
  //   }

  //   if (todayLogs.some(e => AireData.POSITIVE.includes(e.mood))) {
  //     moodPoints += 1; // happy/joyful bonus
  //   }

  //   // ✅ Task logic (NEW)
  //   let taskPoints = 0;

  //   if (localStorage.getItem("bodybooster-achieved") === today) taskPoints += 0.5;
  //   if (localStorage.getItem("minitask-achieved") === today) taskPoints += 0.5;
  //   if (localStorage.getItem("mindreset-achieved") === today) taskPoints += 0.5;
  //   if (localStorage.getItem("moodlifting-achieved") === today) taskPoints += 0.5;
  //   if (localStorage.getItem("grounding-achieved") === today) taskPoints += 0.5;

  //   return moodPoints + taskPoints;
  // }

  /* Expose everything */
  return {
    logMood,
    getMoodLog,
    // getTotalPoints,
    // getCurrentStageByPoints,
    getStreak,
    getStageKey,
    getStageInfo,
    getDaysTracked,
    getLatestMood,
    getLatestEntry,
    getTodayCheckInCount,
    getMoodReflected,
    getHealth,
    getButterflyMessage,
    getButterflyClass,
    getButterflyStage,
    getGrowthHistory,
    addBondDay,
    getBondDays,
    getBondLevel,
    addXP,
    getXP,
    POSITIVE,
    NEGATIVE,
    MOOD_META,
    STAGES,
    today: () => todayStr(),
  };

})();
