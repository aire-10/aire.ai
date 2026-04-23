function initProgress(config) {
  const {
    selector,
    storageKey,
    activeClass = "done"
  } = config;

  const items = document.querySelectorAll(selector);
  if (!items.length) return;

  // LOAD saved progress
  const saved = JSON.parse(localStorage.getItem(storageKey)) || [];

  items.forEach((item, index) => {

    // ✅ RESTORE COMPLETED STATE
    if (saved.includes(index)) {
      item.classList.add(activeClass);

      // 🔥 Restore button UI
      const btn = item.querySelector(".task-start-btn");
      if (btn) {
        btn.textContent = "Completed ✓";
        btn.className = "btn btn-completed";
        btn.disabled = true;
      }

      // Optional: update emoji/check
      // Mini-tasks
      const leaf = item.querySelector(".task-leaf");
      if (leaf) leaf.textContent = "✓";

      // Body booster
      const circle = item.querySelector(".bb-check-circle");
      if (circle) {
      circle.classList.add("done");
      circle.textContent = "✓";
    }
    }

    // CLICK TO TOGGLE (optional)
    item.addEventListener("click", (e) => {
      if (e.target.closest("button")) return;

      item.classList.toggle(activeClass);
      save();
    });
  });

  function save() {
    const completed = [];

    items.forEach((item, index) => {
      if (item.classList.contains(activeClass)) {
        completed.push(index);
      }
    });

    localStorage.setItem(storageKey, JSON.stringify(completed));
  }
}

function checkDailyReset(keys = []) {
  const today = new Date().toDateString(); // e.g. "Wed Mar 18 2026"
  const lastDate = localStorage.getItem("last-visit-date");

  // If it's a new day → reset everything
  if (lastDate !== today) {

    // Clear all progress keys
    keys.forEach(key => {
      localStorage.removeItem(key);
    });

    // Update stored date
    localStorage.setItem("last-visit-date", today);
  }
}

function scheduleMidnightReset(keys) {
  const now = new Date();
  const midnight = new Date();

  midnight.setHours(24, 0, 0, 0); // next midnight

  const timeUntilMidnight = midnight - now;

  setTimeout(() => {
    keys.forEach(key => localStorage.removeItem(key));
    location.reload(); // refresh page
  }, timeUntilMidnight);
}