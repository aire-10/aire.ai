@extends('layouts.app')

@section('content')

<main class="journal-page-body">

  <!-- ══ BOOK WRAPPER ══ -->
  <div class="book-wrapper">

    <!-- Tabs -->
    <div class="book-tabs">
      <div class="book-tab tab-active" title="Journal">📖</div>
      <a href="javascript:void(0)" 
         class="book-tab tab-history tab-inactive" 
         id="historyTabLink" 
         title="History">HISTORY</a>
    </div>

    <!-- Open book -->
    <div class="book-open" id="bookOpen">

      <!-- LEFT PAGE -->
      <div class="book-page book-page-left">
        <div class="book-ruled-lines"></div>

        <div class="book-left-content">
          <span class="about-pill">About Journal</span>

          <h2 class="book-left-title">My Journal 🦋</h2>

          <p class="book-left-tagline"><em>Your Private Space for Reflection</em></p>

          <p class="book-left-desc">
            This journal is a dedicated space designed for you to document
            your daily life, thoughts, and feelings. Whether you want to
            celebrate, vent, or simply clear your mind — this space is yours.
            Every entry is saved privately on your device.
          </p>

          <div class="book-butterfly-img">
            <img src="{{ asset('images/inJar.png') }}" 
                 alt="Butterfly in jar"
                 onerror="this.style.display='none'" />
          </div>
        </div>
      </div>

      <!-- Spine -->
      <div class="book-spine">
        @for ($i = 0; $i < 10; $i++)
          <span></span>
        @endfor
      </div>

      <!-- RIGHT PAGE -->
      <div class="book-page book-page-right" id="rightPage">
        <div class="book-ruled-lines"></div>

        <div class="book-right-content">
          <div class="book-right-title">Start A New Entry</div>

          <div class="entry-box" style="width: 100%; min-height: 500px; border: 2px solid #8F9F8F; border-radius: 40px; padding: 20px; position: relative; z-index: 5;">
            <textarea
              id="journalInput"
              class="journal-textarea"
              placeholder="Document your day freely. Describe your emotions or reflect…"
            ></textarea>
          </div>

          <div class="journal-upload-row">
            <label class="upload-label">
              📎 Add a photo
              <input type="file" id="imageInput" accept="image/*" hidden />
            </label>

            <span class="upload-hint" id="uploadHint">No file selected</span>
          </div>

          <button class="journal-save-btn" type="button" onclick="saveEntry()">
            Save Entry
          </button>

          <img src="{{ asset('images/Land.png') }}" 
               class="corner-butterfly-img"
               onerror="this.style.display='none'" />
        </div>
      </div>

    </div>
  </div>

  <!-- Save toast -->
  <div id="saveMessage" class="save-message">
    🌿 Your journal has been saved
  </div>

</main>

@endsection

@push('scripts')


<script>
document.getElementById("imageInput").addEventListener("change", function () {
  const hint = document.getElementById("uploadHint");
  hint.textContent = this.files[0] ? "📄 " + this.files[0].name : "";
});

// PAGE FLIP
const historyTab = document.getElementById("historyTabLink");
const rightPage = document.getElementById("rightPage");

if (historyTab && rightPage) {
  historyTab.addEventListener('click', function(e) {
    e.preventDefault();

    rightPage.style.transform = "rotateY(-5deg) scale(1.01)";

    setTimeout(function() {
      rightPage.classList.add('flip-forward');
      rightPage.style.transform = "";
    }, 30);

    setTimeout(function() {
      window.location.href = "{{ url('journal-history') }}";
    }, 650);
  });
}

function saveEntry() {
  const text = document.getElementById("journalInput").value;
  const file = document.getElementById("imageInput").files[0];

  if (text.trim() === "") {
    alert("Please write something first.");
    return;
  }

  const formData = new FormData();
  formData.append("content", text);

  if (file) {
    formData.append("image", file);
  }

  fetch("/journal", {
    method: "POST",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    },
    body: formData
  })
  .then(res => {
    if (!res.ok) throw new Error("Failed");
    return res.json();
  })
  .then(data => {
    document.getElementById("journalInput").value = "";

    const input = document.getElementById("imageInput");
    const newInput = input.cloneNode(true);

    input.parentNode.replaceChild(newInput, input);

    // Re-attach event listener
    newInput.addEventListener("change", function () {
      const hint = document.getElementById("uploadHint");
      hint.textContent = this.files[0] ? "📄 " + this.files[0].name : "";
    });

    // Clear hint
    document.getElementById("uploadHint").textContent = "";

    const msg = document.getElementById("saveMessage");
    msg.classList.add("show");

    setTimeout(() => msg.classList.remove("show"), 2500);
  })
  .catch(() => {
    alert("Failed to save journal.");
  });
}
</script>

@endpush