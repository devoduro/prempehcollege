@props(['title', 'href' => null, 'linkLabel' => 'View all', 'id' => null, 'level' => 'h2'])
{{-- Section heading: bold uppercase title, gold underline accent and a full-width hairline. --}}
<div class="flex items-end justify-between gap-4 border-b border-green-200 pb-3">
    <{{ $level }} @if ($id) id="{{ $id }}" @endif class="relative font-serif text-2xl font-bold uppercase tracking-wide text-green-900 md:text-3xl after:absolute after:-bottom-[13px] after:left-0 after:h-1 after:w-16 after:bg-gold-400">
        {{ $title }}
    </{{ $level }}>
    @if ($href)
        <a href="{{ $href }}" class="shrink-0 text-sm font-semibold text-green-700 hover:text-green-900 hover:underline">{{ $linkLabel }} &raquo;</a>
    @endif
</div>
