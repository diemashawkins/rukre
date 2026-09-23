@props(['title' => 'Nothing here yet', 'icon' => 'folder'])

<div {{ $attributes->merge(['class' => 'card flex flex-col items-center px-6 py-16 text-center']) }}>
    <span class="flex size-14 items-center justify-center rounded-full bg-surface-elevated text-primary"><x-icon :name="$icon" class="size-6" /></span>
    <h3 class="type-subtitle mt-4">{{ $title }}</h3>
    <div class="mt-2 max-w-md text-text-muted">{{ $slot }}</div>
</div>
