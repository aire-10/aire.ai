<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#d8e4d5] text-[#25372a]">
        <flux:header container class="border-b border-[#c9d6c7] bg-[#f8faf5]/95 backdrop-blur">
            <flux:sidebar.toggle class="mr-2 lg:hidden" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Home') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('journal')" :current="request()->routeIs('journal')" wire:navigate>
                    {{ __('Journal') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('history')" :current="request()->routeIs('history')" wire:navigate>
                    {{ __('History') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('profile.show')" :current="request()->routeIs('profile.show')" wire:navigate>
                    {{ __('Airé') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 py-0! rtl:space-x-reverse">
                <flux:tooltip :content="__('Explore')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Explore')" />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <flux:sidebar collapsible="mobile" sticky class="border-e border-[#c9d6c7] bg-[#f8faf5] lg:hidden">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Navigate')">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Home') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="book-open-text" :href="route('journal')" :current="request()->routeIs('journal')" wire:navigate>
                        {{ __('Journal') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('history')" :current="request()->routeIs('history')" wire:navigate>
                        {{ __('History') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user" :href="route('profile.show')" :current="request()->routeIs('profile.show')" wire:navigate>
                        {{ __('Airé') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
