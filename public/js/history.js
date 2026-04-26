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
    try {
      const res = await fetch('/history/sessions');
      if (!res.ok) throw new Error('Failed to fetch');
      const data = await res.json();
      return data.sessions || [];
    } catch (err) {
      console.error('Fetch error:', err);
      return [];
    }
  }

  function applyFilters(items) {
    let result = items;

    if (query) {
      const q = query.toLowerCase();
      result = result.filter(s =>
        (s.title || "").toLowerCase().includes(q) ||
        (s.preview || "").toLowerCase().includes(q)
      );
    }

    if (selectedMood !== "any") {
      result = result.filter(s =>
        (s.mood || "").toLowerCase() === selectedMood
      );
    }

    if (selectedTime !== "all") {
      const now = new Date();

      result = result.filter(s => {
        const date = new Date(s.updatedAt);

        if (selectedTime === "this_week") {
          const weekAgo = new Date();
          weekAgo.setDate(now.getDate() - 7);
          return date >= weekAgo;
        }

        const monthIndex = [
          "january", "february", "march", "april", "may", "june",
          "july", "august", "september", "october", "november", "december"
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

  async function renameSession(id, newName) {
    await fetch(`/history/session/${id}/rename`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ title: newName })
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

      // NEW: Visible buttons layout
      listEl.innerHTML = paginated.map(s => `
        <div class="history-row" data-id="${s.id}">
          <div class="history-row-main" onclick="openSession('${s.id}')">
            <div class="history-title">${escapeHtml(s.title || "New Chat")}</div>
            <div class="history-preview">${escapeHtml(s.preview || "No messages yet")}</div>
            <div class="history-date">${fmt(s.updatedAt)}</div>
          </div>
          <div class="history-row-buttons">
            <button class="rename-chat-btn" data-id="${s.id}" data-title="${escapeHtml(s.title || "New Chat")}">Rename</button>
            <button class="delete-chat-btn danger" data-id="${s.id}">Delete</button>
          </div>
        </div>
      `).join("");

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

  // Event delegation for buttons
  listEl?.addEventListener("click", async (e) => {
    // Rename button
    if (e.target.classList.contains("rename-chat-btn")) {
      e.stopPropagation();
      const id = e.target.dataset.id;
      const currentTitle = e.target.dataset.title;
      const newName = prompt("Enter new name:", currentTitle);
      if (!newName || newName === currentTitle) return;

      await renameSession(id, newName);
      render();
      return;
    }

    // Delete button
    if (e.target.classList.contains("delete-chat-btn")) {
      e.stopPropagation();
      const id = e.target.dataset.id;
      if (!confirm("Delete this chat?")) return;

      await deleteSession(id);
      render();
      return;
    }
  });

  // Make openSession available globally
  window.openSession = openSession;

  // Search
  searchEl?.addEventListener("input", () => {
    query = searchEl.value;
    currentPage = 1;
    render();
  });

  // Pagination
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

  // Filter dropdowns
  document.querySelectorAll(".pill-dd > button").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const wrapper = btn.parentElement;
      document.querySelectorAll(".pill-dd").forEach(w => {
        if (w !== wrapper) w.classList.remove("is-open");
      });
      wrapper.classList.toggle("is-open");
    });
  });

  document.addEventListener("click", () => {
    document.querySelectorAll(".pill-dd").forEach(w => {
      w.classList.remove("is-open");
    });
  });

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

      const label = wrapper.querySelector(".pill-label");
      label.textContent = item.textContent;

      wrapper.classList.remove("is-open");
      render();
    });
  });

  document.querySelector('[data-filter="all"]')?.addEventListener("click", () => {
    selectedMood = "any";
    selectedTime = "all";
    document.getElementById("moodLabel").textContent = "Mood: Any";
    document.getElementById("timeLabel").textContent = "All";
    render();
  });

  // Initial render
  render();

});