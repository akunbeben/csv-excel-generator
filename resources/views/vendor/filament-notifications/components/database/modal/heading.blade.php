@props([
    'unreadNotificationsCount',
])

<x-filament::modal.heading>
    <span class="relative">
        History

        @if ($unreadNotificationsCount)
            <x-filament::badge
                size="xs"
                class="absolute -top-1 start-full ms-1 w-max"
            >
                {{ $unreadNotificationsCount }}
            </x-filament::badge>
        @endif
    </span>
</x-filament::modal.heading>
