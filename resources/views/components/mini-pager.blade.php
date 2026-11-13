{{-- Compact numbered pager used under the Announcements and Events columns. --}}
@if ($paginator->hasPages())
    <nav aria-label="Pages" class="flex flex-wrap items-center justify-center gap-1 text-sm">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-green-950/50">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="grid h-8 w-8 place-items-center rounded-sm bg-green-800 font-semibold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="grid h-8 w-8 place-items-center rounded-sm bg-white font-semibold text-green-900 ring-1 ring-green-200 hover:ring-gold-400" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </nav>
@endif
