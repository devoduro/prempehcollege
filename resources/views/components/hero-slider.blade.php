@props(['slides'])
{{-- Full-width home slider (Swiper). Each slide: photo, green gradient, eyebrow, title, subtitle and a call to action. --}}
<section aria-roledescription="carousel" aria-label="Featured stories" class="hero-slider swiper relative overflow-hidden bg-green-950 text-white" data-hero-slider>
    <div class="swiper-wrapper">
        @foreach ($slides as $i => $slide)
            @php($img = $slide->getFirstMedia('image'))
            <div class="swiper-slide relative min-h-[28rem] md:min-h-[36rem]" role="group" aria-label="Slide {{ $i + 1 }} of {{ $slides->count() }}">
                @if ($img)
                    <img src="{{ \App\Support\Img::url($img, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover"
                         width="1600" height="900" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-950/75 to-green-900/10"></div>
                <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-green-950/70 to-transparent"></div>
                <div class="container-pc relative flex min-h-[28rem] items-center py-16 md:min-h-[36rem]">
                    <div class="max-w-2xl">
                        @if ($slide->eyebrow)<p class="eyebrow !text-gold-400">{{ $slide->eyebrow }}</p>@endif
                        <h2 class="mt-3 font-serif text-4xl font-bold leading-tight md:text-6xl">{{ $slide->title }}</h2>
                        @if ($slide->subtitle)<p class="mt-5 max-w-xl text-lg text-green-100">{{ $slide->subtitle }}</p>@endif
                        @if ($slide->button_label && $slide->button_url)
                            <a href="{{ preg_match('~^https://~', $slide->button_url) ? $slide->button_url : url($slide->button_url) }}" class="btn btn-gold mt-8">{{ $slide->button_label }} <span aria-hidden="true">&rarr;</span></a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev" aria-label="Previous slide"></div>
    <div class="swiper-button-next" aria-label="Next slide"></div>
    <div class="absolute inset-x-0 bottom-0 z-10 h-1.5 bg-gold-400"></div>
</section>
