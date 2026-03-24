<x-layouts::app :title="__('Dashboard')">
    <div class="aire-shell space-y-6">
        <section class="grid gap-4 lg:grid-cols-3">
            <article class="aire-panel p-4 lg:col-span-2">
                <div class="grid gap-4 md:grid-cols-[220px_1fr]">
                    <div class="aire-soft flex items-center justify-center p-5 text-7xl">🦋</div>
                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#617662]">Daily Check-in</p>
                        <h2 class="text-2xl font-semibold text-[#2c442d]">Your butterfly is glowing softly today.</h2>
                        <p class="text-sm text-[#5e715f]">Want to pause and breathe together before we continue?</p>
                        <button class="aire-chip">Breathe</button>
                    </div>
                </div>
            </article>

            <article class="aire-panel p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#617662]">Daily Affirmation</p>
                <p class="mt-3 text-base font-medium text-[#344b35]">I am allowed to grow at my own pace, just like my butterfly.</p>
            </article>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="aire-panel p-5">
                <p class="text-sm font-semibold text-[#344b35]">Chat with Airé</p>
                <p class="mt-2 text-6xl">🌿</p>
                <a href="{{ route('history') }}" class="mt-3 inline-flex text-sm font-semibold text-[#486a49]">View chats →</a>
            </article>

            <article class="aire-panel p-5">
                <p class="text-sm font-semibold text-[#344b35]">Mood Check-in</p>
                <p class="mt-4 text-2xl">😄 😊 😐 😟 😞</p>
                <button class="mt-4 rounded-xl bg-[#dfe9da] px-4 py-2 text-sm font-semibold text-[#375238]">Select Mood</button>
            </article>

            <article class="aire-panel p-5">
                <p class="text-sm font-semibold text-[#344b35]">Self-care Tools</p>
                <div class="mt-4 grid grid-cols-2 gap-2 text-xs font-semibold text-white">
                    <span class="rounded-lg bg-[#4f7649] px-3 py-2 text-center">Breathing</span>
                    <span class="rounded-lg bg-[#4f7649] px-3 py-2 text-center">Grounding</span>
                    <span class="rounded-lg bg-[#4f7649] px-3 py-2 text-center">Mood Boost</span>
                    <span class="rounded-lg bg-[#4f7649] px-3 py-2 text-center">Mind Reset</span>
                </div>
            </article>
        </section>
    </div>
</x-layouts::app>
