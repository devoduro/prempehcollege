@php
    $links = config('site.links');
    $apply = $links['admission'] ?: url('academics/overview');
    $tabs = array_filter([
        ['Admissions', $apply, 'bg-gold-400 text-green-950', 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z'],
        $links['online_classes'] ? ['Online classes', $links['online_classes'], 'bg-green-800 text-white', 'M3 5h18v11H3zM2 20h20'] : null,
        ['Programmes', route('programmes.index'), 'bg-white text-ink', 'M5 4v16M9 4v16M13 4v16M17 5l3 15'],
        ['Contact us', route('contact'), 'bg-ink text-white', 'M3 5h18v14H3zM3 6l9 7 9-7'],
    ]);
@endphp
<nav aria-label="Quick links" class="fixed right-0 top-1/2 z-30 hidden -translate-y-1/2 flex-col gap-2.5 md:flex">
    @foreach ($tabs as [$label, $url, $colour, $icon])
        <a href="{{ $url }}" title="{{ $label }}" @if (preg_match('~^https?://~', $url)) rel="noopener" @endif
           class="group flex h-14 w-[3.75rem] items-center justify-center rounded-l-xl shadow-lg ring-1 ring-black/5 transition-all hover:w-40 hover:justify-start hover:gap-2 hover:pl-4 {{ $colour }}">
            <svg aria-hidden="true" class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
            <span class="hidden whitespace-nowrap text-sm font-bold group-hover:inline group-focus-visible:inline">{{ $label }}</span>
            <span class="sr-only group-hover:hidden">{{ $label }}</span>
        </a>
    @endforeach
</nav>
