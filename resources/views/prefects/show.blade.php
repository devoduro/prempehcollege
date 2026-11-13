@php
    $group = $board->getFirstMedia('group_photo');
    $gallery = $board->getMedia('gallery');
    // The top two positions are featured large; everyone else is listed by position.
    $featured = $prefects->filter(fn ($p) => $p->position && $p->position->rank <= 20 && $p->getFirstMedia('photo'))->take(2);
    $rest = $prefects->reject(fn ($p) => $featured->contains($p));
@endphp
<x-layouts.app :title="$board->name" :description="$board->description" :image="$group ? \App\Support\Img::url($group, 'large') : null">
    <x-page-header :title="$board->name" eyebrow="Prefects" :crumbs="['Prefects' => route('prefects.index'), $board->name => null]" />

    <div class="container-pc py-12">
        {{-- Board selector --}}
        @if ($boards->count() > 1)
            <nav aria-label="Prefectorial boards" class="flex flex-wrap gap-2">
                @foreach ($boards as $b)
                    <a href="{{ url('prefects/'.$b->slug) }}" @if ($b->id === $board->id) aria-current="page" @endif
                       class="rounded-sm px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 {{ $b->id === $board->id ? 'bg-green-800 text-white ring-green-800' : 'bg-white text-green-900 ring-green-200 hover:ring-gold-400' }}">
                        {{ $b->academic_year }}@if ($b->is_current) <span class="text-gold-300">&bull; current</span>@endif
                    </a>
                @endforeach
            </nav>
        @endif

        @if ($board->description)<p class="mt-6 max-w-3xl text-lg text-green-950/80">{{ $board->description }}</p>@endif

        @if ($group)
            <img src="{{ \App\Support\Img::url($group, 'large') }}" alt="{{ $board->name }} group photo" class="mt-8 w-full rounded-sm shadow-lg" data-lightbox>
        @endif

        {{-- Featured leaders --}}
        @if ($featured->isNotEmpty())
            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                @foreach ($featured as $p)
                    @php($photo = $p->getFirstMedia('photo'))
                    <article class="flex overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100">
                        <img src="{{ \App\Support\Img::url($photo, 'card') }}" alt="{{ $p->name }}" class="h-full w-2/5 object-cover object-top" loading="lazy" data-lightbox>
                        <div class="flex flex-1 flex-col justify-center border-l-4 border-gold-400 p-5">
                            <p class="eyebrow">{{ $p->displayTitle() }}</p>
                            <h2 class="mt-1 font-serif text-2xl font-bold text-green-900">{{ $p->name }}</h2>
                            <p class="mt-2 text-sm text-green-950/70">{{ collect([$p->house?->name, $p->programme_class])->filter()->join(' · ') }}</p>
                            @if ($p->bio)<p class="mt-3 text-sm text-green-950/80">{{ \Illuminate\Support\Str::limit($p->bio, 160) }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        {{-- Everyone else, grouped by position --}}
        @if ($rest->isNotEmpty())
            <h2 class="mt-12 font-serif text-2xl font-bold text-green-900">The board</h2>
            <ul class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($rest as $p)
                    @php($photo = $p->getFirstMedia('photo'))
                    <li class="overflow-hidden rounded-sm bg-white text-center shadow-sm ring-1 ring-green-100">
                        @if ($photo)
                            <img src="{{ \App\Support\Img::url($photo, 'card') }}" alt="{{ $p->name }}" class="aspect-[4/5] w-full object-cover object-top" loading="lazy" data-lightbox>
                        @else
                            <div class="grid aspect-[4/5] w-full place-items-center bg-green-100 font-serif text-4xl font-bold text-green-800" aria-hidden="true">{{ mb_substr($p->name, 0, 1) }}</div>
                        @endif
                        <div class="p-3">
                            <p class="font-semibold text-green-900">{{ $p->name }}</p>
                            <p class="text-sm font-medium text-gold-700">{{ $p->displayTitle() }}</p>
                            @if ($p->house || $p->programme_class)<p class="mt-0.5 text-xs text-green-950/60">{{ collect([$p->house?->name, $p->programme_class])->filter()->join(' · ') }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @elseif ($featured->isEmpty() && $gallery->isEmpty() && ! $group)
            <p class="mt-10 rounded-sm bg-white p-8 text-center text-green-950/70 ring-1 ring-green-100">Members of this board will be added soon.</p>
        @endif

        {{-- Photo gallery (e.g. archive photos from the old website) --}}
        @if ($gallery->isNotEmpty())
            <h2 class="mt-12 font-serif text-2xl font-bold text-green-900">Photos</h2>
            <div class="pc-gallery mt-4 md:!grid-cols-3">
                @foreach ($gallery as $img)
                    <figure><img src="{{ \App\Support\Img::url($img, 'card') }}" alt="{{ \App\Support\Img::alt($img, $board->name.' photo') }}" loading="lazy"></figure>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
