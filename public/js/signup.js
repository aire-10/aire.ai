document.addEventListener("DOMContentLoaded", () => {

  // Terms modal elements
  const termsLink = document.getElementById("termsLink");
  const modal = document.getElementById("termsModal");
  const closeBtn = document.getElementById("closeTerms");

  if (termsLink && modal) {
    termsLink.addEventListener("click", (e) => {
      e.preventDefault();
      modal.classList.remove("hidden");
    });
  }

  if (closeBtn && modal) {
    closeBtn.addEventListener("click", () => {
      modal.classList.add("hidden");
    });
  }

  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.add("hidden");
      }
    });
  }

  // Let the form submit normally to Laravel backend
  // The server will handle user creation and redirect to /home
  // No form interception or manual redirect needed

});