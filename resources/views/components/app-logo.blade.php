@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Airé" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-full bg-[#4f7649] text-sm text-white shadow-sm">
            🦋
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Airé" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-full bg-[#4f7649] text-sm text-white shadow-sm">
            🦋
        </x-slot>
    </flux:brand>
@endif
