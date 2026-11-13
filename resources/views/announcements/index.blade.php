<x-layouts.app title="Announcements" description="Official announcements and notices from Prempeh College, Kumasi.">
    <x-page-header title="Announcements" eyebrow="News & Events" :crumbs="['Announcements' => null]" />

    <div class="container-pc py-12">
        <x-news-tabs active="announcements" />

        <div class="mt-10"><x-section-title title="Current announcements" /></div>

        @if ($announcements->isEmpty())
            <p class="mt-10 rounded-sm bg-white p-6 text-green-950/70 ring-1 ring-green-100">No current announcements.</p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($announcements as $a)
                    <x-compact-card :item="$a" kind="announcement" />
                @endforeach
            </div>
            <div class="mt-10">{{ $announcements->links() }}</div>
        @endif
    </div>
</x-layouts.app>
