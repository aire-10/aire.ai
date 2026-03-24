<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('Welcome')])
    </head>
    <body>
        <div class="aire-shell">
            <header class="aire-panel mb-8 flex items-center justify-between px-6 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-full bg-[#4f7649] text-lg text-white">🦋</span>
                    <div>
                        <p class="text-2xl font-semibold text-[#355238]">Airé</p>
                        <p class="text-xs text-[#6e816f]">Your calm companion</p>
                    </div>
                </a>
                <nav class="hidden items-center gap-6 text-sm font-medium text-[#5d705f] sm:flex">
                    <a href="#features" class="hover:text-[#355238]">Features</a>
                    <a href="#about" class="hover:text-[#355238]">About</a>
                    <a href="{{ route('login') }}" class="aire-chip">Continue</a>
                </nav>
            </header>

            <section class="aire-panel overflow-hidden">
                <div class="grid gap-8 bg-gradient-to-br from-[#f6f4e7] via-[#e6eedf] to-[#cbd9c7] px-6 py-12 lg:grid-cols-2 lg:px-12">
                    <div class="space-y-6">
                        <p class="text-sm font-semibold uppercase tracking-wide text-[#486a49]">Welcome to Airé</p>
                        <h1 class="text-4xl font-semibold leading-tight text-[#233824] sm:text-5xl">Find your peace. Unleash your power.</h1>
                        <p class="max-w-xl text-base text-[#445845]">Chat anytime, journal your thoughts, and explore self-care tools in one gentle, beautiful experience.</p>
                        <a href="{{ route('register') }}" class="inline-flex rounded-full bg-[#4f7649] px-6 py-3 text-sm font-semibold text-white shadow hover:bg-[#42653d]">Start Your Journey</a>
                    </div>
                    <div class="aire-soft flex items-center justify-center p-6">
                        <div class="text-center">
                            <div class="text-8xl">🦋</div>
                            <p class="mt-3 text-sm text-[#59705a]">A safe space that grows with you.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['AI ChatBot: Airé', 'Your empathetic companion for personalized support.'],
                    ['Smart Journal', 'Track mood patterns and reflect with guided prompts.'],
                    ['Self-Care Tools', 'Breathing, grounding, and mood boosters in one place.'],
                ] as [$title, $copy])
                    <article class="aire-panel p-6 text-center">
                        <h2 class="mb-2 text-lg font-semibold text-[#2f4630]">{{ $title }}</h2>
                        <p class="text-sm text-[#5e715f]">{{ $copy }}</p>
                    </article>
                @endforeach
            </section>
        </div>
    </body>
</html>
