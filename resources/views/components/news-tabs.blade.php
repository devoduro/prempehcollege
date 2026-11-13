@props(['active'])
{{-- Switches between the three listings, mirroring the home page block. --}}
<nav aria-label="News and events" class="flex flex-wrap gap-2">
    @foreach (['news' => ['News', 'news.index', 'news'], 'events' => ['Events', 'events.index', 'calendar'], 'announcements' => ['Announcements', 'announcements.index', 'megaphone']] as $key => [$label, $route, $icon])
        <a href="{{ route($route) }}" @if ($active === $key) aria-current="page" @endif
           class="rounded border-2 px-4 py-1.5 text-sm font-bold uppercase tracking-wide {{ $active === $key ? 'border-green-800 bg-green-800 text-white' : 'border-green-800 bg-white text-green-900 hover:bg-green-50' }}">{{ $label }}</a>
    @endforeach
</nav>
