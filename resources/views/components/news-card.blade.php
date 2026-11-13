@props(['post', 'highlight' => false, 'excerpt' => true])
@php($cover = \App\Support\Img::cover($post))
{{-- Card: photo with category badge, serif uppercase title, short excerpt and "Read More". The first card in a row can be highlighted in gold. --}}
<article @class([
    'group relative flex flex-col overflow-hidden rounded-sm shadow-md ring-1 transition hover:-translate-y-0.5 hover:shadow-xl',
    'bg-gold-400 ring-gold-500' => $highlight,
    'bg-white ring-green-100' => ! $highlight,
])>
    <div class="relative">
        @if ($cover)
            <img src="{{ \App\Support\Img::url($cover) }}" alt="{{ \App\Support\Img::alt($cover) }}" class="aspect-[4/3] w-full object-cover" loading="lazy" width="800" height="600">
        @else
            <div class="grid aspect-[4/3] w-full place-items-center bg-green-900" aria-hidden="true">
                <img src="{{ asset('images/crest-white.png') }}" alt="" class="h-12 opacity-80" loading="lazy">
            </div>
        @endif
        @if ($post->category)
            <span class="absolute right-3 top-3 rounded-sm bg-green-800 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white shadow">{{ $post->category->name }}</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-serif text-lg font-bold uppercase leading-snug text-green-950">
            <a href="{{ url($post->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ \Illuminate\Support\Str::limit($post->title, 90) }}</a>
        </h3>
        @if ($excerpt && $post->excerpt)
            <p class="mt-3 text-sm leading-relaxed {{ $highlight ? 'text-green-950' : 'text-green-950/75' }}">{{ \Illuminate\Support\Str::limit($post->excerpt, 130) }}</p>
        @endif
        <p class="mt-auto flex items-center justify-between pt-4 text-sm">
            <span class="font-semibold {{ $highlight ? 'text-green-950' : 'text-green-700' }}">Read More &raquo;</span>
            @if ($post->published_at)
                <time class="{{ $highlight ? 'text-green-950/70' : 'text-green-950/50' }}" datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j M Y') }}</time>
            @endif
        </p>
    </div>
</article>
