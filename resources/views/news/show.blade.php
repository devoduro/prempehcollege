@php($featured = $post->getFirstMedia('featured'))
<x-layouts.app :title="$post->seo_title ?: $post->title" :description="$post->seo_description ?: $post->excerpt"
    :image="$featured ? \App\Support\Img::url($featured, 'large') : null">
    <x-page-header :title="$post->title" :eyebrow="$post->category?->name"
        :crumbs="['News' => route('news.index'), \Illuminate\Support\Str::limit($post->title, 40) => null]" />

    <article class="container-pc max-w-4xl py-12">
        @if ($post->published_at)
            <p class="text-sm text-green-950/60">Published <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('j F Y') }}</time></p>
        @endif
        @if ($featured)
            <img src="{{ \App\Support\Img::url($featured, 'large') }}" alt="{{ \App\Support\Img::alt($featured) }}" class="mt-6 w-full rounded-lg" fetchpriority="high">
        @endif
        <div class="prose-pc mt-8">
            {!! \App\Support\Content::render($post->body) !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <ul class="mt-10 flex flex-wrap gap-2" aria-label="Tags">
                @foreach ($post->tags as $tag)
                    <li class="rounded-full bg-green-50 px-3 py-1 text-sm text-green-900 ring-1 ring-green-100">{{ $tag->name }}</li>
                @endforeach
            </ul>
        @endif
    </article>

    <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => \Illuminate\Support\Str::limit($post->title, 110),
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified' => $post->updated_at?->toIso8601String(),
        'image' => $featured ? [\App\Support\Img::url($featured, 'large')] : [],
        'publisher' => ['@type' => 'Organization', 'name' => config('site.name')],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

    @if ($related->isNotEmpty())
        <section class="bg-green-50 py-12">
            <div class="container-pc">
                <h2 class="font-serif text-2xl font-bold text-green-900">More news</h2>
                <div class="mt-6 grid gap-6 md:grid-cols-3">
                    @foreach ($related as $r)
                        <x-news-card :post="$r" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
