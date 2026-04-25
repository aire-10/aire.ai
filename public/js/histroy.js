document.addEventListener("DOMContentLoaded", () => {

  const PAGE_SIZE = 10;
  let currentPage = 1;

  const listEl = document.getElementById("historyList");
  const prevBtn = document.getElementById("prevPage");
  const nextBtn = document.getElementById("nextPage");
  const pageNumbersEl = document.getElementById("pageNumbers");
  const searchEl = document.getElementById("historySearch");
  const sessionCountEl = document.getElementById("sessionCount");

  let query = "";
  let selectedMood = "any";
  let selectedTime = "all";

  function fmt(ts) {
    if (!ts) return "";
    return new Date(ts).toLocaleString();
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

  async function fetchSessions() {
    const res = await fetch('/history/sessions');
    const data = await res.json();
    return data.sessions || [];
  }

  function applyFilters(items) {
    let result = items;

    // SEARCH
    if (query) {
      const q = query.toLowerCase();
      result = result.filter(s =>
        (s.title || "").toLowerCase().includes(q) ||
        (s.preview || "").toLowerCase().includes(q)
      );
    }

    // MOOD FILTER
    if (selectedMood !== "any") {
      result = result.filter(s =>
        (s.mood || "").toLowerCase() === selectedMood
      );
    }

    // TIME FILTER
    if (selectedTime !== "all") {
      const now = new Date();

      result = result.filter(s => {
        const date = new Date(s.updatedAt);

        if (selectedTime === "this_week") {
          const weekAgo = new Date();
          weekAgo.setDate(now.getDate() - 7);
          return date >= weekAgo;
        }

        // MONTH FILTER
        const monthIndex = [
          "january","february","march","april","may","june",
          "july","august","september","october","november","december"
        ].indexOf(selectedTime);

        return date.getMonth() === monthIndex;
      });
    }

    return result;
  }

  function openSession(id) {
    window.location.href = `/chat?id=${id}`;
  }

  async function deleteSession(id) {
    await fetch(`/history/session/${id}`, {
      method: "DELETE",
      headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
      }
    });
  }

  async function render() {
    if (!listEl) return;

    try {
      const sessions = await fetchSessions();

      if (sessionCountEl) {
        sessionCountEl.textContent = `(${sessions.length} session${sessions.length !== 1 ? "s" : ""})`;
      }

      const filtered = applyFilters(sessions);

      const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
      if (currentPage > totalPages) currentPage = totalPages;

      const start = (currentPage - 1) * PAGE_SIZE;
      const paginated = filtered.slice(start, start + PAGE_SIZE);

      if (!filtered.length) {
        listEl.innerHTML = `<p class="history-empty">No chats found.</p>`;
        return;
      }

      listEl.innerHTML = paginated.map(s => `
        <div class="history-row" data-id="${s.id}">
          <div class="history-row-main">
            <div class="history-title">${escapeHtml(s.title || "New Chat")}</div>
            <div class="history-preview">${escapeHtml(s.preview || "No messages yet")}</div>
            <div class="history-date">${fmt(s.updatedAt)}</div>
          </div>
            <div class="history-row-actions">
              <span class="history-open">›</span>

              <button class="history-kebab">⋮</button>

              <div class="history-menu">
                <button class="rename-btn">Rename</button>
                <button class="delete-btn danger">Delete</button>
              </div>
            </div>
        </div>
      `).join("");

      // Pagination
      if (prevBtn) prevBtn.disabled = currentPage <= 1;
      if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

      if (pageNumbersEl) {
        pageNumbersEl.innerHTML = "";
        for (let i = 1; i <= totalPages; i++) {
          const btn = document.createElement("button");
          btn.textContent = i;
          btn.className = i === currentPage ? "num-active" : "";
          btn.onclick = () => {
            currentPage = i;
            render();
          };
          pageNumbersEl.appendChild(btn);
        }
      }

    } catch (err) {
      console.error(err);
      listEl.innerHTML = `<p class="history-empty">Failed to load chats.</p>`;
    }
  }

  // CLICK HANDLING
  listEl?.addEventListener("click", async (e) => {

    // =========================
    // TOGGLE DROPDOWN
    // =========================
    if (e.target.classList.contains("history-kebab")) {
      const row = e.target.closest(".history-row");
      const menu = row.querySelector(".history-menu");

      document.querySelectorAll(".history-menu").forEach(m => {
        if (m !== menu) m.classList.remove("is-open");
      });

      menu.classList.toggle("is-open");
      return;
    }

    // =========================
    // RENAME
    // =========================
    if (e.target.classList.contains("rename-btn")) {
      const row = e.target.closest(".history-row");
      const id = row.dataset.id;

      const newName = prompt("Enter new name:");
      if (!newName) return;

      await fetch(`/history/session/${id}/rename`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ title: newName })
      });

      render();
      return;
    }

    // =========================
    // DELETE
    // =========================
    if (e.target.classList.contains("delete-btn")) {
      const row = e.target.closest(".history-row");
      const id = row.dataset.id;

      const confirmDelete = confirm("Delete this chat?");
      if (!confirmDelete) return;

      await deleteSession(id);
      render();
      return;
    }

    // =========================
    // OPEN CHAT
    // =========================
    const row = e.target.closest(".history-row");
    if (!row) return;

    const id = row.dataset.id;
    openSession(id);
  });

  // Search
  searchEl?.addEventListener("input", () => {
    query = searchEl.value;
    currentPage = 1;
    render();
  });

  prevBtn?.addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      render();
    }
  });

  nextBtn?.addEventListener("click", () => {
    currentPage++;
    render();
  });

  render();

  document.addEventListener("click", (e) => {
    document.querySelectorAll(".history-menu").forEach(m => {
      if (!m.contains(e.target)) {
        m.classList.remove("is-open");
      }
    });
  });

// =========================
// PILL DROPDOWN (FILTERS)
// =========================
  document.querySelectorAll(".pill-dd > button").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();

      const wrapper = btn.parentElement;

      // close others
      document.querySelectorAll(".pill-dd").forEach(w => {
        if (w !== wrapper) w.classList.remove("is-open");
      });

      // toggle current
      wrapper.classList.toggle("is-open");
    });
  });

  document.addEventListener("click", () => {
    document.querySelectorAll(".pill-dd").forEach(w => {
      w.classList.remove("is-open");
    });
  });

  // =========================
  // FILTER CLICK HANDLING
  // =========================
  document.querySelectorAll(".pill-item").forEach(item => {
    item.addEventListener("click", () => {

      const wrapper = item.closest(".pill-dd");
      const type = wrapper.dataset.dd;
      const value = item.dataset.value;

      if (type === "mood") {
        selectedMood = value;
      }

      if (type === "time") {
        selectedTime = value;
      }

      // update label text
      const label = wrapper.querySelector(".pill-label");
      label.textContent = item.textContent;

      wrapper.classList.remove("is-open");

      render(); // 🔥 IMPORTANT
    });
  });

  document.querySelector('[data-filter="all"]')?.addEventListener("click", () => {
    selectedMood = "any";
    selectedTime = "all";

    document.getElementById("moodLabel").textContent = "Mood: Any";
    document.getElementById("timeLabel").textContent = "All";

    render();
  });

});