@props(['name'])

<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    @switch($name)
        @case('view')
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="2.5"/>
            @break
        @case('edit')
            <path d="m14 6 4 4M4 20l4.5-1 11-11a2.8 2.8 0 0 0-4-4l-11 11L4 20Z"/>
            @break
        @case('delete')
            <path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 4v5m4-5v5"/>
            @break
        @case('suspend')
            <circle cx="12" cy="12" r="8"/>
            <path d="m6.5 6.5 11 11"/>
            @break
        @case('activate')
            <circle cx="12" cy="12" r="8"/>
            <path d="m8.5 12 2.5 2.5 4.5-5"/>
            @break
    @endswitch
</svg>
