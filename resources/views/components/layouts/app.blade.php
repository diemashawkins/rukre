@props(['title' => null, 'bare' => false])

@php
    $nav = [
        ['route' => 'home', 'match' => 'home', 'label' => 'Home', 'icon' => 'home'],
        ['route' => 'videos.index', 'match' => 'videos.*', 'label' => 'Videos', 'icon' => 'film'],
        ['route' => 'audio.index', 'match' => 'audio.*', 'label' => 'Audio', 'icon' => 'music'],
        ['route' => 'books.index', 'match' => 'books.*', 'label' => 'Books', 'icon' => 'book'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B0B0C">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23FFD300'/%3E%3Cpath d='M10 7h7.5a6 6 0 0 1 1.6 11.8L23 25h-4.6l-3.4-5.8H14V25h-4zm4 3.6v5h3.3a2.5 2.5 0 0 0 0-5z' fill='%23000'/%3E%3C/svg%3E">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh" hx-boost="true" hx-indicator="#nav-progress">
    <div id="nav-progress" class="progress-bar pointer-events-none fixed inset-x-0 top-0 z-[60] h-0.5 origin-left bg-primary opacity-0 shadow-glow transition-opacity"></div>

    @if ($bare)
        {{ $slot }}
    @else
        {{-- Desktop sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-60 flex-col border-r border-border-subtle bg-surface-deep lg:flex">
            <a href="{{ route('home') }}" class="flex h-20 items-center gap-2 px-6">
                <x-logo />
            </a>

            <nav class="flex flex-col gap-1 px-3" aria-label="Main">
                @foreach ($nav as $link)
                    @php $active = request()->routeIs($link['match']); @endphp
                    <a href="{{ route($link['route']) }}" @class([
                        'group flex h-11 items-center gap-3 rounded-full px-4 font-label text-sm font-semibold tracking-[0.05em] uppercase transition',
                        'bg-primary text-black' => $active,
                        'text-text-muted hover:bg-surface-card hover:text-text-primary' => ! $active,
                    ]) @if ($active) aria-current="page" @endif>
                        <x-icon :name="$link['icon']" />
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto border-t border-border-subtle p-4">
                <div class="flex items-center gap-2 px-2 text-text-dim">
                    <x-icon name="server" class="size-4" />
                    <span class="type-label-sm">Home server</span>
                    <span class="ml-auto size-2 rounded-full bg-primary shadow-glow" title="Connected"></span>
                </div>
                @auth
                    <div class="mt-4 flex items-center gap-3 px-2">
                        <span class="flex size-9 items-center justify-center rounded-full bg-surface-elevated font-display text-sm font-bold text-primary">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                        <span class="min-w-0 flex-1 truncate text-sm font-semibold text-text-primary">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" hx-boost="false">
                            @csrf
                            <button class="btn-icon size-9" title="Sign out" aria-label="Sign out"><x-icon name="logout" class="size-4" /></button>
                        </form>
                    </div>
                @endauth
            </div>
        </aside>

        <div class="lg:pl-60">
            {{-- Top bar --}}
            <header class="glass sticky top-0 z-30 border-b border-border-subtle">
                <div class="mx-auto flex h-16 max-w-[1600px] items-center gap-4 px-4 md:px-8 xl:px-12">
                    <a href="{{ route('home') }}" class="lg:hidden"><x-logo /></a>
                    <form action="{{ route('search') }}" method="GET" class="relative ml-auto w-full max-w-md lg:ml-0" role="search">
                        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-text-dim" />
                        <input type="search" name="q" value="{{ request()->routeIs('search') ? request('q') : '' }}" placeholder="Search videos, music, books…" class="input h-10 rounded-full pl-11 focus:pl-[43px]" aria-label="Search">
                    </form>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" hx-boost="false" class="lg:hidden">
                            @csrf
                            <button class="btn-icon" title="Sign out" aria-label="Sign out"><x-icon name="logout" class="size-4" /></button>
                        </form>
                    @endauth
                </div>
            </header>

            <main id="main" class="mx-auto max-w-[1600px] px-4 pt-6 pb-40 md:px-8 md:pt-8 lg:pb-28 xl:px-12">
                {{ $slot }}
            </main>
        </div>

        <x-mini-player />

        {{-- Mobile thumb navigation --}}
        <nav class="glass fixed inset-x-0 bottom-0 z-40 flex h-16 border-t border-border-subtle pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Main">
            @foreach ($nav as $link)
                @php $active = request()->routeIs($link['match']); @endphp
                <a href="{{ route($link['route']) }}" @class(['flex flex-1 flex-col items-center justify-center gap-1', 'text-primary' => $active, 'text-text-dim' => ! $active]) @if ($active) aria-current="page" @endif>
                    <x-icon :name="$link['icon']" />
                    <span class="type-label-sm">{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
    @endif
</body>
</html>
