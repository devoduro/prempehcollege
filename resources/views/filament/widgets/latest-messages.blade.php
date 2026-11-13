<x-filament-widgets::widget>
    <div class="rounded-md bg-white shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-white/10">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Latest messages</h4>
            <a href="{{ $url }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">All messages</a>
        </div>
        <div class="max-h-72 divide-y divide-gray-100 overflow-y-auto dark:divide-white/10">
            @forelse ($messages as $m)
                <a href="{{ \App\Filament\Resources\ContactMessageResource::getUrl('view', ['record' => $m]) }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $m->read_at ? 'bg-gray-100 text-gray-500' : 'bg-amber-100 text-amber-700' }}">
                        <x-filament::icon :icon="$m->read_at ? 'heroicon-o-envelope-open' : 'heroicon-s-envelope'" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm {{ $m->read_at ? 'font-medium' : 'font-bold' }} text-gray-800 dark:text-gray-100">{{ $m->name }}</span>
                        <span class="block truncate text-xs text-gray-500">{{ $m->subject ?: \Illuminate\Support\Str::limit($m->message, 50) }}</span>
                    </span>
                    <span class="whitespace-nowrap text-xs text-gray-400">{{ $m->created_at->diffForHumans(short: true) }}</span>
                </a>
            @empty
                <p class="px-4 py-8 text-center text-sm text-gray-400">No messages yet.</p>
            @endforelse
        </div>
    </div>
</x-filament-widgets::widget>
