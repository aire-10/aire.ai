async function renderRecentChats(limit = 3) {
  const listEl = document.getElementById("recentChatList");
  if (!listEl) return;

  try {
    const res = await fetch('/history/sessions');
    const data = await res.json();

    const sessions = data.sessions || [];

    listEl.innerHTML = "";

    if (!sessions.length) {
      listEl.innerHTML = `<div class="recent-empty">No chats yet.</div>`;
      return;
    }

    sessions.slice(0, limit).forEach((s) => {
      const title = s.title || "New Chat";
      const preview = s.preview || s.last_message || "No messages yet.";
      const when = new Date(s.updatedAt).toLocaleString();

      const item = document.createElement("button");
      item.className = "recent-chat-item";

      item.innerHTML = `
        <div class="recent-title">${title}</div>
        <div class="recent-preview">${preview}</div>
        <div class="recent-date">${when}</div>
      `;

      item.addEventListener("click", () => {
        window.location.href = `/chat?id=${s.id}`;
      });

      listEl.appendChild(item);
    });

  } catch (err) {
    console.error(err);
    listEl.innerHTML = `<div class="recent-empty">Failed to load chats.</div>`;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  renderRecentChats(3);
});