@php
    // Two-tone palette from jhs_portal: a darker icon block beside a solid colour body.
    $palette = [
        'blue' => ['bg-[#42a5dc]', 'bg-[#2c8fc6]'],
        'green' => ['bg-[#8cc152]', 'bg-[#74a83b]'],
        'orange' => ['bg-[#f5922f]', 'bg-[#de7a14]'],
        'red' => ['bg-[#da4453]', 'bg-[#c12f3e]'],
        'slate' => ['bg-[#5b6f8f]', 'bg-[#4a5c7a]'],
        'purple' => ['bg-[#967adc]', 'bg-[#7d62c4]'],
    ];
@endphp
<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($tiles as $t)
            <a href="{{ $t['url'] }}" class="group flex overflow-hidden rounded-md text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="flex w-16 shrink-0 items-center justify-center {{ $palette[$t['color']][1] }}">
                    <x-filament::icon :icon="$t['icon']" class="h-7 w-7" />
                </span>
                <span class="flex min-w-0 flex-1 flex-wrap items-center justify-between gap-x-3 gap-y-1 px-4 py-3 {{ $palette[$t['color']][0] }}">
                    <span class="min-w-0 flex-1 basis-28">
                        <span class="block text-base font-semibold leading-tight">{{ $t['title'] }}</span>
                        <span class="block truncate text-xs opacity-90">{{ $t['sub'] }}</span>
                    </span>
                    <span class="text-2xl font-bold leading-tight tracking-tight">{{ $t['value'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
