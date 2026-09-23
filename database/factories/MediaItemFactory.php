<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return $this->attributesFor(MediaType::Video, 'mp4', 'video/mp4', 'Videos');
    }

    public function video(): static
    {
        return $this->state(fn (): array => $this->attributesFor(MediaType::Video, 'mp4', 'video/mp4', 'Videos'));
    }

    public function audio(): static
    {
        return $this->state(fn (): array => $this->attributesFor(MediaType::Audio, 'mp3', 'audio/mpeg', 'Music') + [
            'creator' => fake()->name(),
            'album' => fake()->words(2, true),
            'track_number' => fake()->numberBetween(1, 12),
        ]);
    }

    public function book(string $extension = 'epub'): static
    {
        $mime = match ($extension) {
            'pdf' => 'application/pdf',
            'cbz' => 'application/vnd.comicbook+zip',
            default => 'application/epub+zip',
        };

        return $this->state(fn (): array => $this->attributesFor(MediaType::Book, $extension, $mime, 'Books') + [
            'creator' => fake()->name(),
            'duration' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFor(MediaType $type, string $extension, string $mime, string $folder): array
    {
        $title = ucwords(fake()->unique()->words(3, true));
        $collection = ucwords(fake()->word());
        $path = "{$folder}/{$collection}/{$title}.{$extension}";

        return [
            'type' => $type,
            'disk' => 'media',
            'path' => $path,
            'path_hash' => MediaItem::hashPath('media', $path),
            'title' => $title,
            'collection' => $collection,
            'extension' => $extension,
            'mime_type' => $mime,
            'size' => fake()->numberBetween(100_000, 900_000_000),
            'duration' => $type === MediaType::Book ? null : fake()->numberBetween(120, 7200),
            'file_modified_at' => now(),
            'scanned_at' => now(),
        ];
    }
}
