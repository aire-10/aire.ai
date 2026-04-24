document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("signupForm");

  // ✅ MOVE THIS HERE (outside submit)
  const termsLink = document.getElementById("termsLink");
  const modal = document.getElementById("termsModal");
  const closeBtn = document.getElementById("closeTerms");

  termsLink.addEventListener("click", (e) => {
    e.preventDefault();
    modal.classList.remove("hidden");
  });

  closeBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
  });

  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.add("hidden");
    }
  });

  // 🔽 KEEP ONLY FORM LOGIC INSIDE
  form.addEventListener("submit", function(e) {

    e.preventDefault();

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm").value;

    if(password !== confirm){
      alert("Passwords do not match.");
      return;
    }

    // Save user
    const user = {
      name: name,
      email: email,
      password: btoa(password)
    };

    localStorage.setItem("aire_user", JSON.stringify(user));

    // initialize butterfly data
    localStorage.setItem("aire_bond_level", "0");
    localStorage.setItem("aire_mood_log", JSON.stringify([]));
    localStorage.setItem("aire_days_cared", "0");

    // redirect to chat page
    window.location.href = "chat.html";

  });

});