@props(['house'])
@php
    $crest = $house->getFirstMedia('crest');
    $cover = $house->getFirstMedia('cover');
    $colour = $house->cssColour();
@endphp
{{-- House card: the house colour runs along the top edge and tints the crest badge. --}}
<article class="group relative flex h-full flex-col overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
    <div class="h-2" style="background-color: {{ $colour }}"></div>
    <div class="relative">
        @if ($cover)
            <img src="{{ \App\Support\Img::url($cover, 'card') }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy" width="800" height="600">
        @else
            <div class="aspect-[4/3] w-full" style="background: linear-gradient(135deg, {{ $colour }}, #032a0d)" aria-hidden="true"></div>
        @endif
        <div class="absolute -bottom-8 left-5 grid h-16 w-16 place-items-center overflow-hidden rounded-full bg-white shadow-lg ring-4 ring-white" style="outline: 3px solid {{ $colour }}">
            @if ($crest)
                <img src="{{ \App\Support\Img::url($crest, 'thumb') }}" alt="{{ $house->name }} crest" class="h-full w-full object-contain p-1" loading="lazy">
            @else
                <span class="font-serif text-2xl font-bold" style="color: {{ $colour }}" aria-hidden="true">{{ mb_substr($house->name, 0, 1) }}</span>
            @endif
        </div>
    </div>
    <div class="flex flex-1 flex-col px-5 pb-5 pt-12">
        <h3 class="font-serif text-xl font-bold text-green-950">
            <a href="{{ url($house->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $house->name }}</a>
        </h3>
        @if ($house->motto)<p class="mt-1 text-sm italic text-green-950/60">&ldquo;{{ $house->motto }}&rdquo;</p>@endif
        @if ($house->summary)<p class="mt-3 text-sm leading-relaxed text-green-950/80">{{ \Illuminate\Support\Str::limit($house->summary, 140) }}</p>@endif
        <p class="mt-auto pt-4 text-sm font-semibold text-green-700">Read the history &raquo;</p>
    </div>
</article>
