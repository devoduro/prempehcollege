@php($cover = $programme->getFirstMedia('cover'))
<x-layouts.app :title="$programme->name.' programme'" :description="$programme->summary" :image="$cover ? \App\Support\Img::url($cover, 'large') : null">
    <x-page-header :title="$programme->name" eyebrow="Programme" :crumbs="['Programmes' => route('programmes.index'), $programme->name => null]" />

    <div class="container-pc grid gap-12 py-12 lg:grid-cols-[1fr_18rem]">
        <article class="min-w-0">
            @if ($cover)<img src="{{ \App\Support\Img::url($cover, 'large') }}" alt="" class="mb-8 w-full rounded-sm shadow-md">@endif
            @if ($programme->summary)<p class="text-xl leading-relaxed">{{ $programme->summary }}</p>@endif
            @if ($programme->description)<div class="prose-pc mt-6">{!! \App\Support\Content::render($programme->description) !!}</div>@endif

            <h2 class="mt-10 font-serif text-2xl font-bold text-green-900">Elective combinations</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @forelse ($programme->combinations as $c)
                    <div class="rounded-sm border-l-4 border-gold-400 bg-white p-5 shadow-sm ring-1 ring-green-100">
                        <p class="font-semibold text-green-900">{{ $c->name ?: 'Combination '.$loop->iteration }}</p>
                        <ul class="mt-2 space-y-1 text-sm">@foreach ($c->subjects as $s)<li class="flex gap-2"><span class="text-gold-600" aria-hidden="true">&#10003;</span>{{ $s }}</li>@endforeach</ul>
                        @if ($c->notes)<p class="mt-3 text-sm text-green-950/70">{{ $c->notes }}</p>@endif
                    </div>
                @empty
                    <p class="text-green-950/60 sm:col-span-2">Elective combinations will be published soon.</p>
                @endforelse
            </div>
        </article>

        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            @if ($programme->core_subjects)
                <div class="rounded-sm bg-green-900 p-5 text-white shadow-md">
                    <h2 class="font-serif text-lg font-bold text-gold-400">Core subjects</h2>
                    <ul class="mt-3 space-y-1.5 text-sm">@foreach ($programme->core_subjects as $s)<li class="flex gap-2"><span aria-hidden="true">&#10003;</span>{{ $s }}</li>@endforeach</ul>
                </div>
            @endif
            <div>
                <h2 class="font-serif text-lg font-bold text-green-900">Other programmes</h2>
                <ul class="mt-3 space-y-1">@foreach ($others as $o)<li><a href="{{ url($o->path) }}" class="block rounded px-2 py-1.5 text-green-950/80 hover:bg-green-50 hover:text-green-900">{{ $o->name }}</a></li>@endforeach</ul>
                <a href="{{ route('programmes.index') }}" class="mt-3 inline-block text-sm font-semibold text-green-700 hover:underline">All programmes &raquo;</a>
            </div>
        </aside>
    </div>
</x-layouts.app>
