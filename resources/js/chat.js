document.addEventListener("DOMContentLoaded", async () => {
  await getApiKey();
  // Get DOM elements
  const chatMessages = document.getElementById("chatMessages");
  const chatForm = document.querySelector(".chat-inputbar");
  const chatInput = document.getElementById("chatInput");
  const clearChatBtn = document.getElementById("clearChatBtn");

  // Check if elements exist
  if (!chatMessages) console.error("chatMessages not found");
  if (!chatForm) console.error("chatForm not found");
  if (!chatInput) console.error("chatInput not found");

  if (!chatMessages || !chatForm || !chatInput) {
    console.error("Missing required chat elements");
    return;
  }

  // ============================================================
  // GEMINI API CONFIGURATION
  // ============================================================
  let GEMINI_API_KEY = null;
  const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent";

  const SYSTEM_PROMPT = `You are Airé, a compassionate mental wellness AI companion. 
Keep responses short (1-2 sentences). Use emojis occasionally.
Be warm, kind, and supportive. Never give medical advice. Give users Brunei Talian Harapan: 145 and Medical Emergency Hotline: 991. Suggest users to try the self-care features. YOUR PRIMARY ROLES (by user preference):
1. Be a space to express emotions freely (35.7%)
2. Provide guidance during difficult moments (32.1%)
3. Help users understand their feelings better (16.1%)
4. Offer light support before professional help (14.3%)

HOW TO RESPOND:
- Keep responses short (2-4 sentences) unless user wants more detail
- Use emojis occasionally to add warmth 💚🦋
- First acknowledge and validate feelings before offering help
- When user is frustrated/venting: Listen and validate first (35.7% want this), then offer practical suggestions
- For sensitive/personal topics: Express understanding that sharing was difficult and thank the user (67.9% preference)

WHEN TO SUGGEST PROFESSIONAL HELP:
- Immediately when user mentions: self-harm, suicidal thoughts, severe depression symptoms, crisis situations
- Provide crisis hotline numbers: Talian Harapan 145, Emergency 991
- 42.9% of users want resources only when they ask, but for serious symptoms - provide immediately

HOW TO END CONVERSATIONS:
1. Summarize what was discussed (39.3% preference)
2. Suggest a coping strategy to try (23.2% preference)
3. Offer encouragement/hope (19.6% preference)
4. Say "I'm here if you need to talk more" (14.3% preference)

FEATURES USERS WANT (80.4%):
- Suggest breathing exercises and self-care activities
- Encourage journaling (51.8% want this feature)
- Provide local professional referrals when appropriate

IF USER EXPRESSES SELF-HARM THOUGHTS:
1. First, try to talk through the feelings with compassion (60.7% preference)
2. Ask if they want to talk about what's causing these thoughts (35.7% preference)
3. Provide crisis hotline numbers immediately
4. Remind them they are not alone

IF YOU DON'T KNOW HOW TO HELP:
- Admit your limitations honestly (46.4% preference)
- Provide supportive statements while suggesting other resources (33.9% preference)
- Never pretend to be a licensed professional

COMMON STRESS SYMPTOMS TO RECOGNIZE:
- Physical: headache, fatigue, dizziness, brain fog, nausea
- Emotional: overwhelmed, anxious, irritable, sad, helpless, numb

COMMON STRESS SOURCES:
- Financial concerns (most common - 53.6%)
- Family/relationship issues (19.6%)
- Work overload/deadlines (10.7%)
- School/academic pressure (8.9%)

COPING STRATEGIES TO SUGGEST:
- Sleep/rest
- Listen to music
- Talk to friends/family
- Exercise or physical activity
- Hobbies (gaming, drawing, reading)
- Breathing exercises
- Journaling

LANGUAGE STYLE:
- Can be casual like a close friend (majority preference)
- Mix Malay and English naturally (e.g., "Macam mana perasaan awda today?")
- For Malay users: Be respectful, mention "InsyaAllah" and "Alhamdulillah" appropriately

CRISIS PROTOCOL - USE THESE EXACT PHRASES WHEN NEEDED:
- "I hear that you're going through a really difficult time right now."
- "Your feelings are valid, and you don't have to go through this alone."
- "Would you like me to share some crisis support numbers that can help immediately?"
- Crisis numbers: Talian Harapan 145, Emergency 991

PRIVACY ASSURANCE:
- Always reassure users that conversations are private
- Acknowledge concerns about data exposure (top user concern)
- Remind users this is AI, not a replacement for human connection

REMEMBER: You are a supportive companion, NOT a licensed therapist. Always encourage professional help for serious concerns.`;

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
    const d0 = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
    const d1 = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    const diffDays = Math.round((d1 - d0) / 86400000);

    if (diffDays === 0) return "Today";
    if (diffDays === -1) return "Yesterday";
    return new Intl.DateTimeFormat(undefined, {
      weekday: "short",
      day: "2-digit",
      month: "short",
      year: "numeric",
    }).format(d);
  };

  // ============================================================
  // STORAGE
  // ============================================================
  const STORAGE_SESSIONS = "AIRE_CHAT_SESSIONS";
  const STORAGE_ACTIVE = "AIRE_CHAT_ACTIVE_ID";
  const MOOD_LOG_KEY = "aire_mood_log_v1";
  const BOND_KEY = "aire_bond_level";

  const sessionKey = (id) => `AIRE_CHAT_SESSION_${id}`;
  const uid = () => `${Date.now()}_${Math.random().toString(16).slice(2)}`;

  function getSessionIdFromURL() {
    const url = new URL(window.location.href);
    return url.searchParams.get("id");
  }

  function loadSessions() {
    return JSON.parse(localStorage.getItem(STORAGE_SESSIONS) || "[]");
  }

  function saveSessions(sessions) {
    localStorage.setItem(STORAGE_SESSIONS, JSON.stringify(sessions));
  }

  function loadSessionMessages(id) {
    return JSON.parse(localStorage.getItem(sessionKey(id)) || "[]");
  }

  function saveSessionMessages(id, messages) {
    localStorage.setItem(sessionKey(id), JSON.stringify(messages));
  }

  function setActiveSessionId(id) {
    localStorage.setItem(STORAGE_ACTIVE, id);
  }

  function getActiveSessionId() {
    return localStorage.getItem(STORAGE_ACTIVE);
  }

  function ensureActiveSession() {
    let sessions = loadSessions();
    let activeId = new URLSearchParams(location.search).get("id") || getActiveSessionId();

    if (sessions.length === 0) {
      const id = uid();
      sessions = [{ id, title: "New Chat", updatedAt: Date.now() }];
      saveSessions(sessions);
      saveSessionMessages(id, []);
      activeId = id;
    }

    if (!activeId || !sessions.some(s => s.id === activeId)) {
      activeId = sessions[0].id;
    }

    setActiveSessionId(activeId);
    return { sessions, activeId };
  }

  function touchSession(id) {
    const sessions = loadSessions();
    const idx = sessions.findIndex(s => s.id === id);
    if (idx === -1) return;
    sessions[idx].updatedAt = Date.now();
    const updated = sessions.splice(idx, 1)[0];
    sessions.unshift(updated);
    saveSessions(sessions);
  }

  function maybeSetSessionTitle(id, userText) {
    const sessions = loadSessions();
    const idx = sessions.findIndex(s => s.id === id);
    if (idx === -1) return;
    const current = sessions[idx].title || "";
    if (current !== "New Chat") return;
    const title = userText.trim().slice(0, 28) || "New Chat";
    sessions[idx].title = title;
    sessions[idx].updatedAt = Date.now();
    saveSessions(sessions);
  }

  // ============================================================
  // MOOD LOGGING
  // ============================================================
  function loadMoodLog() {
    try {
      return JSON.parse(localStorage.getItem(MOOD_LOG_KEY)) || [];
    } catch {
      return [];
    }
  }

  function saveMoodLog(log) {
    localStorage.setItem(MOOD_LOG_KEY, JSON.stringify(log));
  }

  function updateBond(mood) {
    const moodPoints = {
      happy: 3, good: 2, calm: 1, neutral: 0,
      tired: -1, stressed: -2, anxious: -2, sad: -3, down: -3
    };
    let bond = parseInt(localStorage.getItem(BOND_KEY) || "0");
    bond += moodPoints[mood] || 0;
    bond = Math.max(-10, Math.min(40, bond));
    localStorage.setItem(BOND_KEY, bond);
  }

  function logMood(mood) {
    const log = loadMoodLog();
    log.push({ mood, ts: Date.now() });
    saveMoodLog(log);
    updateBond(mood);
  }

  // ============================================================
  // GEMINI API CALL - SIMPLIFIED
  // ============================================================
  let isWaitingForResponse = false;

  async function getApiKey() {
    try {
      const response = await fetch('/get-gemini-key');
      const data = await response.json();
      GEMINI_API_KEY = data.key;

      console.log("API KEY LOADED:", GEMINI_API_KEY);
    } catch (error) {
      console.error("Failed to get API key:", error);
    }
  }

  async function getAIResponse(userMessage) {
    if (isWaitingForResponse) {
      return "⏰ Please wait a moment before sending another message. 💚";
    }

    isWaitingForResponse = true;

    try {
      console.log("Calling Gemini API...");

      const requestBody = {
        contents: [
          {
            role: "user",
            parts: [{ text: SYSTEM_PROMPT + "\n\nUser says: " + userMessage + "\n\nRespond as Airé (1-2 sentences):" }]
          }
        ],
        generationConfig: {
          temperature: 0.7,
          maxOutputTokens: 5000,
          topP: 0.95,
        },
      };

      const response = await fetch(`${GEMINI_API_URL}?key=${GEMINI_API_KEY}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestBody),
      });

      console.log("Response status:", response.status);

      if (!response.ok) {
        if (response.status === 429) {
          return "⏰ Please wait a moment before sending another message. 💚";
        }
        if (response.status === 403 || response.status === 401) {
          return "🔑 API key issue. Please check your Gemini API key. 💚";
        }
        return "🌸 I'm here for you. Please try again. 💚";
      }

      const data = await response.json();

      if (data.candidates && data.candidates[0] && data.candidates[0].content) {
        return data.candidates[0].content.parts[0].text.trim();
      }

      return "🌸 How can I support you today? 💚";

    } catch (error) {
      console.error("API call failed:", error);
      return "🌸 Please try again in a moment. I'm here for you. 💚";
    } finally {
      isWaitingForResponse = false;
    }
  }

  // ============================================================
  // INITIALIZE SESSION
  // ============================================================
  const urlSessionId = getSessionIdFromURL();
  if (urlSessionId) {
    setActiveSessionId(urlSessionId);
  }

  const { activeId } = ensureActiveSession();
  let messages = loadSessionMessages(activeId);
  let lastRenderedDay = null;

  // ============================================================
  // RENDERING
  // ============================================================
  const renderDayDividerIfNeeded = (ts) => {
    const dk = dayKey(ts);
    if (dk === lastRenderedDay) return;
    lastRenderedDay = dk;
    const div = document.createElement("div");
    div.className = "day-divider";
    div.textContent = dayLabel(ts);
    chatMessages.appendChild(div);
  };

  const renderMessage = ({ role, text, ts }) => {
    renderDayDividerIfNeeded(ts);
    const row = document.createElement("div");
    row.className = `msg ${role}`;
    const bubble = document.createElement("div");
    bubble.className = "msg-bubble";

    if (role === "bot") {
      const title = document.createElement("div");
      title.className = "msg-title";
      title.textContent = "AI Bot: Airé 💚";
      bubble.appendChild(title);
    }

    const body = document.createElement("div");
    body.className = "msg-text";
    body.textContent = text;
    bubble.appendChild(body);

    const time = document.createElement("div");
    time.className = "msg-time";
    time.textContent = formatTime(ts);
    bubble.appendChild(time);

    row.appendChild(bubble);
    chatMessages.appendChild(row);
  };

  const renderAll = () => {
    chatMessages.innerHTML = "";
    lastRenderedDay = null;

    if (messages.length === 0) {
      const welcome = {
        role: "bot",
        text: "✨ Hi, I'm Airé! I'm here to listen and support you. 💚\n\nHow are you feeling today? 🦋",
        ts: Date.now(),
      };
      messages = [welcome];
      saveSessionMessages(activeId, messages);
      touchSession(activeId);
    }

    messages.forEach(renderMessage);
    scrollToBottom();
  };

  // ============================================================
  // TYPING INDICATOR
  // ============================================================
  let typingEl = null;

  const showTyping = () => {
    if (typingEl) return;
    typingEl = document.createElement("div");
    typingEl.className = "msg bot typing";
    const bubble = document.createElement("div");
    bubble.className = "msg-bubble";
    const title = document.createElement("div");
    title.className = "msg-title";
    title.textContent = "AI Bot: Airé 💚";
    bubble.appendChild(title);
    const body = document.createElement("div");
    body.className = "msg-text";
    body.innerHTML = `Airé is typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>`;
    bubble.appendChild(body);
    typingEl.appendChild(bubble);
    chatMessages.appendChild(typingEl);
    scrollToBottom();
  };

  const hideTyping = () => {
    if (typingEl) typingEl.remove();
    typingEl = null;
  };

  // ============================================================
  // MESSAGE ACTIONS
  // ============================================================
  const addMessage = (role, text, ts = Date.now()) => {
    const msg = { role, text, ts };
    messages.push(msg);
    saveSessionMessages(activeId, messages);
    touchSession(activeId);
    renderMessage(msg);
  };

  const sendUserText = async (text) => {
    const trimmed = (text || "").trim();
    if (!trimmed) return;

    // Add user message
    addMessage("user", trimmed);
    maybeSetSessionTitle(activeId, trimmed);
    
    // Show typing indicator
    showTyping();

    // Get AI response
    const aiResponse = await getAIResponse(trimmed);
    
    // Hide typing and add bot response
    hideTyping();
    addMessage("bot", aiResponse);
  };

  // ============================================================
  // AUTO-SCROLL
  // ============================================================
  const newMsgBtn = document.getElementById("newMsgBtn");
  const newMsgCount = document.getElementById("newMsgCount");
  let userAtBottom = true;
  let unreadCount = 0;

  const isNearBottom = (threshold = 80) => {
    const distance = chatMessages.scrollHeight - (chatMessages.scrollTop + chatMessages.clientHeight);
    return distance < threshold;
  };

  const scrollToBottom = () => {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  };

  const showNewBtn = () => {
    unreadCount += 1;
    if (newMsgCount) newMsgCount.textContent = unreadCount;
    if (newMsgBtn) newMsgBtn.style.display = "inline-flex";
  };

  const hideNewBtn = () => {
    unreadCount = 0;
    if (newMsgCount) newMsgCount.textContent = 0;
    if (newMsgBtn) newMsgBtn.style.display = "none";
  };

  const updateUserAtBottom = () => {
    userAtBottom = isNearBottom();
    if (userAtBottom) hideNewBtn();
  };

  // ============================================================
  // EVENT LISTENERS
  // ============================================================
  // Form submit
  chatForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return;
    sendUserText(text);
    chatInput.value = "";
  });

  // Enter key
  chatInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      const text = chatInput.value.trim();
      if (!text) return;
      sendUserText(text);
      chatInput.value = "";
    }
  });

  // Suggestion buttons
  const suggestionBtns = document.querySelectorAll(".suggest-item");
  console.log("Found suggestion buttons:", suggestionBtns.length);
  suggestionBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const text = btn.dataset.fill || btn.innerText || "";
      console.log("Suggestion clicked:", text);
      sendUserText(text);
    });
  });

  // Mood buttons
  const moodBtns = document.querySelectorAll(".mood-btn");
  console.log("Found mood buttons:", moodBtns.length);
  const moodMap = {
    happy: "happy", good: "good", neutral: "neutral",
    sad: "sad", tired: "tired"
  };

  moodBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const moodValue = btn.dataset.mood;
      const mood = moodMap[moodValue] || moodValue || "okay";
      console.log("Mood clicked:", mood);
      logMood(mood);
      sendUserText(`I'm feeling ${mood} today.`);
    });
  });

  // Clear chat button
  if (clearChatBtn) {
    clearChatBtn.addEventListener("click", () => {
      if (confirm("Clear this chat?")) {
        messages = [];
        localStorage.removeItem(sessionKey(activeId));
        touchSession(activeId);
        renderAll();
      }
    });
  }

  // New chat button
  const newChatBtn = document.getElementById("newChatBtn");
  if (newChatBtn) {
    newChatBtn.addEventListener("click", () => {
      const id = uid();
      const sessions = loadSessions();
      sessions.unshift({ id, title: "New Chat", updatedAt: Date.now() });
      saveSessions(sessions);
      saveSessionMessages(id, []);
      setActiveSessionId(id);
      location.href = `chat.html?id=${encodeURIComponent(id)}`;
    });
  }

  // Scroll events
  if (chatMessages) {
    chatMessages.addEventListener("scroll", updateUserAtBottom);
  }
  
  if (newMsgBtn) {
    newMsgBtn.addEventListener("click", () => {
      scrollToBottom();
      hideNewBtn();
      userAtBottom = true;
    });
  }

  // Mutation observer for new messages
  const observer = new MutationObserver(() => {
    if (userAtBottom) {
      scrollToBottom();
    } else {
      showNewBtn();
    }
  });
  if (chatMessages) {
    observer.observe(chatMessages, { childList: true, subtree: true });
  }

  // ============================================================
  // INITIAL RENDER
  // ============================================================
  renderAll();
  updateUserAtBottom();

  console.log("Chat initialized successfully!");
  console.log("Gemini API URL:", GEMINI_API_URL);
});