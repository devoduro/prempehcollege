<x-layouts.app title="Programmes & course combinations" description="The senior high school programmes and elective subject combinations at Prempeh College.">
    <x-page-header title="Programmes & Course Combinations" eyebrow="Academics" :crumbs="['Programmes' => null]" />

    <div class="container-pc py-12">
        <p class="max-w-3xl text-lg leading-relaxed text-green-950/80">Explore the range of subject combinations available for every programme at Prempeh College. Every student takes the core subjects, then chooses an elective combination within their programme.</p>

        @if ($programmes->isEmpty())
            <p class="mt-10 rounded-sm bg-white p-8 text-center text-green-950/70 ring-1 ring-green-100">Programmes will be listed here soon.</p>
        @else
            {{-- Jump links --}}
            <nav aria-label="Programmes" class="mt-8 flex flex-wrap gap-2">
                @foreach ($programmes as $p)
                    <a href="#{{ $p->slug }}" class="rounded-sm border-2 border-green-800 bg-white px-4 py-1.5 text-sm font-bold uppercase tracking-wide text-green-900 hover:bg-green-800 hover:text-white">{{ $p->name }}</a>
                @endforeach
            </nav>

            <div class="mt-10 space-y-10">
                @foreach ($programmes as $p)
                    <section id="{{ $p->slug }}" class="scroll-mt-28 overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100" aria-labelledby="h-{{ $p->slug }}">
                        <div class="flex flex-wrap items-end justify-between gap-3 bg-green-900 px-6 py-5 text-white">
                            <h2 id="h-{{ $p->slug }}" class="font-serif text-2xl font-bold">{{ $p->name }}</h2>
                            <a href="{{ url($p->path) }}" class="text-sm font-semibold text-gold-400 hover:underline">Full details &raquo;</a>
                        </div>
                        <div class="grid gap-8 p-6 lg:grid-cols-2">
                            <div>
                                @if ($p->summary)<p class="text-lg text-green-950/90">{{ $p->summary }}</p>@endif
                                @if ($p->core_subjects)
                                    <h3 class="mt-5 text-xs font-bold uppercase tracking-widest text-gold-600">Core subjects</h3>
                                    <ul class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($p->core_subjects as $s)<li class="rounded-full bg-green-50 px-3 py-1 text-sm text-green-900 ring-1 ring-green-100">{{ $s }}</li>@endforeach
                                    </ul>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-widest text-gold-600">Elective combinations</h3>
                                @forelse ($p->combinations as $c)
                                    <div class="mt-3 rounded-sm border-l-4 border-gold-400 bg-green-50/60 p-4">
                                        @if ($c->name)<p class="font-semibold text-green-900">{{ $c->name }}</p>@endif
                                        <ul class="mt-1 flex flex-wrap gap-2">
                                            @foreach ($c->subjects as $s)<li class="rounded bg-white px-2.5 py-0.5 text-sm text-green-950 ring-1 ring-green-200">{{ $s }}</li>@endforeach
                                        </ul>
                                        @if ($c->notes)<p class="mt-2 text-sm text-green-950/70">{{ $c->notes }}</p>@endif
                                    </div>
                                @empty
                                    <p class="mt-3 text-sm text-green-950/60">Elective combinations for this programme will be published soon.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
