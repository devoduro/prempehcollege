@props(['title' => null, 'description' => null, 'image' => null, 'canonical' => null])
@php
    $siteName = config('site.name');
    $fullTitle = $title ? $title.' | '.$siteName : $siteName.' | '.config('site.location');
    $desc = $description ?: 'Prempeh College, Kumasi: a boys\' secondary school founded in '.config('site.founded').', known for academic excellence, NSMQ titles and robotics.';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($desc), 160) }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $title ?? $siteName }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($desc), 200) }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:image" content="{{ $image ?? asset('images/og-default.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#054716">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'HighSchool',
        'name' => $siteName,
        'url' => url('/'),
        'logo' => asset('images/crest-white.png'),
        'foundingDate' => (string) config('site.founded'),
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Kumasi', 'addressCountry' => 'GH'],
        'telephone' => config('site.phone'),
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-2 focus:top-2 focus:z-50 focus:rounded focus:bg-gold-400 focus:px-4 focus:py-2 focus:text-green-950">Skip to content</a>

    @include('partials.header')

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    @include('partials.footer')

    <dialog id="lightbox" class="m-auto max-h-[90vh] max-w-[95vw] bg-transparent p-0 backdrop:bg-black/80" aria-label="Image viewer">
        <img src="" alt="" class="max-h-[90vh] max-w-[95vw] rounded-lg object-contain">
        <p class="mt-2 text-center text-sm text-white">Click anywhere to close</p>
    </dialog>
</body>
</html>
