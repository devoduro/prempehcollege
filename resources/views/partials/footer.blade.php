@php($footerMenu = \App\Support\Menu::tree('footer'))
<footer class="mt-16 bg-green-950 text-green-100">
    <div class="h-1.5 bg-gold-400"></div>
    <div class="container-pc grid gap-10 py-12 md:grid-cols-4">
        <div class="md:col-span-2">
            <img src="{{ asset('images/crest-white.png') }}" alt="Prempeh College" class="h-12 w-auto" width="222" height="85" loading="lazy">
            <p class="mt-4 max-w-sm text-sm">Since {{ config('site.founded') }}, shaping leaders in {{ config('site.location') }}.</p>
        </div>
        <nav aria-label="Footer">
            <h2 class="font-serif font-bold text-gold-400">Explore</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach (config('site.sections') as $key => $label)
                    <li><a class="hover:text-gold-400" href="{{ url($key) }}">{{ $label }}</a></li>
                @endforeach
                <li><a class="hover:text-gold-400" href="{{ route('news.index') }}">News</a></li>
                <li><a class="hover:text-gold-400" href="{{ route('events.index') }}">Events</a></li>
                <li><a class="hover:text-gold-400" href="{{ route('announcements.index') }}">Announcements</a></li>
                <li><a class="hover:text-gold-400" href="{{ route('houses.index') }}">Houses</a></li>
                <li><a class="hover:text-gold-400" href="{{ route('programmes.index') }}">Programmes</a></li>
                <li><a class="hover:text-gold-400" href="{{ route('prefects.index') }}">Prefects</a></li>
            </ul>
        </nav>
        <div>
            <h2 class="font-serif font-bold text-gold-400">Contact</h2>
            <p class="mt-3 text-sm">{{ config('site.phone') }}<br>{{ config('site.location') }}</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a class="hover:text-gold-400" href="{{ route('contact') }}">Send a message</a></li>
                @foreach ($footerMenu as $item)
                    @if ($item['url'])
                        <li><a class="hover:text-gold-400" href="{{ $item['url'] }}" rel="noopener">{{ $item['label'] === 'Email' ? 'Staff email' : $item['label'] }}</a></li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div class="border-t border-green-800 py-4 text-center text-xs text-green-300">
        &copy; {{ now()->year }} {{ config('site.name') }}. All rights reserved.
    </div>
</footer>
