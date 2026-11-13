<x-layouts.app title="Page not found">
    <section class="container-pc max-w-3xl py-24 text-center">
        <p class="eyebrow">Error 404</p>
        <h1 class="mt-3 font-serif text-4xl font-bold text-green-900">We couldn't find that page</h1>
        <p class="mt-4 text-lg text-green-950/80">The page may have moved when the website was rebuilt. Try searching for it.</p>
        <form method="GET" action="{{ route('search') }}" role="search" class="mx-auto mt-8 flex max-w-lg gap-2">
            <label for="q404" class="sr-only">Search the site</label>
            <input id="q404" name="q" type="search" maxlength="100" placeholder="Search pages and news" class="w-full rounded-md border border-green-300 bg-white px-4 py-2.5">
            <button class="btn btn-green">Search</button>
        </form>
        <p class="mt-8"><a href="{{ route('home') }}" class="font-semibold text-green-700 hover:underline">Go to the home page &rarr;</a></p>
    </section>
</x-layouts.app>
