<x-layouts::app :title="__('Profile')">
    <div class="aire-shell">
        <section class="grid gap-4 md:grid-cols-3">
            <article class="aire-panel p-5">
                <h2 class="text-lg font-semibold text-[#2f4630]">Account Details</h2>
                <div class="mt-3 space-y-2 text-sm text-[#5e715f]">
                    <p>{{ auth()->user()->name }}</p>
                    <p>{{ auth()->user()->email }}</p>
                </div>
            </article>
            <article class="aire-panel p-5">
                <h2 class="text-lg font-semibold text-[#2f4630]">My Butterfly</h2>
                <p class="mt-3 text-6xl">🦋</p>
                <p class="mt-3 text-sm text-[#5e715f]">Health: Thriving · Mood: Calm & Joyful</p>
            </article>
            <article class="aire-panel p-5">
                <h2 class="text-lg font-semibold text-[#2f4630]">Recent Chat History</h2>
                <ul class="mt-3 space-y-2 text-sm text-[#5e715f]">
                    <li>Today · I felt overwhelmed.</li>
                    <li>Today · I had a great day.</li>
                    <li>Today · I am feeling anxious.</li>
                </ul>
            </article>
        </section>
    </div>
</x-layouts::app>
