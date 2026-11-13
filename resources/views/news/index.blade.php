<x-layouts.app :title="$category ? $category->name.' news' : 'News'" description="News and achievements from Prempeh College, Kumasi.">
    <x-page-header :title="$category ? $category->name : 'News'" eyebrow="News & Events"
        :crumbs="$category ? ['News' => route('news.index'), $category->name => null] : ['News' => null]" />

    <div class="container-pc py-12">
        <x-news-tabs active="news" />

        <div class="mt-10">
            <x-section-title :title="$category ? $category->name : 'Latest news'" level="h2" />
        </div>

        @if ($categories->isNotEmpty())
            <nav aria-label="News categories" class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('news.index') }}" class="rounded-sm px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 {{ $category ? 'bg-white text-green-900 ring-green-200 hover:ring-gold-400' : 'bg-green-800 text-white ring-green-800' }}" @unless ($category) aria-current="page" @endunless>All</a>
                @foreach ($categories as $c)
                    <a href="{{ route('news.index', ['category' => $c->slug]) }}" @if ($category?->is($c)) aria-current="page" @endif
                       class="rounded-sm px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 {{ $category?->is($c) ? 'bg-green-800 text-white ring-green-800' : 'bg-white text-green-900 ring-green-200 hover:ring-gold-400' }}">{{ $c->name }}</a>
                @endforeach
            </nav>
        @endif

        @if ($posts->isEmpty())
            <p class="mt-10 text-green-950/70">No news yet.</p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($posts as $i => $post)
                    {{-- Highlight the newest story on the first page only. --}}
                    <x-news-card :post="$post" :highlight="$i === 0 && $posts->onFirstPage() && ! $category" />
                @endforeach
            </div>
            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </div>
</x-layouts.app>
