<x-layouts.app title="Contact" description="Contact Prempeh College, Kumasi. Send the school a message or call the office.">
    <x-page-header title="Contact us" :crumbs="['Contact' => null]" />

    <div class="container-pc grid gap-12 py-12 lg:grid-cols-[1fr_22rem]">
        <section aria-labelledby="form-heading">
            <h2 id="form-heading" class="font-serif text-2xl font-bold text-green-900">Send a message</h2>
            @if ($page?->body)
                <div class="prose-pc mt-3 !text-base">{!! \App\Support\Content::render($page->body) !!}</div>
            @endif

            @if (session('status'))
                <p role="status" class="mt-6 rounded-md bg-green-50 p-4 font-medium text-green-900 ring-1 ring-green-200">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-md bg-red-50 p-4 text-red-900 ring-1 ring-red-200">
                    <p class="font-semibold">Please check the form:</p>
                    <ul class="mt-1 list-disc pl-5 text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2" novalidate>
                @csrf
                <input type="hidden" name="form_token" value="{{ $formToken }}">
                {{-- Honeypot: invisible to people and screen readers --}}
                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label for="website">Leave this empty</label>
                    <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                @foreach ([['name', 'Full name', 'text', true, 'name'], ['email', 'Email', 'email', true, 'email'], ['phone', 'Phone (optional)', 'tel', false, 'tel'], ['subject', 'Subject (optional)', 'text', false, 'off']] as [$field, $label, $type, $required, $ac])
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-semibold text-green-950">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" autocomplete="{{ $ac }}" @if ($required) required @endif
                            @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror
                            class="mt-1 w-full rounded-md border border-green-300 bg-white px-3 py-2 focus:border-green-700">
                        @error($field)<p id="{{ $field }}-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                @endforeach

                <div class="sm:col-span-2">
                    <label for="message" class="block text-sm font-semibold text-green-950">Message</label>
                    <textarea id="message" name="message" rows="6" required @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                        class="mt-1 w-full rounded-md border border-green-300 bg-white px-3 py-2 focus:border-green-700">{{ old('message') }}</textarea>
                    @error('message')<p id="message-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" class="btn btn-green">Send message</button>
                    <p class="mt-3 text-xs text-green-950/60">We use your details only to reply to this message.</p>
                </div>
            </form>
        </section>

        <aside class="rounded-lg bg-green-900 p-6 text-white lg:self-start">
            <h2 class="font-serif text-xl font-bold text-gold-400">Reach the school</h2>
            <dl class="mt-4 space-y-4 text-sm">
                <div><dt class="font-semibold">Phone</dt><dd><a class="hover:text-gold-400" href="tel:{{ preg_replace('/\s+/', '', config('site.phone')) }}">{{ config('site.phone') }}</a></dd></div>
                <div><dt class="font-semibold">Location</dt><dd>{{ config('site.location') }}</dd></div>
                @if (config('site.email'))
                    <div><dt class="font-semibold">Email</dt><dd><a class="hover:text-gold-400" href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></dd></div>
                @endif
            </dl>
        </aside>
    </div>
</x-layouts.app>
