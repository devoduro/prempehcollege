<x-filament-widgets::widget>
    <div class="rounded-md bg-white shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-white/10">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Recent activity</h4>
            <a href="{{ $url }}" class="text-xs font-medium text-primary-600 hover:text-primary-700">Full log</a>
        </div>
        <ul class="max-h-72 divide-y divide-gray-100 overflow-y-auto dark:divide-white/10">
            @forelse ($entries as $e)
                <li class="flex items-start gap-3 px-4 py-2.5">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $e->log_name === 'auth' ? 'bg-sky-100 text-sky-700' : 'bg-green-100 text-green-700' }}">
                        <x-filament::icon :icon="$e->log_name === 'auth' ? 'heroicon-o-key' : 'heroicon-o-pencil-square'" class="h-4 w-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm text-gray-800 dark:text-gray-100">{{ $e->description }}@if ($e->subject_type) &middot; {{ class_basename($e->subject_type) }}@endif</span>
                        <span class="block truncate text-xs text-gray-500">{{ $e->causer?->name ?? 'System or visitor' }} &middot; {{ $e->created_at->diffForHumans() }}</span>
                    </span>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-gray-400">Nothing recorded yet.</li>
            @endforelse
        </ul>
    </div>
</x-filament-widgets::widget>
