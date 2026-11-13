<x-layouts.app title="Houses" description="The houses of Prempeh College, Kumasi: their history, colours and housemasters.">
    <x-page-header title="Our Houses" eyebrow="School life" :crumbs="['Houses' => null]" />

    <div class="container-pc py-12">
        <p class="max-w-3xl text-lg leading-relaxed text-green-950/80">The house system is at the heart of boarding life at Prempeh College. Each house has its own history, colours and traditions, and competes in sports, academics and service.</p>

        @if ($houses->isEmpty())
            <div class="mt-10 rounded-sm bg-white p-8 text-center ring-1 ring-green-100">
                <p class="font-serif text-xl font-bold text-green-900">House histories are being prepared</p>
                <p class="mt-2 text-green-950/70">Please check back soon.</p>
            </div>
        @else
            <ul class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($houses as $house)
                    <li><x-house-card :house="$house" /></li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.app>
