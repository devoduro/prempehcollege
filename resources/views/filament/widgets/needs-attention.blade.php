<x-filament-widgets::widget>
    <div class="rounded-md bg-white shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-white/10">
        <div class="border-b border-gray-100 px-4 py-3 dark:border-white/10">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Needs attention</h4>
            <p class="text-xs text-gray-500">Drafts waiting to be published and records missing details.</p>
        </div>
        <ul class="divide-y divide-gray-100 dark:divide-white/10">
            @foreach ($items as $i)
                <li>
                    @if ($i['url'])<a href="{{ $i['url'] }}" class="flex items-center justify-between px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-white/5">
                    @else<div class="flex items-center justify-between px-4 py-2.5 text-sm">@endif
                        <span class="text-gray-700 dark:text-gray-200">{{ $i['label'] }}</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $i['count'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">{{ $i['count'] > 0 ? $i['count'] : 'All done' }}</span>
                    @if ($i['url'])</a>@else</div>@endif
                </li>
            @endforeach
        </ul>
    </div>
</x-filament-widgets::widget>
