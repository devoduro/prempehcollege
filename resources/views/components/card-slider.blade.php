@props(['label' => 'Items', 'perView' => 3])
{{-- Moving card slider. Slides go in the slot as <div class="swiper-slide">…</div>. --}}
<div class="card-slider swiper relative px-0 md:px-6" data-card-slider data-per-view="{{ $perView }}" aria-roledescription="carousel" aria-label="{{ $label }}">
    <div class="swiper-wrapper pb-2">
        {{ $slot }}
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev !hidden md:!flex" aria-label="Previous"></div>
    <div class="swiper-button-next !hidden md:!flex" aria-label="Next"></div>
</div>
