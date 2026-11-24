@php
    $sectionLabel = $page->section ? config('site.sections.'.$page->section) : null;
    $crumbs = $sectionLabel ? [$sectionLabel => url($page->section), $page->title => null] : [$page->title => null];
    $featured = $page->getFirstMedia('featured');
    $gallery = $page->getMedia('gallery')->concat($photos)->unique('id')->take(12);
    $facts = collect($page->facts ?? [])->filter(fn ($f) => filled($f['value'] ?? null) && filled($f['label'] ?? null))->values();
    $highlights = collect($page->highlights ?? [])->filter(fn ($h) => filled($h['title'] ?? null))->values();
    $hasBody = trim(strip_tags((string) $page->body)) !== '';
    $rendered = \App\Support\Content::withToc($page->body);
    $banner = $featured ?? $photos->first();
@endphp
<x-layouts.app :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $page->body), 160)"
    :image="$banner ? \App\Support\Img::url($banner, 'large') : null">

    {{-- Banner --}}
    <section class="relative overflow-hidden bg-green-950 text-white">
        @if ($banner)<img src="{{ \App\Support\Img::url($banner, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30" fetchpriority="high">@endif
        <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-950/85 to-green-900/30"></div>
        <div class="container-pc relative py-14 md:py-20">
            <nav aria-label="Breadcrumb" class="text-sm text-green-200">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a class="hover:text-gold-400" href="{{ route('home') }}">Home</a></li>
                    @foreach ($crumbs as $label => $url)
                        <li aria-hidden="true">/</li>
                        <li>@if ($url)<a class="hover:text-gold-400" href="{{ $url }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
            @if ($sectionLabel)<p class="eyebrow mt-6 !text-gold-400">{{ $sectionLabel }}</p>@endif
            <h1 class="mt-2 max-w-4xl font-serif text-4xl font-bold leading-tight md:text-6xl">{{ $page->title }}</h1>
        </div>
        <div class="h-1.5 bg-gold-400"></div>
    </section>

    {{-- Key facts strip --}}
    @if ($facts->isNotEmpty())
        <section class="border-b border-green-100 bg-white" aria-label="Key facts">
            @php($cols = [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3', 4 => 'md:grid-cols-4'][min($facts->count(), 4)])
            <dl class="container-pc grid grid-cols-2 gap-6 py-8 text-center {{ $cols }}">
                @foreach ($facts as $f)
                    <div><dd class="font-serif text-3xl font-bold text-green-900 md:text-4xl">{{ $f['value'] }}</dd><dt class="mt-1 text-xs font-bold uppercase tracking-widest text-green-800">{{ $f['label'] }}</dt></div>
                @endforeach
            </dl>
        </section>
    @endif

    <div class="container-pc grid gap-12 py-12 lg:grid-cols-[1fr_18rem]">
        <article class="min-w-0">
            @if ($hasBody)
                <div class="prose-pc">{!! $rendered['html'] !!}</div>
            @elseif ($highlights->isEmpty() && $news->isEmpty())
                <p class="rounded-sm bg-white p-8 text-center text-green-950/70 ring-1 ring-green-100">More about {{ $page->title }} will be published here soon.</p>
            @endif

            {{-- Timeline --}}
            @if ($highlights->isNotEmpty())
                <h2 class="mt-12 font-serif text-2xl font-bold text-green-900 first:mt-0">Highlights</h2>
                <ol class="relative ml-3 mt-6 space-y-7 border-l-4 border-gold-400/60 pl-8" aria-label="Highlights">
                    @foreach ($highlights as $h)
                        <li class="relative">
                            <span class="absolute -left-[2.65rem] top-2 h-5 w-5 rounded-full border-4 border-white bg-green-800 shadow ring-2 ring-gold-400" aria-hidden="true"></span>
                            @if (filled($h['year'] ?? null))<p class="inline-block rounded-sm bg-green-900 px-3 py-1 text-xs font-bold uppercase tracking-widest text-gold-400">{{ $h['year'] }}</p>@endif
                            <h3 class="mt-2 font-serif text-xl font-bold text-green-900">{{ $h['title'] }}</h3>
                            @if (filled($h['text'] ?? null))<p class="mt-1 leading-relaxed text-green-950/85">{{ $h['text'] }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            @endif

            {{-- Photos from related stories --}}
            @if ($gallery->isNotEmpty())
                <h2 class="mt-12 font-serif text-2xl font-bold text-green-900">Photos</h2>
                <div class="pc-gallery mt-4">
                    @foreach ($gallery as $img)
                        <figure><img src="{{ \App\Support\Img::url($img, 'card') }}" data-full="{{ \App\Support\Img::url($img, 'large') }}" alt="{{ \App\Support\Img::alt($img, $page->title.' photo') }}" loading="lazy"></figure>
                    @endforeach
                </div>
            @endif
            @include('partials.osa-meetings')
        </article>

        <aside class="space-y-8 lg:sticky lg:top-28 lg:self-start" aria-label="Related">
            @if ($siblings->count() > 1)
                <nav aria-label="{{ $sectionLabel }} pages">
                    <h2 class="font-serif text-lg font-bold text-green-900">{{ $sectionLabel }}</h2>
                    <ul class="mt-3 space-y-1 border-l-2 border-green-100">
                        @foreach ($siblings as $s)
                            <li><a href="{{ url($s->path) }}" @if ($s->id === $page->id) aria-current="page" @endif
                                   class="-ml-0.5 block border-l-2 py-1.5 pl-4 {{ $s->id === $page->id ? 'border-gold-400 font-semibold text-green-900' : 'border-transparent text-green-950/70 hover:border-green-300 hover:text-green-900' }}">{{ $s->title }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif
            <div class="rounded-sm bg-green-900 p-5 text-white shadow-md">
                <h2 class="font-serif text-lg font-bold text-gold-400">Get involved</h2>
                <p class="mt-2 text-sm text-green-100">Questions about {{ $page->title }}? The school office will be glad to help.</p>
                <a href="{{ route('contact') }}" class="btn btn-gold mt-4 w-full justify-center">Contact us</a>
            </div>
        </aside>
    </div>

    {{-- Latest related news --}}
    @if ($news->isNotEmpty())
        <section class="bg-green-50 py-14" aria-labelledby="related-news">
            <div class="container-pc">
                <x-section-title id="related-news" :title="'Latest: '.$page->title" :href="route('news.index')" link-label="All news" />
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($news->take(3) as $i => $post)
                        <x-news-card :post="$post" :highlight="$i === 0" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
