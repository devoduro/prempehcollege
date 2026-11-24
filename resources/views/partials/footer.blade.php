@php($footerMenu = \App\Support\Menu::tree('footer'))
@php($phoneHref = 'tel:'.preg_replace('/[^+\d]/', '', (string) config('site.phone')))
@php($link = 'text-green-100/85 transition hover:text-gold-400')
<footer class="relative mt-16 overflow-hidden bg-green-950 text-green-100">
    <div class="h-1.5 bg-gold-400"></div>
    <div class="pointer-events-none absolute -right-24 top-10 h-72 w-72 rounded-full bg-gold-400/5 blur-3xl" aria-hidden="true"></div>

    <div class="container-pc grid gap-12 pb-12 pt-14 md:grid-cols-2 lg:grid-cols-12">
        {{-- Brand --}}
        <div class="lg:col-span-4">
            <img src="{{ asset('images/crest-white.png') }}" alt="Prempeh College" class="h-14 w-auto" width="222" height="85" loading="lazy">
            <p class="mt-5 max-w-xs text-sm leading-relaxed text-green-100/80">Since {{ config('site.founded') }}, shaping leaders in {{ config('site.location') }}.</p>
            <p class="mt-5 inline-block border-l-2 border-gold-400 pl-3 font-serif text-lg italic text-gold-300">&ldquo;{{ config('site.facts.motto') }}&rdquo;</p>
            <a href="{{ route('contact') }}" class="mt-7 flex w-fit items-center gap-2 rounded-full bg-gold-400 px-6 py-2.5 text-sm font-bold text-green-950 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-gold-300">Get in touch <span aria-hidden="true">&rarr;</span></a>
        </div>

        {{-- Explore --}}
        <nav aria-label="Footer" class="lg:col-span-3">
            <h2 class="font-serif text-lg font-bold text-gold-400">Explore</h2>
            <span class="mt-2 block h-0.5 w-8 bg-gold-400/60"></span>
            <ul class="mt-5 grid grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
                @foreach (config('site.sections') as $key => $label)
                    <li><a class="{{ $link }}" href="{{ url($key) }}">{{ $label }}</a></li>
                @endforeach
                <li><a class="{{ $link }}" href="{{ route('programmes.index') }}">Programmes</a></li>
                <li><a class="{{ $link }}" href="{{ route('houses.index') }}">Houses</a></li>
                <li><a class="{{ $link }}" href="{{ route('prefects.index') }}">Prefects</a></li>
                <li><a class="{{ $link }}" href="{{ route('facilities.index') }}">Facilities</a></li>
                <li><a class="{{ $link }}" href="{{ route('church.index') }}">Church life</a></li>
            </ul>
        </nav>

        {{-- News and community --}}
        <div class="lg:col-span-2">
            <h2 class="font-serif text-lg font-bold text-gold-400">Community</h2>
            <span class="mt-2 block h-0.5 w-8 bg-gold-400/60"></span>
            <ul class="mt-5 space-y-2.5 text-sm">
                <li><a class="{{ $link }}" href="{{ route('news.index') }}">News</a></li>
                <li><a class="{{ $link }}" href="{{ route('events.index') }}">Events</a></li>
                <li><a class="{{ $link }}" href="{{ route('announcements.index') }}">Announcements</a></li>
                <li><a class="{{ $link }}" href="{{ url('about/old-boys-association') }}">Old Boys Association</a></li>
                <li><a class="{{ $link }}" href="{{ route('osa.home') }}">Old Students portal</a></li>
            </ul>
        </div>

        {{-- Contact --}}
        <div class="lg:col-span-3">
            <h2 class="font-serif text-lg font-bold text-gold-400">Contact</h2>
            <span class="mt-2 block h-0.5 w-8 bg-gold-400/60"></span>
            <ul class="mt-5 space-y-3.5 text-sm">
                <li class="flex gap-3">
                    <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                    <a class="{{ $link }}" href="{{ $phoneHref }}">{{ config('site.phone') }}</a>
                </li>
                <li class="flex gap-3">
                    <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>{{ config('site.location') }}</span>
                </li>
                @if (config('site.email'))
                    <li class="flex gap-3">
                        <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <a class="{{ $link }}" href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a>
                    </li>
                @endif
                <li class="flex gap-3">
                    <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <a class="{{ $link }}" href="{{ route('contact') }}">Send a message</a>
                </li>
                @foreach ($footerMenu as $item)
                    @if ($item['url'])
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="m4 8 8 5 8-5"/></svg>
                            <a class="{{ $link }}" href="{{ $item['url'] }}" rel="noopener">{{ $item['label'] === 'Email' ? 'Staff email' : $item['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-pc flex flex-col items-center justify-center gap-3 py-5 text-xs text-green-300">
            <p>&copy; {{ now()->year }} {{ config('site.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>
