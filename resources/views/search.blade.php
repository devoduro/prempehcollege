<x-layouts.app title="Search">
    <x-page-header title="Search" :crumbs="['Search' => null]" />

    <div class="container-pc max-w-4xl py-12">
        <form method="GET" action="{{ route('search') }}" role="search" class="flex gap-2">
            <label for="q" class="sr-only">Search the site</label>
            <input id="q" name="q" type="search" value="{{ $q }}" maxlength="100" placeholder="Search pages and news"
                class="w-full rounded-md border border-green-300 bg-white px-4 py-2.5 focus:border-green-700">
            <button class="btn btn-green">Search</button>
        </form>

        @if (mb_strlen($q) >= 2)
            @php($total = $pages->count() + $posts->count() + $people->count())
            <p class="mt-6 text-green-950/70" role="status">{{ $total }} {{ \Illuminate\Support\Str::plural('result', $total) }} for &ldquo;{{ $q }}&rdquo;</p>

            @if ($people->isNotEmpty())
                <h2 class="mt-8 font-serif text-xl font-bold text-green-900">People</h2>
                <ul class="mt-3 divide-y divide-green-100 rounded-lg bg-white ring-1 ring-green-100">
                    @foreach ($people as $person)
                        @php($photo = $person->getFirstMedia('photo'))
                        <li class="flex items-center gap-4 p-4">
                            @if ($photo)<img src="{{ \App\Support\Img::url($photo, 'thumb') }}" alt="" class="h-14 w-14 shrink-0 rounded-full object-cover object-top" loading="lazy">@endif
                            <div>
                                <a href="{{ url($person->group->page->path) }}" class="font-semibold text-green-800 hover:underline">{{ $person->name }}</a>
                                <p class="text-sm text-green-950/70">{{ $person->role }} &middot; {{ $person->group->page->title }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($pages->isNotEmpty())
                <h2 class="mt-8 font-serif text-xl font-bold text-green-900">Pages</h2>
                <ul class="mt-3 divide-y divide-green-100 rounded-lg bg-white ring-1 ring-green-100">
                    @foreach ($pages as $p)
                        <li class="p-4">
                            <a href="{{ url($p->path) }}" class="font-semibold text-green-800 hover:underline">{{ $p->title }}</a>
                            <p class="mt-1 text-sm text-green-950/70">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $p->body)), 160) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($posts->isNotEmpty())
                <h2 class="mt-8 font-serif text-xl font-bold text-green-900">News</h2>
                <ul class="mt-3 divide-y divide-green-100 rounded-lg bg-white ring-1 ring-green-100">
                    @foreach ($posts as $p)
                        <li class="p-4">
                            <a href="{{ url($p->path) }}" class="font-semibold text-green-800 hover:underline">{{ $p->title }}</a>
                            <p class="mt-1 text-sm text-green-950/70">{{ $p->published_at?->format('j M Y') }} &middot; {{ \Illuminate\Support\Str::limit((string) $p->excerpt, 140) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        @elseif ($q !== '')
            <p class="mt-6 text-green-950/70">Type at least two characters.</p>
        @endif
    </div>
</x-layouts.app>
