<x-layouts.app :title="'Attendance: '.$event->title">
    <x-page-header :title="$event->title" eyebrow="Meeting attendance" :crumbs="['Old Students' => route('osa.home'), 'Attendance' => null]" />
    <div class="container-pc py-12">
        <div class="mx-auto max-w-2xl rounded-sm bg-white p-6 ring-1 ring-green-100 md:p-8">
            <p class="text-sm text-green-950/70">{{ $event->starts_at->format('l, j F Y, g:i A') }}@if ($event->venue) · {{ $event->venue }}@endif</p>

            @unless ($open)
                <p class="mt-6 rounded-sm bg-gold-400/20 p-4 font-semibold text-green-950">Registration for this meeting is closed.</p>
            @else
                <form method="POST" action="{{ route('osa.store', $event->slug) }}" class="mt-6 grid gap-5 sm:grid-cols-2" novalidate>
                    @csrf
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="website">Leave this empty</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-green-950">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" class="mt-1 w-full rounded-sm border border-green-200 px-3 py-2">
                        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="year_completed" class="block text-sm font-semibold text-green-950">Year group</label>
                        <input id="year_completed" name="year_completed" type="number" min="1930" max="{{ date('Y') }}" value="{{ old('year_completed') }}" required inputmode="numeric" placeholder="e.g. 2016" class="mt-1 w-full rounded-sm border border-green-200 px-3 py-2">
                        @error('year_completed')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="house_id" class="block text-sm font-semibold text-green-950">House</label>
                        <select id="house_id" name="house_id" required class="mt-1 w-full rounded-sm border border-green-200 px-3 py-2">
                            <option value="">Select your house</option>
                            @foreach ($houses as $h)<option value="{{ $h->id }}" @selected(old('house_id') == $h->id)>{{ $h->name }}</option>@endforeach
                        </select>
                        @error('house_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-green-950">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 w-full rounded-sm border border-green-200 px-3 py-2">
                        @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-green-950">Phone number</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="0244123456" class="mt-1 w-full rounded-sm border border-green-200 px-3 py-2">
                        @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="rounded-sm bg-green-800 px-6 py-3 font-semibold text-white hover:bg-green-900">Register my attendance</button>
                        <p class="mt-2 text-xs text-green-950/60">You will receive an SMS and an email confirming your presence.</p>
                    </div>
                </form>
            @endunless
        </div>
    </div>
</x-layouts.app>
