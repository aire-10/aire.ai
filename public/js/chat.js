document.addEventListener("DOMContentLoaded", async () => {

  function generateId() {
    return 'id_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }

  // ============================================================
  // DOM
  // ============================================================
  const chatMessages = document.getElementById("chatMessages");
  const chatForm = document.querySelector(".chat-inputbar");
  const chatInput = document.getElementById("chatInput");

  if (!chatMessages || !chatForm || !chatInput) return;

  // ============================================================
  // SESSION (FROM URL ONLY)
  // ============================================================
  const urlParams = new URLSearchParams(window.location.search);
  let activeId = urlParams.get('id');

  // If no session → create one
  if (!activeId) {
    const res = await fetch('/history/session', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    const data = await res.json();

    activeId = data.id;
    window.location.href = `/chat?id=${activeId}`;
    return;
  }

  // ============================================================
  // STATE
  // ============================================================
  let messages = [];
  let lastRenderedDay = null;

  // ============================================================
  // LOAD CHAT FROM BACKEND
  // ============================================================
  async function loadChatFromServer(sessionId) {
    try {
      const res = await fetch(`/history/session/${sessionId}`);

      if (!res.ok) {
        console.warn("Session not found, starting fresh");
        messages = [];
        renderAll();
        return;
      }

      const data = await res.json();

      messages = (data.messages || []).map(m => ({
        role: m.isUser ? 'user' : 'bot',
        text: m.isUser ? m.text : m.response,
        ts: m.timestamp
      }));

      renderAll();

    } catch (error) {
      console.error("Load failed:", error);
    }
  }



  // ============================================================
  // UTILITIES
  // ============================================================

  const pad2 = (n) => String(n).padStart(2, "0");

  const formatTime = (ts) => {
    const d = new Date(ts);
    return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
  };

  const dayKey = (ts) => {
    const d = new Date(ts);
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
  };

  const dayLabel = (ts) => {
    const d = new Date(ts);
    const now = new Date();

    if (d.toDateString() === now.toDateString()) return "Today";

    const yesterday = new Date();
    yesterday.setDate(now.getDate() - 1);

    if (d.toDateString() === yesterday.toDateString()) return "Yesterday";

    return d.toLocaleDateString();
  };

  await loadChatFromServer(activeId);
  // ============================================================
  // RENDER
  // ============================================================
  function renderDayDividerIfNeeded(ts) {
    const dk = dayKey(ts);
    if (dk === lastRenderedDay) return;

    lastRenderedDay = dk;

    const div = document.createElement("div");
    div.className = "day-divider";
    div.textContent = dayLabel(ts);
    chatMessages.appendChild(div);
  }

  function renderMessage(msg) {
    renderDayDividerIfNeeded(msg.ts);

    const row = document.createElement("div");
    row.className = `msg ${msg.role}`;

    const bubble = document.createElement("div");
    bubble.className = "msg-bubble";

    if (msg.role === "bot") {
      const title = document.createElement("div");
      title.className = "msg-title";
      title.textContent = "AI Bot: Airé 💚";
      bubble.appendChild(title);
    }

    const text = document.createElement("div");
    text.className = "msg-text";
    text.textContent = msg.text;

    const time = document.createElement("div");
    time.className = "msg-time";
    time.textContent = formatTime(msg.ts);

    bubble.appendChild(text);
    bubble.appendChild(time);
    row.appendChild(bubble);
    chatMessages.appendChild(row);
  }

  function renderAll() {
    chatMessages.innerHTML = "";
    lastRenderedDay = null;

    if (!messages.length) {
      renderMessage({
        role: "bot",
        text: "✨ Hi, I'm Airé! I'm here to listen and support you 💚",
        ts: Date.now()
      });
      return;
    }

    messages.forEach(renderMessage);
    scrollToBottom();
  }

  // ============================================================
  // SEND MESSAGE (BACKEND)
  // ============================================================
  async function sendUserText(text) {
    const trimmed = text.trim();
    if (!trimmed) return;

    addMessage("user", trimmed);

    showTyping();

    try {
      const res = await fetch('/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          message: trimmed,
          session_id: activeId
        })
      });

      const data = await res.json();

      hideTyping();
      addMessage("bot", data.reply);

    } catch (err) {
      hideTyping();
      addMessage("bot", "🌸 Something went wrong. Try again.");
    }
  }

  function addMessage(role, text) {
    const msg = { role, text, ts: Date.now() };
    messages.push(msg);
    renderMessage(msg);
    scrollToBottom();
  }

  // ============================================================
  // TYPING
  // ============================================================
  let typingEl = null;

  function showTyping() {
    typingEl = document.createElement("div");
    typingEl.className = "msg bot typing";
    typingEl.innerHTML = `<div class="msg-bubble">Typing...</div>`;
    chatMessages.appendChild(typingEl);
  }

  function hideTyping() {
    if (typingEl) typingEl.remove();
    typingEl = null;
  }

  // ============================================================
  // SCROLL
  // ============================================================
  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // ============================================================
  // EVENTS
  // ============================================================
  chatForm.addEventListener("submit", (e) => {
    e.preventDefault();
    sendUserText(chatInput.value);
    chatInput.value = "";
  });

  chatInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      sendUserText(chatInput.value);
      chatInput.value = "";
    }
  });

  // ============================================================
  // NEW CHAT
  // ============================================================
  const newChatBtn = document.getElementById("newChatBtn");
  if (newChatBtn) {
    newChatBtn.addEventListener("click", () => {
      const newId = generateId();
      window.location.href = `/chat?id=${newId}`;
    });
  }

  // ============================================================
  // CLEAR CHAT (DATABASE)
  // ============================================================
  const clearChatBtn = document.getElementById("clearChatBtn");

  if (clearChatBtn) {
    clearChatBtn.addEventListener("click", async () => {
      if (!confirm("Clear chat?")) return;

      await fetch(`/history/session/${activeId}`, {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        }
      });

      messages = [];
      renderAll();
    });
  }

  console.log("✅ Chat ready (DB mode)");

  // ============================================================
  // MOOD AND SUGGESTION BUTTONS
  // ============================================================

  document.querySelectorAll('.suggest-item').forEach(btn => {
    btn.addEventListener('click', () => {
      sendUserText(btn.dataset.fill);
    });
  });

  document.querySelectorAll('.mood-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const mood = btn.dataset.mood;

      const moodMessages = {
        happy: "I'm feeling happy",
        good: "I'm feeling good",
        neutral: "I'm feeling neutral",
        sad: "I'm feeling sad",
        tired: "I'm feeling tired"
      };

      sendUserText(moodMessages[mood] || "I'm feeling okay");
    });
  });

});