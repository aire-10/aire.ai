<x-layouts::app :title="__('History')">
    <div class="aire-shell">
        <section class="aire-panel p-6">
            <h1 class="text-2xl font-semibold text-[#2f4630]">Chat History</h1>
            <p class="mt-2 text-sm text-[#5e715f]">Review past sessions and continue where you left off.</p>
            <div class="mt-5 space-y-3">
                @foreach ([
                    ['Feeling overwhelmed with assignments', 'Anxious'],
                    ['A great day at the beach', 'Happy'],
                    ['Anxious with everything', 'Anxious'],
                ] as [$title, $mood])
                    <article class="aire-soft flex items-center justify-between p-3">
                        <div>
                            <p class="font-medium">{{ $title }}</p>
                            <p class="text-xs text-[#6c806c]">Today</p>
                        </div>
                        <span class="rounded-full bg-[#e6ece2] px-3 py-1 text-xs font-semibold text-[#496b4a]">{{ $mood }}</span>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts::app>
