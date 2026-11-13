@props(['category' => 'general'])
{{-- Simple line icons for facility categories, used when a facility has no photo yet. --}}
<svg {{ $attributes }} fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
    @switch($category)
        @case('dining')<path d="M6 3v7a2 2 0 0 0 2 2v9M10 3v7a2 2 0 0 1-2 2M8 3v5M17 3c-2 2-3 4-3 7 0 1.5 1 2 2 2h1v9"/>@break
        @case('health')<path d="M12 21s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.6-7 10-7 10Z"/><path d="M12 9v5M9.5 11.5h5"/>@break
        @case('staff')<path d="M4 20v-1a4 4 0 0 1 4-4h2a4 4 0 0 1 4 4v1M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM16 11a2.5 2.5 0 1 0 0-5M17 15h1a3 3 0 0 1 3 3v2"/>@break
        @case('boarding')<path d="M3 20V8l9-5 9 5v12M3 20h18M9 20v-6h6v6"/>@break
        @case('worship')<path d="M12 3v18M7 8h10"/>@break
        @case('academic')<path d="M3 9l9-5 9 5-9 5-9-5ZM7 11.5V16c0 1.5 2.2 3 5 3s5-1.5 5-3v-4.5"/>@break
        @case('sports')<circle cx="12" cy="12" r="9"/><path d="M12 3c3 3 3 15 0 18M3 12h18"/>@break
        @default<path d="M4 21V7l8-4 8 4v14M9 21v-6h6v6"/>
    @endswitch
</svg>
