document.addEventListener("DOMContentLoaded", () => {
  renderNavbar();

document.body.insertAdjacentHTML("beforeend", `
  <div id="hotlineModal" class="modal-overlay hidden">
    <div class="modal-card">
      <h2>📞 Hotline Support</h2>
      <p>You’re not alone 💚</p>
      <p><strong>Talian Harapan:</strong> 145</p>
      <p><strong>Emergency:</strong> 991</p>
      <button id="closeHotline" class="btn">Close</button>
    </div>
  </div>
`);

});

function renderNavbar() {
  const navbar = document.getElementById("navbar");
  if (!navbar) return;

  const isLoggedIn = !!localStorage.getItem("authToken");

  navbar.innerHTML = `
    <div class="landing-navbar">
      <a href="${isLoggedIn ? 'home.html' : 'landing.html'}" class="landing-logo">
        <img src="/images/logo.png" alt="Airé Logo" />
        <span>Airé</span>
      </a>

      <nav class="landing-navlinks">
        ${
          isLoggedIn
            ? `
              <a href="home.html" class="nav-link">Home</a>
              <a href="chat.html" class="nav-link">Airé</a>
              <a href="journal.html" class="nav-link">Journal</a>

              <div class="nav-dropdown">
                <button class="nav-dropbtn" type="button">Self Care ▾</button>
                <div class="nav-dropdown-menu">
                  <a href="grounding.html">Grounding</a>
                  <a href="breathing-mt.html">Breathing</a>
                  <a href="moodbooster.html">Mood Booster</a>
                </div>
              </div>

              <a href="growth.html">Butterfly Pet</a>
              
              <div class="nav-profile">
                <img id="navProfilePic" src="/images/profile.jpeg" alt="Profile"/>

                <div class="profile-dropdown" id="profileDropdown">
                  <a href="profile.html">Profile</a>
                  <a href="#" id="hotlineBtn">Hotline</a>
                  <a href="#" id="logoutNav">Logout</a>
                </div>
              </div>
            `
            : `
              <a href="landing.html" class="nav-link">About</a>
              <a href="login.html" class="nav-link">Login</a>
              <a href="signup.html" class="nav-link">Sign Up</a>
            `
        }
      </nav>
    </div>
  `;

  /* Dropdown behaviour */
  const profile = navbar.querySelector(".nav-profile");
  const dropdown = navbar.querySelector("#profileDropdown");

  if (profile && dropdown) {
    profile.addEventListener("click", (e) => {
      e.stopPropagation(); // prevent immediate closing
      dropdown.classList.toggle("show");
    });

    // ✅ Close when clicking outside
    document.addEventListener("click", (e) => {
      if (!profile.contains(e.target)) {
        dropdown.classList.remove("show");
      }
    });
  }

  // Self Care dropdown (click instead of hover)
  const selfCare = navbar.querySelector(".nav-dropdown");
  const selfCareBtn = navbar.querySelector(".nav-dropbtn");
  const selfCareMenu = navbar.querySelector(".nav-dropdown-menu");

  if (selfCareBtn && selfCareMenu) {
    selfCareBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      selfCareMenu.classList.toggle("show");
    });

    // Close when clicking outside
    document.addEventListener("click", (e) => {
      if (!selfCare.contains(e.target)) {
        selfCareMenu.classList.remove("show");
      }
    });
  }

  /* Logout */
  const logout = navbar.querySelector("#logoutNav");
  if (logout) {
    logout.addEventListener("click", () => {
      localStorage.removeItem("authToken");
      window.location.href = "login.html";
    });
  }

  /* Hotline */
  const hotline = navbar.querySelector("#hotlineBtn");

  if (hotline) {
    hotline.addEventListener("click", () => {
      document.getElementById("hotlineModal").classList.remove("hidden");
    });
  }

  document.addEventListener("click", (e) => {
    if (e.target.id === "closeHotline") {
      document.getElementById("hotlineModal").classList.add("hidden");
    }
  });

  document.addEventListener("click", (e) => {
    const modal = document.getElementById("hotlineModal");
    if (e.target === modal) {
      modal.classList.add("hidden");
    }
  });

  /* Load user's profile photo into navbar */
  const navPic = navbar.querySelector("#navProfilePic");

  try {
    const user = JSON.parse(localStorage.getItem("aire_user_profile"));

    if (user && user.photoDataUrl && navPic) {
      navPic.src = user.photoDataUrl;
    }
  } catch (e) {
    console.log("No profile photo found");
  }

const currentPage = window.location.href.split("/").pop();

const links = navbar.querySelectorAll(".nav-link");

links.forEach(link => {
  const linkPage = link.getAttribute("href");

  if (currentPage.includes(linkPage)) {
    link.classList.add("active");
  }
});
}