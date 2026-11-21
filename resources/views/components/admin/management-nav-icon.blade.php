@props(['name'])

<svg class="dar-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard')
            <path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/>
            @break
        @case('venues')
            <path d="M3 21h18M5 21V4h10v17m0-12h4v12M8 8h4m-4 4h4m-4 4h4"/>
            @break
        @case('bookings')
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4m8-4v4m-8 8h3"/>
            @break
        @case('users')
        @case('profile')
            <circle cx="12" cy="8" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
            @break
        @case('event-types')
            <path d="M3 4h8l10 10-7 7L3 10V4Z"/><circle cx="7.5" cy="8" r="1"/>
            @break
        @case('reviews')
            <path d="M21 12a8 8 0 0 1-8 8H4l2-4a8 8 0 1 1 15-4ZM9 12h7m-7 3h4"/>
            @break
        @case('spaces')
            <path d="M4 21h16M6 21V4h11v17m-4-8h1"/>
            @break
        @case('space-layouts')
            <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M10 4v16m0-8h11"/>
            @break
        @case('packages')
            <path d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4 9-4m-9 4v10M7 5l9 4"/>
            @break
        @case('logout')
            <path d="M10 4H4v16h6m4-4 4-4-4-4m4 4H8"/>
            @break
    @endswitch
</svg>
