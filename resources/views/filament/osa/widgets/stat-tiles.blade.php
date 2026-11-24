<x-filament-widgets::widget>
    <div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(260px,1fr))">
        @foreach ($tiles as [$body, $dark, $icon, $title, $value, $sub, $url])
            <a href="{{ $url }}" style="display:flex;overflow:hidden;border-radius:6px;color:#fff;box-shadow:0 1px 2px rgba(0,0,0,.12)">
                <span style="width:64px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:{{ $dark }}">
                    <x-filament::icon :icon="$icon" style="width:28px;height:28px" />
                </span>
                <span style="flex:1;min-width:0;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:{{ $body }}">
                    <span style="min-width:0">
                        <span style="display:block;font-weight:600;font-size:15px">{{ $title }}</span>
                        <span style="display:block;font-size:12px;opacity:.9">{{ $sub }}</span>
                    </span>
                    <span style="font-size:26px;font-weight:700">{{ $value }}</span>
                </span>
            </a>
        @endforeach
    </div>
</x-filament-widgets::widget>
