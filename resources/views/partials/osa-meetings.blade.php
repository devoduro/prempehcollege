@if (isset($osaEvents) && $osaEvents->isNotEmpty())
    <section class="mt-12" aria-labelledby="osa-meetings-heading">
        <h2 id="osa-meetings-heading" class="font-serif text-2xl font-bold text-green-900">AGM and association meetings</h2>
        <p class="mt-2 text-sm text-green-950/70">Old boys: scan the QR code at the venue, or register your attendance online below.</p>
        <ul class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ($osaEvents as $e)
                @php($upcoming = $e->starts_at->isFuture() || $e->starts_at->isToday())
                <li class="border-t-4 {{ $upcoming ? 'border-gold-400' : 'border-green-200' }} bg-white p-5 shadow-sm ring-1 ring-green-100">
                    <p class="text-xs font-bold uppercase tracking-wide {{ $upcoming ? 'text-gold-600' : 'text-green-950/50' }}">{{ $upcoming ? 'Upcoming' : 'Past meeting' }}</p>
                    <h3 class="mt-1 font-serif text-lg font-bold text-green-900">{{ $e->title }}</h3>
                    <p class="mt-1 text-sm text-green-950/80">{{ $e->starts_at->format('l, j F Y, g:i A') }}@if ($e->venue) · {{ $e->venue }}@endif</p>
                    @if ($e->description)<p class="mt-2 text-sm text-green-950/80">{{ \Illuminate\Support\Str::limit($e->description, 160) }}</p>@endif
                    @if ($e->isOpen())
                        <a href="{{ route('osa.register', $e->slug) }}" class="btn btn-green mt-4 inline-flex">Register my attendance</a>
                    @elseif (! $upcoming)
                        <p class="mt-3 text-sm font-semibold text-green-800">{{ $e->attendances_count }} old boys attended</p>
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="mt-4"><a href="{{ route('osa.home') }}" class="font-semibold text-green-800 underline">Old Students page, executives and all meetings</a></p>
    </section>
@endif
