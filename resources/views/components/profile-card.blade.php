@props(['person', 'lead' => false])
@php($photo = $person->getFirstMedia('photo'))
{{-- Profile card: the photo is cropped to the face and shoulders; the name and role are real text underneath.
     Selecting the photo opens the full poster. --}}
<li @class(['group overflow-hidden rounded-sm bg-white shadow-md ring-1 transition hover:-translate-y-0.5 hover:shadow-xl', 'ring-gold-400 sm:col-span-2 sm:row-span-1' => $lead, 'ring-green-100' => ! $lead])>
    <div class="{{ $lead ? 'sm:flex' : '' }}">
        @if ($photo)
            <img src="{{ \App\Support\Img::url($photo, 'card') }}" data-full="{{ \App\Support\Img::url($photo, 'large') }}"
                 alt="{{ $person->name ? $person->name.($person->role ? ', '.$person->role : '') : 'Photograph' }}"
                 class="w-full cursor-zoom-in object-cover object-top {{ $lead ? 'aspect-[4/4.2] sm:w-1/2' : 'aspect-[4/4.2]' }}" loading="lazy" width="800" height="1094">
        @else
            <div class="grid aspect-[4/4.2] w-full place-items-center bg-green-100 font-serif text-5xl font-bold text-green-800 {{ $lead ? 'sm:w-1/2' : '' }}" aria-hidden="true">{{ mb_substr((string) $person->name, 0, 1) }}</div>
        @endif
        <div class="border-t-4 border-gold-400 p-4 {{ $lead ? 'flex flex-col justify-center sm:w-1/2 sm:border-l-4 sm:border-t-0 sm:p-8' : '' }}">
            <h3 class="font-serif {{ $lead ? 'text-2xl' : 'text-lg' }} font-bold leading-snug text-green-900">{{ $person->name }}</h3>
            @if ($person->role)<p class="mt-1 text-sm font-semibold text-gold-700">{{ $person->role }}</p>@endif
            @if ($person->bio)<p class="mt-3 text-sm text-green-950/80">{{ $person->bio }}</p>@endif
        </div>
    </div>
</li>
