<x-layouts.app title="Attendance recorded">
    <x-page-header title="Attendance recorded" eyebrow="Old Students Association" :crumbs="['Old Students' => route('osa.home'), 'Thank you' => null]" />
    <div class="container-pc py-12">
        <div class="mx-auto max-w-2xl rounded-sm bg-white p-8 text-center ring-1 ring-green-100">
            @if (session('osa_duplicate'))
                <p class="font-serif text-2xl font-bold text-green-950">You are already checked in</p>
                <p class="mt-2 text-green-950/80">This phone number has already been registered for {{ $event->title }}.</p>
            @else
                <p class="font-serif text-2xl font-bold text-green-950">Thank you{{ session('osa_name') ? ', '.session('osa_name') : '' }}!</p>
                <p class="mt-2 text-green-950/80">Your presence at {{ $event->title }} has been recorded. A confirmation SMS and email are on their way.</p>
            @endif
            <a href="{{ route('osa.home') }}" class="mt-6 inline-block font-semibold text-green-800 underline">Back to the Old Students page</a>
        </div>
    </div>
</x-layouts.app>
