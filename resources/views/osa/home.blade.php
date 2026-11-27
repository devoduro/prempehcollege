<x-layouts.app title="Old Students Association">
    <x-page-header title="Prempeh College Old Students Association" eyebrow="Alumni" :crumbs="['Old Students' => null]" />
    <div class="container-pc py-12">
        @if ($upcoming->isNotEmpty())
            <h2 class="font-serif text-2xl font-bold text-green-950">Upcoming meetings</h2>
            <ul class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach ($upcoming as $e)
                    <li class="rounded-sm bg-white p-5 ring-1 ring-green-100">
                        <p class="font-semibold text-green-950">{{ $e->title }}</p>
                        <p class="mt-1 text-sm text-green-950/70">{{ $e->starts_at->format('l, j F Y, g:i A') }}@if ($e->venue) · {{ $e->venue }}@endif</p>
                    </li>
                @endforeach
            </ul>
        @endif

        <h2 class="mt-12 font-serif text-2xl font-bold text-green-950">Global executives</h2>
        @if ($executives->isEmpty())
            <p class="mt-4 rounded-sm bg-white p-8 text-center text-green-950/70 ring-1 ring-green-100">The executives will appear here soon.</p>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($executives as $x)
                    @php $img = $x->getFirstMediaUrl('photo', 'card'); @endphp
                    <article class="overflow-hidden rounded-sm bg-white ring-1 ring-green-100">
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $x->name }}" loading="lazy" class="aspect-[4/5] w-full object-cover">
                        @else
                            <div class="flex aspect-[4/5] w-full items-center justify-center bg-green-50 font-serif text-5xl text-green-800">{{ mb_substr($x->name, 0, 1) }}</div>
                        @endif
                        <div class="p-4">
                            <h3 class="font-serif text-lg font-bold text-green-950">{{ $x->name }}</h3>
                            <p class="text-sm font-semibold text-green-800">{{ $x->position }}</p>
                            <p class="mt-1 text-xs text-green-950/60">
                                {{ collect([$x->chapter, $x->year_completed ? 'Year group '.$x->year_completed : null, $x->house?->name])->filter()->implode(' · ') }}
                            </p>
                            @if ($x->bio)<p class="mt-2 text-sm text-green-950/80">{{ $x->bio }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
