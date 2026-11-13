@props(['title', 'eyebrow' => null, 'crumbs' => []])
<section class="bg-green-900 text-white">
    <div class="container-pc py-12 md:py-16">
        <nav aria-label="Breadcrumb" class="text-sm text-green-200">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a class="hover:text-gold-400" href="{{ route('home') }}">Home</a></li>
                @foreach ($crumbs as $label => $url)
                    <li aria-hidden="true">/</li>
                    <li>@if ($url)<a class="hover:text-gold-400" href="{{ $url }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif</li>
                @endforeach
            </ol>
        </nav>
        @if ($eyebrow)<p class="eyebrow mt-6 !text-gold-400">{{ $eyebrow }}</p>@endif
        <h1 class="mt-2 max-w-4xl font-serif text-3xl font-bold leading-tight md:text-5xl">{{ $title }}</h1>
    </div>
    <div class="h-1.5 bg-gold-400"></div>
</section>
