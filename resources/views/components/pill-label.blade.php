@props(['icon' => 'news'])
{{-- Outlined label that heads each column of the Events & Announcements block. --}}
<p {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded border-2 border-green-800 bg-white px-3 py-1 text-xs font-bold uppercase tracking-wider text-green-900']) }}>
    <svg aria-hidden="true" class="h-4 w-4 text-gold-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        @switch($icon)
            @case('megaphone')
                <path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1Zm12-3a5 5 0 0 1 0 8m3-11a9 9 0 0 1 0 14"/>
                @break
            @case('calendar')
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>
                @break
            @default
                <path d="M4 5h12v14H6a2 2 0 0 1-2-2V5Zm12 4h4v8a2 2 0 0 1-2 2h-2M8 9h4M8 13h4"/>
        @endswitch
    </svg>
    {{ $slot }}
</p>
