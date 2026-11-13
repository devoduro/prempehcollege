@props(['id', 'title' => 'Video'])
<div class="pc-video">
    <button type="button" data-video-id="{{ $id }}" data-video-title="{{ $title }}"
        class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-white"
        aria-label="Play video: {{ $title }} (loads YouTube)">
        <span class="grid h-16 w-16 place-items-center rounded-full bg-gold-400 text-2xl text-green-950" aria-hidden="true">&#9654;</span>
        <span class="px-4 text-center text-sm text-green-100">Press play to load the video from YouTube.</span>
    </button>
</div>
