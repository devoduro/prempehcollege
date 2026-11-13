<x-layouts.app title="Facilities" description="School facilities at Prempeh College, Kumasi: dining hall and kitchen, clinic, staff common room, chapel and more.">
    <x-page-header title="School Facilities" eyebrow="Campus" :crumbs="['Facilities' => null]" />

    <div class="container-pc py-12">
        <p class="max-w-3xl text-lg leading-relaxed text-green-950/80">Prempeh College is a public secondary boarding school for boys. These are the places that keep our community fed, healthy, supported and growing.</p>

        @if ($facilities->isEmpty())
            <p class="mt-10 rounded-sm bg-white p-8 text-center text-green-950/70 ring-1 ring-green-100">Facilities will be listed here soon.</p>
        @else
            @foreach ($groups as $category => $items)
                <div class="mt-12"><x-section-title :title="\App\Models\Facility::CATEGORIES[$category] ?? 'Facilities'" /></div>
                <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $f)
                        @php($cover = $f->getFirstMedia('cover') ?? $f->getFirstMedia('gallery'))
                        <li class="group relative flex flex-col overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                            @if ($cover)
                                <img src="{{ \App\Support\Img::url($cover, 'card') }}" alt="" class="aspect-[16/10] w-full object-cover" loading="lazy" width="800" height="500">
                            @else
                                <div class="grid aspect-[16/10] w-full place-items-center bg-gradient-to-br from-green-900 to-green-700" aria-hidden="true">
                                    <x-facility-icon :category="$f->category" class="h-14 w-14 text-gold-400/90" />
                                </div>
                            @endif
                            @if ($f->is_planned)<span class="absolute right-3 top-3 rounded-sm bg-gold-400 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-green-950 shadow">Planned</span>@endif
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="font-serif text-xl font-bold text-green-900"><a href="{{ url($f->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $f->name }}</a></h3>
                                @if ($f->summary)<p class="mt-2 flex-1 text-sm leading-relaxed text-green-950/80">{{ $f->summary }}</p>@endif
                                <p class="mt-4 text-sm font-semibold text-green-700">Learn more &raquo;</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        @endif
    </div>
</x-layouts.app>
