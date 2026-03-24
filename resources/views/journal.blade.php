<x-layouts::app :title="__('Journal')">
    <div class="aire-shell">
        <section class="aire-panel p-6">
            <h1 class="text-2xl font-semibold text-[#2f4630]">Journal</h1>
            <p class="mt-2 text-sm text-[#5e715f]">Write your thoughts, track your patterns, and revisit your growth story.</p>
            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <article class="aire-soft p-4">
                    <h2 class="font-semibold">About Journal</h2>
                    <p class="mt-2 text-sm text-[#617662]">Your private space for reflection and gentle self-awareness.</p>
                </article>
                <article class="aire-soft p-4">
                    <h2 class="font-semibold">Start a New Entry</h2>
                    <textarea class="mt-2 w-full rounded-xl border border-[#c9d6c7] bg-white p-3" rows="5" placeholder="What are you feeling today?"></textarea>
                </article>
            </div>
        </section>
    </div>
</x-layouts::app>
