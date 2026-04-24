document.addEventListener("DOMContentLoaded", () => {
  // MUST match chat.js
  const STORAGE_SESSIONS = "AIRE_CHAT_SESSIONS";
  const STORAGE_ACTIVE = "AIRE_CHAT_ACTIVE_ID";
  const sessionKey = (id) => `AIRE_CHAT_SESSION_${id}`;

  const PAGE_SIZE = 10;
  let currentPage = 1;

  const listEl = document.getElementById("historyList");
  const prevBtn = document.getElementById("prevPage");
  const nextBtn = document.getElementById("nextPage");
  const pageNumbersEl = document.getElementById("pageNumbers");
  const searchEl = document.getElementById("historySearch");
  const sessionCountEl = document.getElementById("sessionCount");

  // dropdown pills (optional)
  const allBtn = document.querySelector('.filter-pill[data-filter="all"]');
  const timeDD = document.querySelector('.pill-dd[data-dd="time"]');
  const moodDD = document.querySelector('.pill-dd[data-dd="mood"]');

  const timeBtn = timeDD?.querySelector(".filter-pill");
  const moodBtn = moodDD?.querySelector(".filter-pill");
  const timeMenu = timeDD?.querySelector(".pill-menu");
  const moodMenu = moodDD?.querySelector(".pill-menu");
  const timeLabel = timeDD?.querySelector(".pill-label");
  const moodLabel = moodDD?.querySelector(".pill-label");

  let query = "";
  let timeFilter = "this_week";
  let moodFilter = "any";

  function loadSessions() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_SESSIONS) || "[]");
    } catch {
      return [];
    }
  }

  function saveSessions(sessions) {
    localStorage.setItem(STORAGE_SESSIONS, JSON.stringify(sessions));
  }

  function loadMessages(id) {
    try {
      return JSON.parse(localStorage.getItem(sessionKey(id)) || "[]");
    } catch {
      return [];
    }
  }

  function deleteSessionById(id) {
    const sessions = loadSessions().filter((s) => s.id !== id);
    saveSessions(sessions);
    localStorage.removeItem(sessionKey(id));

    const active = localStorage.getItem(STORAGE_ACTIVE);
    if (active === id) localStorage.removeItem(STORAGE_ACTIVE);
  }

  function fmt(ts) {
    if (!ts) return "";
    return new Date(ts).toLocaleString([], {
      year: "2-digit",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, (m) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    }[m]));
  }

  function previewFromMessages(msgs) {
    const last = msgs[msgs.length - 1];
    if (!last || !last.text) return "No messages yet…";
    const t = String(last.text).trim().replace(/\s+/g, " ");
    return t.length > 60 ? t.slice(0, 59) + "…" : t;
  }

  function detectMood(msgs) {
    const text = msgs.map((m) => (m.text || "").toLowerCase()).join(" ");
    if (text.includes("anxious")) return "anxious";
    if (text.includes("happy")) return "happy";
    if (text.includes("sad")) return "sad";
    if (text.includes("stressed") || text.includes("stress")) return "stressed";
    if (text.includes("tired")) return "tired";
    if (text.includes("calm")) return "calm";
    return "any";
  }

  function inThisWeek(ts) {
    if (!ts) return false;
    return Date.now() - ts <= 7 * 24 * 60 * 60 * 1000;
  }

  function inMonth(ts, monthIndex) {
    if (!ts) return false;
    return new Date(ts).getMonth() === monthIndex;
  }

  function monthValueToIndex(v) {
    const map = {
      january: 0, february: 1, march: 2, april: 3, may: 4, june: 5,
      july: 6, august: 7, september: 8, october: 9, november: 10, december: 11,
    };
    return map[v];
  }

  function applyFilters(items) {
    const q = query.trim().toLowerCase();

    return items.filter((it) => {
      // search
      if (q) {
        const inTitle = (it.title || "").toLowerCase().includes(q);
        const inPrev = (it.preview || "").toLowerCase().includes(q);
        if (!inTitle && !inPrev) return false;
      }

      // time
      if (timeFilter === "this_week") {
        if (!inThisWeek(it.updatedAt)) return false;
      } else {
        const idx = monthValueToIndex(timeFilter);
        if (idx != null && !inMonth(it.updatedAt, idx)) return false;
      }

      // mood
      if (moodFilter !== "any" && it.mood !== moodFilter) return false;

      return true;
    });
  }

  function openSession(id) {
    localStorage.setItem(STORAGE_ACTIVE, id);
    window.location.href = `chat.html?id=${encodeURIComponent(id)}`;
  }

  function closeMenus() {
    listEl?.querySelectorAll(".history-menu.is-open")
      .forEach((m) => m.classList.remove("is-open"));
  }

  function render() {
    if (!listEl) return;

    const sessions = loadSessions();

    if(sessionCountEl){
      const count = sessions.length;
      sessionCountEl.textContent = `(${count} session${count !== 1 ? "s" : ""})`;
    }
    
    const enriched = sessions.map((s) => {
      const msgs = loadMessages(s.id);
      return {
        ...s,
        updatedAt: s.updatedAt || Date.now(),
        preview: previewFromMessages(msgs),
        mood: detectMood(msgs),
      };
    }).sort((a, b) => (b.updatedAt || 0) - (a.updatedAt || 0));

    const filtered = applyFilters(enriched);
    const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * PAGE_SIZE;
    const paginated = filtered.slice(start, start + PAGE_SIZE);

    if (filtered.length === 0) {
      listEl.innerHTML = `<p class="history-empty">No chats found.</p>`;
      pageNumbersEl && (pageNumbersEl.innerHTML = "");
      prevBtn && (prevBtn.disabled = true);
      nextBtn && (nextBtn.disabled = true);
      return;
    }

    listEl.innerHTML = paginated.map((s) => {
      const moodText = s.mood === "any" ? "" : s.mood[0].toUpperCase() + s.mood.slice(1);
      const badge = s.mood === "any" ? "" : `<span class="history-tag tag-${s.mood}">${escapeHtml(moodText)}</span>`;

      return `
        <div class="history-row" role="button" tabindex="0" data-id="${s.id}">
          <div class="history-row-main">
            <div class="history-title">${escapeHtml(s.title || "New Chat")}</div>
            <div class="history-preview">${escapeHtml(s.preview)}</div>
            <div class="history-date">${fmt(s.updatedAt)}</div>
          </div>

          ${badge}

          <div class="history-row-actions">
            <span class="history-open">›</span>

            <button class="history-kebab" type="button" aria-label="More actions" data-action="menu">⋮</button>

            <div class="history-menu" data-menu>
              <button type="button" data-action="rename">Rename</button>
              <button type="button" class="danger" data-action="delete">Delete</button>
            </div>
          </div>
        </div>
      `;
    }).join("");

    // pagination
    if (prevBtn) prevBtn.disabled = currentPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

    if (pageNumbersEl) {
      pageNumbersEl.innerHTML = "";
      for (let i = 1; i <= totalPages; i++) {
        const b = document.createElement("button");
        b.textContent = i;
        b.className = "num-btn" + (i === currentPage ? " num-active" : "");
        b.addEventListener("click", () => { currentPage = i; render(); });
        pageNumbersEl.appendChild(b);
      }
    }
  }

  // clicks
  listEl?.addEventListener("click", (e) => {
    const row = e.target.closest(".history-row");
    if (!row) return;

    const id = row.dataset.id;

    const actionBtn = e.target.closest("[data-action]");
    if (actionBtn) {
      const action = actionBtn.dataset.action;

      if (action === "menu") {
        e.stopPropagation();
        const menu = row.querySelector("[data-menu]");
        const isOpen = menu.classList.contains("is-open");
        closeMenus();
        if (!isOpen) menu.classList.add("is-open");
        return;
      }

      if (action === "rename") {
        e.stopPropagation();
        closeMenus();

        const sessions = loadSessions();
        const s = sessions.find((x) => x.id === id);
        if (!s) return;

        const next = prompt("Rename chat:", s.title || "New Chat");
        if (!next) return;

        s.title = next.trim();
        s.updatedAt = Date.now();
        saveSessions(sessions);
        render();
        return;
      }

      if (action === "delete") {
        e.stopPropagation();
        closeMenus();

        const ok = confirm("Delete this chat session? This cannot be undone.");
        if (!ok) return;

        deleteSessionById(id);
        render();
        return;
      }
    }

    // normal click opens
    openSession(id);
  });

  // enter key opens
  listEl?.addEventListener("keydown", (e) => {
    if (e.key !== "Enter") return;
    const row = e.target.closest(".history-row");
    if (!row) return;
    openSession(row.dataset.id);
  });

  // close menus on outside click
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".history-row-actions")) closeMenus();
  });

  // prev/next
  prevBtn?.addEventListener("click", () => {
    if (currentPage > 1) { currentPage--; render(); }
  });
  nextBtn?.addEventListener("click", () => {
    currentPage++; render();
  });

  // search
  searchEl?.addEventListener("input", () => {
    query = searchEl.value;
    currentPage = 1;
    render();
  });

  // dropdowns
  const closeAll = () => {
    timeDD?.classList.remove("is-open");
    moodDD?.classList.remove("is-open");
    timeBtn?.setAttribute("aria-expanded", "false");
    moodBtn?.setAttribute("aria-expanded", "false");
  };

  const toggleDD = (dd, btn) => {
    const isOpen = dd.classList.contains("is-open");
    closeAll();
    if (!isOpen) {
      dd.classList.add("is-open");
      btn.setAttribute("aria-expanded", "true");
    }
  };

  timeBtn?.addEventListener("click", (e) => { e.stopPropagation(); toggleDD(timeDD, timeBtn); });
  moodBtn?.addEventListener("click", (e) => { e.stopPropagation(); toggleDD(moodDD, moodBtn); });
  document.addEventListener("click", closeAll);

  timeMenu?.querySelectorAll(".pill-item").forEach((item) => {
    item.addEventListener("click", (e) => {
      e.stopPropagation();
      timeFilter = item.dataset.value || "this_week";
      if (timeLabel) timeLabel.textContent = item.textContent.trim();
      allBtn?.classList.remove("is-active");
      currentPage = 1;
      closeAll();
      render();
    });
  });

  moodMenu?.querySelectorAll(".pill-item").forEach((item) => {
    item.addEventListener("click", (e) => {
      e.stopPropagation();
      moodFilter = item.dataset.value || "any";
      const txt = item.textContent.trim();
      if (moodLabel) moodLabel.textContent = (moodFilter === "any") ? "Mood: Any" : `Mood: ${txt}`;
      allBtn?.classList.remove("is-active");
      currentPage = 1;
      closeAll();
      render();
    });
  });

  allBtn?.addEventListener("click", () => {
    allBtn.classList.add("is-active");
    timeFilter = "this_week";
    moodFilter = "any";
    if (timeLabel) timeLabel.textContent = "This week";
    if (moodLabel) moodLabel.textContent = "Mood: Any";
    currentPage = 1;
    render();
  });

  render();
});