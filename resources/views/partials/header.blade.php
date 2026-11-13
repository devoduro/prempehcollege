@php
    $menu = \App\Support\Menu::tree('main');
    $links = config('site.links');
    $href = fn ($url) => $url && preg_match('~^https?://~', $url) ? $url : url($url ?? '/');
@endphp
<div class="bg-green-950 text-sm text-green-100">
    <div class="container-pc flex flex-wrap items-center justify-between gap-x-6 gap-y-1 py-2">
        <p>{{ config('site.location') }} &middot; <a class="hover:text-gold-400" href="tel:{{ preg_replace('/\s+/', '', config('site.phone')) }}">{{ config('site.phone') }}</a></p>
        <nav aria-label="Student portals" class="flex flex-wrap gap-x-5">
            @if ($links['online_classes'])<a class="hover:text-gold-400" href="{{ $links['online_classes'] }}" rel="noopener">Online Classes</a>@endif
            @if ($links['reports'])<a class="hover:text-gold-400" href="{{ $links['reports'] }}" rel="noopener">Report Portal</a>@endif
            @if ($links['transcripts'])<a class="hover:text-gold-400" href="{{ $links['transcripts'] }}" rel="noopener">Request Transcript</a>@endif
            <a class="hover:text-gold-400" href="{{ route('announcements.index') }}">Announcements</a>
        </nav>
    </div>
</div>

<header class="sticky top-0 z-40 bg-green-900 text-white shadow-lg" x-data="mobileNav">
    <div class="container-pc flex h-20 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
            <img src="{{ asset('images/crest-white.png') }}" alt="Prempeh College" class="h-12 w-auto" width="222" height="85">
        </a>

        <nav aria-label="Primary" class="hidden lg:block">
            <ul class="flex items-center gap-1 font-medium">
                @foreach ($menu as $item)
                    @if ($item['children'])
                        <li class="group relative">
                            <button type="button" class="flex items-center gap-1 px-3 py-2 group-focus-within:text-gold-400 group-hover:text-gold-400" aria-haspopup="true">
                                {{ $item['label'] }} <span aria-hidden="true" class="text-xs">&#9662;</span>
                            </button>
                            <ul class="invisible absolute left-0 top-full w-64 rounded-b-md border-t-4 border-gold-400 bg-white py-1 text-ink opacity-0 shadow-xl transition group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                                @if ($item['url'])
                                    <li><a class="block px-4 py-2.5 font-semibold hover:bg-green-50" href="{{ $href($item['url']) }}">{{ $item['label'] }}</a></li>
                                @endif
                                @foreach ($item['children'] as $child)
                                    <li><a class="block px-4 py-2.5 hover:bg-green-50" href="{{ $href($child['url']) }}" @if ($child['external']) rel="noopener" @endif>{{ $child['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li><a class="px-3 py-2 hover:text-gold-400" href="{{ $href($item['url']) }}">{{ $item['label'] }}</a></li>
                    @endif
                @endforeach
                <li class="group relative">
                    <button type="button" class="flex items-center gap-1 px-3 py-2 group-focus-within:text-gold-400 group-hover:text-gold-400" aria-haspopup="true">
                        News &amp; Events <span aria-hidden="true" class="text-xs">&#9662;</span>
                    </button>
                    <ul class="invisible absolute right-0 top-full w-56 rounded-b-md border-t-4 border-gold-400 bg-white py-1 text-ink opacity-0 shadow-xl transition group-focus-within:visible group-focus-within:opacity-100 group-hover:visible group-hover:opacity-100">
                        <li><a class="block px-4 py-2.5 hover:bg-green-50" href="{{ route('news.index') }}">News</a></li>
                        <li><a class="block px-4 py-2.5 hover:bg-green-50" href="{{ route('events.index') }}">Events</a></li>
                        <li><a class="block px-4 py-2.5 hover:bg-green-50" href="{{ route('announcements.index') }}">Announcements</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('search') }}" class="p-2 hover:text-gold-400" aria-label="Search the site">
                <svg aria-hidden="true" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            </a>
            @if ($links['admission'])
                <a href="{{ $links['admission'] }}" class="btn btn-gold hidden sm:inline-flex" rel="noopener">Apply Now</a>
            @endif
            <button type="button" class="p-2 lg:hidden" aria-controls="mobile-menu" aria-label="Menu" x-bind:aria-expanded="open" x-on:click="toggle">
                <svg aria-hidden="true" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    <nav id="mobile-menu" aria-label="Mobile" class="border-t border-green-800 bg-green-900 lg:hidden" x-show="open" x-cloak>
        <ul class="container-pc space-y-1 py-4">
            @foreach ($menu as $item)
                <li>
                    @if ($item['children'])
                        <details class="group">
                            <summary class="flex cursor-pointer list-none items-center justify-between py-2 font-medium">{{ $item['label'] }} <span aria-hidden="true">+</span></summary>
                            <ul class="mb-2 ml-3 border-l border-green-700 pl-3">
                                @foreach ($item['children'] as $child)
                                    <li><a class="block py-1.5 text-green-100 hover:text-gold-400" href="{{ $href($child['url']) }}">{{ $child['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </details>
                    @else
                        <a class="block py-2 font-medium hover:text-gold-400" href="{{ $href($item['url']) }}">{{ $item['label'] }}</a>
                    @endif
                </li>
            @endforeach
            <li>
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between py-2 font-medium">News &amp; Events <span aria-hidden="true">+</span></summary>
                    <ul class="mb-2 ml-3 border-l border-green-700 pl-3">
                        <li><a class="block py-1.5 text-green-100 hover:text-gold-400" href="{{ route('news.index') }}">News</a></li>
                        <li><a class="block py-1.5 text-green-100 hover:text-gold-400" href="{{ route('events.index') }}">Events</a></li>
                        <li><a class="block py-1.5 text-green-100 hover:text-gold-400" href="{{ route('announcements.index') }}">Announcements</a></li>
                    </ul>
                </details>
            </li>
        </ul>
    </nav>
</header>
