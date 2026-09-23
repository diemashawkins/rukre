@props(['item', 'queue' => null, 'index' => 0, 'class' => ''])

@switch($item->type)
    @case(\App\Enums\MediaType::Video)
        <x-video-card :item="$item" :class="$class" />
        @break
    @case(\App\Enums\MediaType::Audio)
        <x-audio-card :item="$item" :queue="$queue" :index="$index" :class="$class" />
        @break
    @default
        <x-book-card :item="$item" :class="$class" />
@endswitch
