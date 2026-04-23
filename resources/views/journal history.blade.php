@extends('layouts.app')

@section('title', 'Journal History')
@section('body-class', 'journal-page-body')

@section('content')
<div class="book-wrapper">
    <div class="book-tabs">
        <a href="javascript:void(0)" class="book-tab tab-inactive" id="journalTabLink" title="Journal">📖</a>
        <div class="book-tab tab-active tab-history" title="History">HISTORY</div>
    </div>

    <div class="book-open" id="bookOpen">
        <div class="book-page book-page-left">
            <div class="book-ruled-lines"></div>
            <div class="book-left-content">
                <span class="about-pill">Journal History</span>
                <h2 class="book-left-title">Your Past Entries 🦋</h2>
                <p class="book-left-tagline"><em>Every thought, preserved.</em></p>
                <p class="book-left-desc">
                    Here you'll find all your previous journal entries, organized from newest to oldest.
                    Click any entry to view it in full detail, or delete entries you no longer wish to keep.
                    Your journal is always yours — private and safe.
                </p>
                <div class="book-butterfly-img">
                    <img src="{{ asset('inJar.png') }}" alt="Butterfly in jar" onerror="this.style.display='none'" />
                </div>
            </div>
        </div>

        <div class="book-spine">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
            <span></span><span></span>
        </div>

        <div class="book-page book-page-right" id="rightPage">
            <div class="book-ruled-lines"></div>
            <div class="book-right-content">
                <div class="book-right-title">📜 Entry History</div>

                <div style="margin: 15px 0 25px 0; padding: 10px; border: 1px dashed #d1ccbc; border-radius: 8px;">
                    <div style="font-weight: 800; font-size: 0.75rem; text-transform: uppercase; color: #4c7a60; margin-bottom: 10px; letter-spacing: 0.05em;">Filter</div>
                    <div style="display: flex; gap: 20px;">
                        <div style="display: flex; flex-direction: column; gap: 4px; flex: 1;">
                            <label style="font-size: 0.7rem; color: #8aaa88; font-weight: bold;">Month</label>
                            <select id="monthFilter" style="padding: 6px; border-radius: 5px; border: 1px solid #d1ccbc; background: #fdfaf3; font-family: inherit; font-size: 0.85rem; color: #4c7a60; cursor: pointer;">
                                <option value="ALL">All Months</option>
                                @foreach(['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'] as $month)
                                    <option value="{{ $month }}">{{ ucfirst(strtolower($month)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 4px; flex: 1;">
                            <label style="font-size: 0.7rem; color: #8aaa88; font-weight: bold;">Year</label>
                            <select id="yearFilter" style="padding: 6px; border-radius: 5px; border: 1px solid #d1ccbc; background: #fdfaf3; font-family: inherit; font-size: 0.85rem; color: #4c7a60; cursor: pointer;">
                                <option value="ALL">All Years</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin-top: -10px; border: 1px solid #d1ccbc; border-radius: 12px; background: rgba(255, 255, 255, 0.3); padding: 15px;">
                    <div style="font-weight: 800; font-size: 0.75rem; margin-bottom: 12px; text-transform: uppercase; color: #4c7a60; letter-spacing: 0.05em; display: flex; justify-content: space-between;">
                        <span>Latest Entries</span>
                        <span style="font-weight: normal; font-size: 0.65rem; color: #8aaa88;">Scroll for more ↓</span>
                    </div>
                    <div id="emptyState" style="text-align:center; padding:40px 0;">
                        <p style="color:#7a9a7a;">No journal entries yet.</p>
                        <p style="font-size:0.85rem;">Start writing in your journal to see entries here.</p>
                    </div>
                    <div id="entriesContainer" style="max-height: 250px; overflow-y: auto; padding-right: 10px; display: none;"></div>
                </div>
                <img src="{{ asset('Land.png') }}" class="corner-butterfly-img" onerror="this.style.display='none'" />
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let entries = JSON.parse(localStorage.getItem("journalEntries")) || [];
    const container = document.getElementById("entriesContainer");
    const emptyState = document.getElementById("emptyState");
    const monthFilter = document.getElementById("monthFilter");
    const yearFilter = document.getElementById("yearFilter");
    const rightPage = document.getElementById("rightPage");
    const journalTab = document.getElementById("journalTabLink");

    if (journalTab && rightPage) {
        journalTab.addEventListener('click', function(e) {
            e.preventDefault();
            rightPage.style.transform = "rotateY(-5deg) scale(1.01)";
            setTimeout(function() {
                rightPage.classList.add('flip-backward');
                rightPage.style.transform = "";
            }, 30);
            setTimeout(function() {
                window.location.href = "{{ url('journal') }}";
            }, 650);
        });
    }

    function navigateToDetail(entryData) {
        if (!rightPage) return;
        localStorage.setItem("selectedEntry", JSON.stringify(entryData));
        rightPage.style.transform = "rotateY(-5deg) scale(1.01)";
        setTimeout(function() {
            rightPage.classList.add('flip-forward');
            rightPage.style.transform = "";
        }, 30);
        setTimeout(function() {
            window.location.href = "{{ url('journal-detail') }}";
        }, 650);
    }

    const startYear = 2025;
    const currentYear = new Date().getFullYear();
    for (let year = startYear; year <= currentYear; year++) {
        let opt = document.createElement('option');
        opt.value = year;
        opt.innerHTML = year;
        yearFilter.appendChild(opt);
    }

    function renderEntries() {
        container.innerHTML = "";
        const selectedMonth = monthFilter.value;
        const selectedYear = yearFilter.value;

        let filteredEntries = entries.filter(function(entry) {
            const entryDateText = entry.date.toUpperCase();
            const matchMonth = (selectedMonth === "ALL") || entryDateText.includes(selectedMonth);
            const matchYear = (selectedYear === "ALL") || entryDateText.includes(selectedYear);
            return matchMonth && matchYear;
        });

        if (filteredEntries.length === 0) {
            container.style.display = "none";
            emptyState.style.display = "block";
            emptyState.innerHTML = entries.length === 0 
              ? '<p style="color:#7a9a7a; font-weight: bold;">No journal entries yet.</p><p style="font-size:0.85rem;">Start writing in your journal to see entries here.</p>'
              : '<p style="color:#7a9a7a; font-weight: bold;">No entries found.</p>';
        } else {
            emptyState.style.display = "none";
            container.style.display = "block";
            [...filteredEntries].reverse().forEach(function(entry, idx) {
                const originalIndex = entries.length - 1 - idx;
                const card = document.createElement("div");
                card.className = "entry-card";
                card.innerHTML = `
                    <div class="entry-date">${entry.date}</div>
                    <div class="entry-text">${entry.text.substring(0, 80)}${entry.text.length > 80 ? '...' : ''}</div>
                    <button class="delete-btn">Delete</button>
                `;
                card.addEventListener("click", (e) => {
                    if (!e.target.classList.contains("delete-btn")) navigateToDetail(entry);
                });
                card.querySelector(".delete-btn").addEventListener("click", (e) => {
                    e.stopPropagation();
                    if (confirm("Delete this entry?")) {
                        entries.splice(originalIndex, 1);
                        localStorage.setItem("journalEntries", JSON.stringify(entries));
                        renderEntries();
                    }
                });
                container.appendChild(card);
            });
        }
    }
    monthFilter.addEventListener("change", renderEntries);
    yearFilter.addEventListener("change", renderEntries);
    renderEntries();
});
</script>
@endpush