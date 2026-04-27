document.addEventListener("DOMContentLoaded", function() {
  console.log("Signup.js loaded - DOM ready");

  // Terms modal elements
  const termsLink = document.getElementById("termsLink");
  const modal = document.getElementById("termsModal");
  const closeBtn = document.getElementById("closeTerms");

  console.log("termsLink found:", termsLink);
  console.log("modal found:", modal);
  console.log("closeBtn found:", closeBtn);

  // Open modal when Terms link is clicked
  if (termsLink && modal) {
    console.log("Setting up termsLink click listener");
    termsLink.addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log("Terms link clicked - opening modal");
      modal.classList.remove("hidden");
      console.log("Modal hidden class removed, current classes:", modal.className);
    });
  } else {
    console.error("termsLink or modal not found!");
    if (!termsLink) console.error("termsLink element missing");
    if (!modal) console.error("modal element missing");
  }

  // Close modal when X button is clicked
  if (closeBtn && modal) {
    closeBtn.addEventListener("click", function(e) {
      e.preventDefault();
      console.log("Close button clicked");
      modal.classList.add("hidden");
    });
  }

  // Close modal when clicking outside the modal content
  if (modal) {
    modal.addEventListener("click", function(e) {
      if (e.target === modal) {
        console.log("Clicked outside modal - closing");
        modal.classList.add("hidden");
      }
    });
  }
});