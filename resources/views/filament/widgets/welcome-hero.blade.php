<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-7 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-[#ffc53a]/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-[#115e03]/40 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-medium text-[#ffc53a]">{{ $date }}</p>
                <h2 class="mt-1 text-3xl font-bold tracking-tight">{{ $title }}</h2>
                <p class="mt-1 max-w-xl text-sm text-slate-300">Here is what is happening on the Prempeh College website today.</p>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-medium">
                    @foreach ($chips as $chip)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/15">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach ($actions as $a)
                    <a href="{{ $a['url'] }}" @if (! empty($a['external'])) target="_blank" rel="noopener" @endif
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ ! empty($a['primary']) ? 'bg-white text-slate-800 hover:bg-gray-100' : 'bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/20' }}">{{ $a['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
