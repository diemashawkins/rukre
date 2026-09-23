@php
    $palettes = [['#FFD300', '#18181B'], ['#06B6D4', '#0B0B0C'], ['#EF4444', '#18181B'], ['#FFE17A', '#2E2E33'], ['#A1A1AA', '#18181B']];
    [$accent, $base] = $palettes[$item->id % count($palettes)];
    [$width, $height] = match ($item->type) {
        \App\Enums\MediaType::Video => [640, 360],
        \App\Enums\MediaType::Audio => [480, 480],
        default => [480, 640],
    };
    $label = match ($item->type) {
        \App\Enums\MediaType::Video => 'VIDEO',
        \App\Enums\MediaType::Audio => 'AUDIO',
        default => strtoupper($item->extension),
    };
    $lines = collect(explode("\n", wordwrap($item->title, $item->type === \App\Enums\MediaType::Video ? 24 : 16, "\n", true)))->take(3);
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}">
    <defs>
        <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="{{ $base }}"/>
            <stop offset="1" stop-color="#0B0B0C"/>
        </linearGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#g)"/>
    <circle cx="{{ $width * 0.85 }}" cy="{{ $height * 0.12 }}" r="{{ $width * 0.35 }}" fill="{{ $accent }}" opacity="0.12"/>
    <rect x="32" y="32" width="72" height="22" rx="11" fill="{{ $accent }}"/>
    <text x="68" y="47.5" text-anchor="middle" font-family="Space Grotesk, Arial, sans-serif" font-size="11" font-weight="700" letter-spacing="1" fill="#000">{{ $label }}</text>
    @foreach ($lines as $line)
        <text x="32" y="{{ round($height * 0.42) + $loop->index * 38 }}" font-family="Syne, Arial Black, sans-serif" font-size="32" font-weight="800" fill="#FFFFFF">{{ $line }}</text>
    @endforeach
</svg>
