@props(['id', 'title' => 'Video'])
{{-- Click-to-play: the YouTube player only loads after the visitor presses play. The poster is YouTube's own thumbnail for the video. --}}
<div class="pc-video group">
    <img src="https://i.ytimg.com/vi/{{ $id }}/hqdefault.jpg" alt="" loading="lazy" referrerpolicy="no-referrer"
         class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
    <div class="absolute inset-0 bg-gradient-to-t from-green-950/80 via-green-950/10 to-green-950/30" aria-hidden="true"></div>
    <button type="button" data-video-id="{{ $id }}" data-video-title="{{ $title }}"
        class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-white"
        aria-label="Play video: {{ $title }} (loads YouTube)">
        <span class="grid h-20 w-20 place-items-center rounded-full bg-gold-400 pl-1 text-3xl text-green-950 shadow-xl ring-8 ring-white/25 transition group-hover:scale-110" aria-hidden="true">&#9654;</span>
        <span class="absolute bottom-3 left-4 right-4 text-left text-xs text-green-100/90">{{ $title }} &middot; loads from YouTube when you press play</span>
    </button>
</div>
