<x-layouts.app title="Church life" description="Denominations, worship and church activities at Prempeh College, Kumasi.">
    <x-page-header title="Church Life" eyebrow="Faith and fellowship" :crumbs="['Student Life' => url('/student-life'), 'Church life' => null]" />

    <div class="container-pc py-12">
        <p class="max-w-3xl text-lg leading-relaxed text-green-950/80">Prempeh College was founded with the Methodist and Presbyterian churches and its chapel has been part of campus life since the early days. Students worship, serve and grow together through the denominations below.</p>

        @if ($chaplaincy->isNotEmpty())
            <div class="mt-12"><x-section-title title="Chaplaincy and counselling" /></div>
            <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($chaplaincy as $person)<x-profile-card :person="$person" />@endforeach
            </ul>
        @endif

        <div class="mt-12"><x-section-title title="Denominations and fellowships" /></div>
        @if ($denominations->isEmpty())
            <p class="mt-8 rounded-sm bg-white p-8 text-center text-green-950/70 ring-1 ring-green-100">Church groups will be listed here soon.</p>
        @else
            <ul class="mt-8 grid gap-6 md:grid-cols-2">
                @foreach ($denominations as $d)
                    @php($cover = $d->getFirstMedia('cover'))
                    <li class="group relative flex flex-col overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                        @if ($cover)<img src="{{ \App\Support\Img::url($cover, 'card') }}" alt="" class="aspect-[16/8] w-full object-cover" loading="lazy" width="800" height="400">
                        @else<div class="grid aspect-[16/6] place-items-center bg-gradient-to-br from-green-900 to-green-700" aria-hidden="true"><x-facility-icon category="worship" class="h-12 w-12 text-gold-400/90" /></div>@endif
                        <div class="flex flex-1 flex-col border-t-4 border-gold-400 p-6">
                            <h3 class="font-serif text-2xl font-bold text-green-900"><a href="{{ url($d->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $d->name }}</a></h3>
                            @if ($d->summary)<p class="mt-2 flex-1 text-green-950/80">{{ $d->summary }}</p>@endif
                            @if ($d->meeting_times)<p class="mt-3 text-sm text-green-900"><span class="font-semibold">Meets:</span> {{ $d->meeting_times }}</p>@endif
                            <p class="mt-4 text-sm font-semibold text-green-700">Learn more &raquo;</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.app>
