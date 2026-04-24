// -------- Recent Chat History (Profile) --------
const SESSIONS_KEY = "AIRE_CHAT_SESSIONS";
const ACTIVE_KEY = "AIRE_CHAT_ACTIVE_ID";
const SESSION_PREFIX = "AIRE_CHAT_";

function loadSessions() {
  try {
    return JSON.parse(localStorage.getItem(SESSIONS_KEY)) || [];
  } catch {
    return [];
  }
}

function loadSessionMessages(sessionId) {
  try {
    return JSON.parse(localStorage.getItem(SESSION_PREFIX + sessionId)) || [];
  } catch {
    return [];
  }
}

function formatDateTime(ts) {
  const d = new Date(ts);
  // matches your style like: 3/1/26, 8:31 pm
  return d.toLocaleString(undefined, {
    year: "2-digit",
    month: "numeric",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
}

function renderRecentChats(limit = 3) {
  const listEl = document.getElementById("recentChatList");
  if (!listEl) return;

  const sessions = loadSessions()
    .slice()
    .sort((a, b) => (b.lastUpdated || 0) - (a.lastUpdated || 0));

  listEl.innerHTML = "";

  if (!sessions.length) {
    listEl.innerHTML = `<div class="recent-empty">No chats yet.</div>`;
    return;
  }

  sessions.slice(0, limit).forEach((s) => {
    const msgs = loadSessionMessages(s.id);
    const lastMsg = msgs.length ? msgs[msgs.length - 1] : null;

    const title = s.title || "New Chat";
    const preview = lastMsg?.text?.slice(0, 60) || "No messages yet.";
    const when = formatDateTime(s.lastUpdated || Date.now());

    const item = document.createElement("button");
    item.type = "button";
    item.className = "recent-chat-item";
    item.innerHTML = `
      <div class="recent-title">${title}</div>
      <div class="recent-preview">${preview}</div>
      <div class="recent-date">${when}</div>
    `;

    // Optional: click opens that session in chat.html
    item.addEventListener("click", () => {
      localStorage.setItem(ACTIVE_KEY, s.id);
      window.location.href = `chat.html?id=${encodeURIComponent(s.id)}`;
    });

    listEl.appendChild(item);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  renderRecentChats(3);
});

// ===== Profile storage keys =====
const USER_KEY = "aire_user_profile"; // { name, email, passwordHash, photoDataUrl }

// Simple hash placeholder (NOT secure). Replace with real backend hashing later.
function fakeHash(str) {
  // quick "hash-like" string for demo only
  let h = 0;
  for (let i = 0; i < str.length; i++) h = (h * 31 + str.charCodeAt(i)) >>> 0;
  return "h_" + h.toString(16);
}

function loadUser() {
  try {
    return JSON.parse(localStorage.getItem(USER_KEY)) || null;
  } catch {
    return null;
  }
}

function saveUser(user) {
  localStorage.setItem(USER_KEY, JSON.stringify(user));
}

function maskPassword(pwHashOrPw) {
  if (!pwHashOrPw) return "—";
  // Always show bullets only (never show real password)
  return "••••••••";
}

function renderUser() {
  const roName = document.getElementById("roName");
  const roEmail = document.getElementById("roEmail");
  const roPassword = document.getElementById("roPassword");
  const img = document.getElementById("profilePhotoImg");

  const user = loadUser();

  roName.textContent = user?.name || "—";
  roEmail.textContent = user?.email || "—";
  roPassword.textContent = maskPassword(user?.passwordHash);

  if (user?.photoDataUrl) {
    img.src = user.photoDataUrl;
  } else {
    img.src = "profile.jpeg"; // fallback
  }
}

function fileToDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  renderUser();

  const form = document.getElementById("updateProfileForm");
  const updName = document.getElementById("updName");
  const updEmail = document.getElementById("updEmail");
  const updPassword = document.getElementById("updPassword");
  const updPhoto = document.getElementById("updPhoto");
  const img = document.getElementById("profilePhotoImg");

  // Live preview for photo
  updPhoto.addEventListener("change", async () => {
    const file = updPhoto.files?.[0];
    if (!file) return;

    const dataUrl = await fileToDataUrl(file);
    img.src = dataUrl; // instant preview
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const current = loadUser() || { name: "", email: "", passwordHash: "", photoDataUrl: "" };

    const name = updName.value.trim();
    const email = updEmail.value.trim();
    const pw = updPassword.value;

    // If user selected a photo, store it
    let photoDataUrl = current.photoDataUrl;
    const file = updPhoto.files?.[0];
    if (file) {
      photoDataUrl = await fileToDataUrl(file);
    }

    const next = {
      ...current,
      name: name || current.name,
      email: email || current.email,
      passwordHash: pw ? fakeHash(pw) : current.passwordHash,
      photoDataUrl,
    };

    saveUser(next);

    // clear inputs
    updName.value = "";
    updEmail.value = "";
    updPassword.value = "";
    updPhoto.value = "";

    renderUser();
    alert("Profile updated!");
  });
});

const logoutBtn = document.getElementById("logoutBtn");

if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    localStorage.removeItem("authToken");
    window.location.href = "login.html";
  });
}
